<?php

namespace App\Jobs;

use App\Exceptions\ConversionException;
use App\Models\ConversionJob;
use App\Services\PdfConverterService;
use App\Services\QuotaReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ConvertPdfPendingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public int $tries = 1;

    public function __construct(
        public string $jobId,
        public string $format,
        public int $dpi,
        public string $sourcePath,
        public string $originalName,
        public ?array $quotaInfo = null,
    ) {
        $this->afterCommit = false;
    }

    public function handle(PdfConverterService $converter): void
    {
        $job = ConversionJob::find($this->jobId);
        if (!$job) {
            Log::error('Conversion job not found in database', ['job_id' => $this->jobId]);
            return;
        }

        $jobDir = storage_path('app/jobs/' . $this->jobId);
        if (!File::isDirectory($jobDir)) {
            File::makeDirectory($jobDir, 0755, true, true);
        }

        $localSourcePath = $jobDir . '/input.pdf';
        $start = microtime(true);

        try {
            // Download source file from the web service if not available locally
            if (!file_exists($this->sourcePath)) {
                $this->downloadSource($localSourcePath);
                $sourceToUse = $localSourcePath;
            } else {
                $sourceToUse = $this->sourcePath;
            }

            $result = $converter->convert(
                $sourceToUse,
                $this->format,
                $this->dpi
            );

            // Upload result back to web service
            $this->uploadResult($result);

            $elapsed = round(microtime(true) - $start, 2);

            $job->update([
                'status' => 'completed',
                'elapsed_sec' => $elapsed,
            ]);

            Log::info('Background conversion completed', [
                'job_id' => $this->jobId,
                'elapsed_sec' => $elapsed,
                'page_count' => $result['pageCount'],
                'is_zip' => $result['isZip'],
            ]);

            $this->cleanupFiles($localSourcePath, $sourceToUse);
        } catch (ConversionException $e) {
            $elapsed = round(microtime(true) - $start, 2);

            Log::warning('Background conversion failed', [
                'job_id' => $this->jobId,
                'error_type' => $e->getErrorType(),
                'message' => $e->getMessage(),
                'elapsed_sec' => $elapsed,
            ]);

            $job->update([
                'status' => 'failed',
                'error_type' => $e->getErrorType(),
                'error' => $e->getMessage(),
                'elapsed_sec' => $elapsed,
            ]);

            $this->releaseQuota();
            $this->cleanupFiles($localSourcePath, $this->sourcePath);
        } catch (Throwable $e) {
            $elapsed = round(microtime(true) - $start, 2);

            Log::error('Background conversion error', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'elapsed_sec' => $elapsed,
            ]);

            $job->update([
                'status' => 'failed',
                'error_type' => 'unexpected_error',
                'error' => 'Terjadi kesalahan yang tidak terduga.',
                'elapsed_sec' => $elapsed,
            ]);

            $this->releaseQuota();
            $this->cleanupFiles($localSourcePath, $this->sourcePath);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $job = ConversionJob::find($this->jobId);
        if ($job) {
            $job->update([
                'status' => 'failed',
                'error_type' => 'job_failed',
                'error' => 'Konversi gagal karena kesalahan server.',
            ]);
        }

        Log::error('Conversion job permanently failed', [
            'job_id' => $this->jobId,
            'error' => $exception?->getMessage(),
        ]);

        $this->releaseQuota();
    }

    private function downloadSource(string $destinationPath): void
    {
        $url = config('app.url') . '/convert/file/' . $this->jobId;

        Log::info('Downloading source file for conversion job', [
            'job_id' => $this->jobId,
            'url' => $url,
        ]);

        $response = Http::timeout(300)
            ->withHeaders(['X-Worker-Token' => 'internal'])
            ->get($url);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Gagal mengunduh file sumber: HTTP ' . $response->status()
            );
        }

        file_put_contents($destinationPath, $response->body());

        if (!file_exists($destinationPath) || filesize($destinationPath) === 0) {
            throw new \RuntimeException('File sumber yang diunduh kosong atau tidak valid.');
        }

        Log::info('Source file downloaded', [
            'job_id' => $this->jobId,
            'size' => filesize($destinationPath),
        ]);
    }

    private function uploadResult(array $result): void
    {
        $url = config('app.url') . '/convert/upload/' . $this->jobId;

        Log::info('Uploading conversion result', [
            'job_id' => $this->jobId,
            'file_name' => $result['fileName'],
            'size' => filesize($result['filePath']),
        ]);

        $response = Http::timeout(600)
            ->attach('result', file_get_contents($result['filePath']), $result['fileName'])
            ->post($url, [
                'file_name' => $result['fileName'],
                'mime_type' => $result['mimeType'],
                'is_zip' => $result['isZip'] ? '1' : '0',
                'page_count' => (string) $result['pageCount'],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Gagal mengunggah hasil konversi: HTTP ' . $response->status()
            );
        }

        Log::info('Result uploaded successfully', [
            'job_id' => $this->jobId,
        ]);

        // Cleanup local result files
        @unlink($result['filePath']);
        if ($result['tempDir'] && is_dir($result['tempDir'])) {
            @rmdir($result['tempDir']);
        }
    }

    private function releaseQuota(): void
    {
        try {
            $quota = $this->quotaInfo;
            if (!$quota) return;

            if ($quota['type'] === 'user') {
                $reservation = new QuotaReservation(
                    userId: (int) $quota['user_id'],
                    usageDate: $quota['usage_date'],
                    isUnlimited: (bool) $quota['is_unlimited'],
                );
                $reservation->release();
            } elseif ($quota['type'] === 'guest') {
                $cacheKey = $quota['cache_key'];
                $cacheCount = (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0);
                if ($cacheCount > 0) {
                    \Illuminate\Support\Facades\Cache::put($cacheKey, $cacheCount - 1, now()->addDay());
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to release quota for job', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function cleanupFiles(string $localPath, string $originalPath): void
    {
        if ($localPath && file_exists($localPath)) {
            @unlink($localPath);
        }
        if ($originalPath && file_exists($originalPath) && $originalPath !== $localPath) {
            @unlink($originalPath);
        }
    }
}
