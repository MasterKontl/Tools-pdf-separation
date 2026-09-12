<?php

namespace App\Jobs;

use App\Exceptions\ConversionException;
use App\Services\PdfConverterService;
use App\Services\QuotaReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
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
        $jobDir = storage_path('app/jobs/' . $this->jobId);
        $statusPath = $jobDir . '/status.json';

        $start = microtime(true);

        try {
            $result = $converter->convert(
                $this->sourcePath,
                $this->format,
                $this->dpi
            );

            $elapsed = round(microtime(true) - $start, 2);

            $status = [
                'status' => 'completed',
                'file_path' => $result['filePath'],
                'file_name' => $result['fileName'],
                'mime_type' => $result['mimeType'],
                'is_zip' => $result['isZip'],
                'page_count' => $result['pageCount'],
                'elapsed_sec' => $elapsed,
                'completed_at' => now()->toIso8601String(),
            ];

            file_put_contents($statusPath, json_encode($status, JSON_PRETTY_PRINT));

            Log::info('Background conversion completed', [
                'job_id' => $this->jobId,
                'elapsed_sec' => $elapsed,
                'page_count' => $result['pageCount'],
                'is_zip' => $result['isZip'],
            ]);

            if (file_exists($this->sourcePath)) {
                @unlink($this->sourcePath);
            }
        } catch (ConversionException $e) {
            $elapsed = round(microtime(true) - $start, 2);

            Log::warning('Background conversion failed', [
                'job_id' => $this->jobId,
                'error_type' => $e->getErrorType(),
                'message' => $e->getMessage(),
                'elapsed_sec' => $elapsed,
            ]);

            $this->writeFailure($statusPath, $e->getErrorType(), $e->getMessage(), $elapsed);
            $this->releaseQuota();
            $this->cleanupSource();
        } catch (Throwable $e) {
            $elapsed = round(microtime(true) - $start, 2);

            Log::error('Background conversion error', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'elapsed_sec' => $elapsed,
            ]);

            $this->writeFailure($statusPath, 'unexpected_error', 'Terjadi kesalahan yang tidak terduga.', $elapsed);
            $this->releaseQuota();
            $this->cleanupSource();
        }
    }

    public function failed(?Throwable $exception): void
    {
        $jobDir = storage_path('app/jobs/' . $this->jobId);
        $statusPath = $jobDir . '/status.json';

        Log::error('Conversion job permanently failed', [
            'job_id' => $this->jobId,
            'error' => $exception?->getMessage(),
        ]);

        $this->writeFailure($statusPath, 'job_failed', 'Konversi gagal karena kesalahan server.', 0);
        $this->releaseQuota();
        $this->cleanupSource();
    }

    private function writeFailure(string $statusPath, string $errorType, string $message, float $elapsed): void
    {
        $status = [
            'status' => 'failed',
            'error_type' => $errorType,
            'error' => $message,
            'elapsed_sec' => $elapsed,
            'failed_at' => now()->toIso8601String(),
        ];

        @file_put_contents($statusPath, json_encode($status, JSON_PRETTY_PRINT));
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

    private function cleanupSource(): void
    {
        if ($this->sourcePath && file_exists($this->sourcePath)) {
            @unlink($this->sourcePath);
        }
    }
}
