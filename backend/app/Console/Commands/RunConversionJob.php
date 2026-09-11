<?php

namespace App\Console\Commands;

use App\Exceptions\ConversionException;
use App\Services\PdfConverterService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunConversionJob extends Command
{
    protected $signature = 'convert:job {jobId}';
    protected $description = 'Run a background PDF conversion job';

    public function handle(PdfConverterService $converter): int
    {
        $jobId = $this->argument('jobId');
        $jobDir = storage_path('app/jobs/' . $jobId);
        $configPath = $jobDir . '/config.json';
        $statusPath = $jobDir . '/status.json';

        if (!file_exists($configPath)) {
            $this->error("Job config not found: {$jobId}");
            return 1;
        }

        $config = json_decode(file_get_contents($configPath), true);
        if (!$config) {
            $this->error("Invalid job config: {$jobId}");
            return 1;
        }

        $start = microtime(true);

        try {
            $result = $converter->convert(
                $config['source_path'],
                $config['format'],
                (int) $config['dpi']
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
                'job_id' => $jobId,
                'elapsed_sec' => $elapsed,
                'page_count' => $result['pageCount'],
                'is_zip' => $result['isZip'],
            ]);

            // Clean up source file
            if (file_exists($config['source_path'])) {
                @unlink($config['source_path']);
            }

            return 0;
        } catch (ConversionException $e) {
            $elapsed = round(microtime(true) - $start, 2);

            Log::warning('Background conversion failed', [
                'job_id' => $jobId,
                'error_type' => $e->getErrorType(),
                'message' => $e->getMessage(),
                'elapsed_sec' => $elapsed,
            ]);

            $this->writeFailure($statusPath, $jobId, $e->getErrorType(), $e->getMessage(), $elapsed, $config);
            $this->cleanupSource($config);
            return 1;
        } catch (Throwable $e) {
            $elapsed = round(microtime(true) - $start, 2);

            Log::error('Background conversion error', [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
                'elapsed_sec' => $elapsed,
            ]);

            $this->writeFailure($statusPath, $jobId, 'unexpected_error', 'Terjadi kesalahan yang tidak terduga.', $elapsed, $config);
            $this->cleanupSource($config);
            return 1;
        }
    }

    private function writeFailure(string $statusPath, string $jobId, string $errorType, string $message, float $elapsed, array $config): void
    {
        $status = [
            'status' => 'failed',
            'error_type' => $errorType,
            'error' => $message,
            'elapsed_sec' => $elapsed,
            'failed_at' => now()->toIso8601String(),
        ];

        file_put_contents($statusPath, json_encode($status, JSON_PRETTY_PRINT));

        // Release quota on failure
        $this->releaseQuota($config);
    }

    private function releaseQuota(array $config): void
    {
        try {
            $quota = $config['quota'] ?? null;
            if (!$quota) return;

            if ($quota['type'] === 'user') {
                $reservation = new \App\Services\QuotaReservation(
                    userId: (int) $quota['user_id'],
                    usageDate: $quota['usage_date'],
                    isUnlimited: (bool) $quota['is_unlimited'],
                );
                $reservation->release();
            } elseif ($quota['type'] === 'guest') {
                $cacheKey = $quota['cache_key'];
                $date = $quota['date'];
                $cacheCount = (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0);
                if ($cacheCount > 0) {
                    \Illuminate\Support\Facades\Cache::put($cacheKey, $cacheCount - 1, now()->addDay());
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed to release quota for job', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function cleanupSource(array $config): void
    {
        $sourcePath = $config['source_path'] ?? null;
        if ($sourcePath && file_exists($sourcePath)) {
            @unlink($sourcePath);
        }
    }
}
