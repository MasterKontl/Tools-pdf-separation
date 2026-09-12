<?php

namespace App\Http\Controllers;

use App\Exceptions\ConversionException;
use App\Exceptions\QuotaExceededException;
use App\Http\Requests\ConvertPdfRequest;
use App\Jobs\ConvertPdfPendingJob;
use App\Services\PdfConverterService;
use App\Services\PdfUrlFetcherService;
use App\Services\QuotaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use Throwable;

class PdfConverterController extends Controller
{
    /**
     * Display the converter interface.
     */
    public function index(Request $request, QuotaService $quotaService)
    {
        $user = $request->user();
        $isAdmin = $user && $user->isAdmin();
        $canHighDpi = $user && $user->canAccessHighDpi();
        $usageInfo = $user ? $quotaService->getUsageInfo($user) : $quotaService->getGuestUsageInfo($request);
        $allowedDpis = $canHighDpi ? [150, 300, 600] : [150, 300];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'allowedDpis' => $allowedDpis,
                'allDpis' => [150, 300, 600],
                'isAdmin' => $isAdmin,
                'canHighDpi' => $canHighDpi,
                'allowedFormats' => config('converter.allowed_formats', ['png', 'jpg']),
                'usageInfo' => $usageInfo,
                'user' => $user,
            ]);
        }

        return view('converter', [
            'allowedDpis' => $allowedDpis,
            'allDpis' => [150, 300, 600],
            'isAdmin' => $isAdmin,
            'canHighDpi' => $canHighDpi,
            'allowedFormats' => config('converter.allowed_formats', ['png', 'jpg']),
            'usageInfo' => $usageInfo,
            'user' => $user,
            'maxBatchSize' => config('converter.max_batch_size', 10),
        ]);
    }

    /**
     * Return quota and DPI entitlement status as JSON.
     */
    public function quota(Request $request, QuotaService $quotaService): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user && $user->isAdmin();
        $canHighDpi = $user && $user->canAccessHighDpi();
        $usageInfo = $user ? $quotaService->getUsageInfo($user) : $quotaService->getGuestUsageInfo($request);
        $allowedDpis = $canHighDpi ? [150, 300, 600] : [150, 300];

        return response()->json([
            'success' => true,
            'allowedDpis' => $allowedDpis,
            'allDpis' => [150, 300, 600],
            'isAdmin' => $isAdmin,
            'canHighDpi' => $canHighDpi,
            'usageInfo' => $usageInfo,
            'user' => $user,
        ]);
    }

    /**
     * Securely fetch a PDF file from a public URL or cloud share link.
     */
    public function fetchUrl(Request $request, PdfUrlFetcherService $fetcher): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ], [
            'url.required' => 'Silakan masukkan URL file PDF.',
            'url.max' => 'Panjang URL melebihi batas yang diizinkan.',
        ]);

        try {
            $result = $fetcher->fetch($request->input('url'));

            return response()->json([
                'success' => true,
                'tempId' => $result['tempId'],
                'fileName' => $result['fileName'],
                'fileSize' => $result['fileSize'],
                'fileSizeFormatted' => $result['fileSizeFormatted'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Handle the PDF conversion process with atomic quota reservation and rollback on failure.
     */
    public function convert(
        ConvertPdfRequest $request,
        PdfConverterService $converter,
        QuotaService $quotaService
    ) {
        $user = $request->user();
        $reservation = null;
        $convertStart = microtime(true);

        Log::info('PDF conversion started', [
            'user_id' => $user?->id ?? 'guest',
            'format' => $request->input('format', 'png'),
            'dpi' => $request->input('dpi', 300),
            'has_temp_file' => $request->filled('temp_file_id'),
            'file_size' => $request->file('pdf')?->getSize(),
        ]);

        // 1. Quota reservation (User atomic DB reservation OR Guest session/cache reservation)
        try {
            if ($user) {
                $reservation = $quotaService->reserveQuotaSlot($user);
            } else {
                $reservation = $quotaService->reserveGuestSlot($request);
            }
        } catch (QuotaExceededException $qe) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'quota_exceeded' => true,
                    'error' => $qe->getMessage(),
                ], 429);
            }

            return back()
                ->withInput()
                ->with('quota_exceeded', true)
                ->with('error', $qe->getMessage());
        }

        $tempFetchedPath = null;

        try {
            $format = $request->input('format', 'png');
            $dpi = (int) $request->input('dpi', 300);

            if ($request->filled('temp_file_id')) {
                $tempId = (string) $request->input('temp_file_id');

                // Security: Strictly enforce alphanumeric token format (prevent path traversal / directory escaping)
                if (!preg_match('/^[a-zA-Z0-9]{10,64}$/', $tempId)) {
                    throw new Exception('ID file sementara tidak valid.');
                }

                $tempFetchedPath = storage_path('app/temp/url_import_' . $tempId . '.pdf');
                $source = $tempFetchedPath;
            } else {
                $source = $request->file('pdf');
            }

            $result = $converter->convert($source, $format, $dpi);

            $elapsed = round(microtime(true) - $convertStart, 2);
            Log::info('PDF conversion completed', [
                'user_id' => $user?->id ?? 'guest',
                'elapsed_sec' => $elapsed,
                'page_count' => $result['pageCount'],
                'is_zip' => $result['isZip'],
                'output_file' => $result['fileName'],
            ]);

            // Cleanup the fetched temporary source file after successful conversion
            if ($tempFetchedPath && file_exists($tempFetchedPath)) {
                @unlink($tempFetchedPath);
            }

            // Reservation is permanently confirmed now that conversion was 100% successful!
            $reservation?->confirm();

            return response()->download(
                $result['filePath'],
                $result['fileName'],
                [
                    'Content-Type' => $result['mimeType'],
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]
            )->deleteFileAfterSend(true);
        } catch (ConversionException $e) {
            $elapsed = round(microtime(true) - $convertStart, 2);
            Log::warning('PDF conversion failed', [
                'user_id' => $user?->id ?? 'guest',
                'error_type' => $e->getErrorType(),
                'message' => $e->getMessage(),
                'elapsed_sec' => $elapsed,
            ]);

            $reservation?->release();

            if ($tempFetchedPath && file_exists($tempFetchedPath)) {
                @unlink($tempFetchedPath);
            }

            $safeMessage = match ($e->getErrorType()) {
                'insufficient_memory' => 'Server kehabisan memori saat memproses PDF. Coba kurangi DPI atau gunakan PDF yang lebih sederhana.',
                'timeout' => 'Proses konversi terlalu lama. Coba kurangi DPI atau gunakan file yang lebih sederhana.',
                'engine_unavailable' => 'Mesin konversi PDF tidak tersedia. Silakan hubungi administrator.',
                'no_output' => 'Tidak ada gambar yang dihasilkan dari PDF ini. Pastikan file PDF tidak kosong atau terenkripsi.',
                'invalid_dpi', 'invalid_format' => $e->getMessage(),
                default => 'Konversi PDF gagal. Pastikan file PDF valid dan tidak rusak.',
            };

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => $safeMessage,
                    'error_type' => $e->getErrorType(),
                ], 422);
            }

            return back()->withInput()->with('error', $safeMessage);
        } catch (Throwable $e) {
            $elapsed = round(microtime(true) - $convertStart, 2);
            Log::error('PDF conversion controller error', [
                'user_id' => $user?->id ?? 'guest',
                'error' => $e->getMessage(),
                'elapsed_sec' => $elapsed,
                'trace' => $e->getTraceAsString(),
            ]);

            $reservation?->release();

            if ($tempFetchedPath && file_exists($tempFetchedPath)) {
                @unlink($tempFetchedPath);
            }

            $safeMessage = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => $safeMessage,
                    'error_type' => 'unexpected_error',
                ], 500);
            }

            return back()->withInput()->with('error', $safeMessage);
        }
    }

    /**
     * Start a background conversion job.
     * Returns a job ID that the client can poll for status.
     */
    public function start(
        ConvertPdfRequest $request,
        QuotaService $quotaService
    ): JsonResponse {
        $user = $request->user();
        $reservation = null;

        // Cleanup old jobs (older than 30 minutes)
        $this->cleanupJobs();

        try {
            if ($user) {
                $reservation = $quotaService->reserveQuotaSlot($user);
            } else {
                $reservation = $quotaService->reserveGuestSlot($request);
            }
        } catch (QuotaExceededException $qe) {
            return response()->json([
                'success' => false,
                'quota_exceeded' => true,
                'error' => $qe->getMessage(),
            ], 429);
        }

        try {
            $format = $request->input('format', 'png');
            $dpi = (int) $request->input('dpi', 300);
            $jobId = Str::random(32);
            $jobDir = storage_path('app/jobs/' . $jobId);

            if (!File::isDirectory($jobDir)) {
                File::makeDirectory($jobDir, 0755, true, true);
            }

            // Handle temp file (URL import) vs uploaded file
            if ($request->filled('temp_file_id')) {
                $tempId = (string) $request->input('temp_file_id');
                if (!preg_match('/^[a-zA-Z0-9]{10,64}$/', $tempId)) {
                    throw new Exception('ID file sementara tidak valid.');
                }
                $sourcePath = storage_path('app/temp/url_import_' . $tempId . '.pdf');
                if (!file_exists($sourcePath)) {
                    throw new Exception('File PDF sementara tidak ditemukan atau sudah kadaluarsa.');
                }
                // Copy to job dir so temp file can be cleaned up
                $jobSourcePath = $jobDir . '/input.pdf';
                copy($sourcePath, $jobSourcePath);
                @unlink($sourcePath);
                $originalName = 'url_import_' . $tempId;
            } else {
                $uploadedFile = $request->file('pdf');
                $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $jobSourcePath = $jobDir . '/input.pdf';
                $uploadedFile->move($jobDir, 'input.pdf');
            }

            // Build quota info for rollback on failure
            $quotaInfo = null;
            if ($user) {
                $quotaInfo = [
                    'type' => 'user',
                    'user_id' => $user->id,
                    'usage_date' => now()->toDateString(),
                    'is_unlimited' => $user->isUnlimited(),
                ];
            } else {
                $sessionId = $request->hasSession() ? $request->session()->getId() : 'nosess';
                $fingerprint = md5($request->ip() . '_' . $sessionId);
                $date = now()->toDateString();
                $quotaInfo = [
                    'type' => 'guest',
                    'cache_key' => 'guest_quota_' . $fingerprint . '_' . $date,
                    'date' => $date,
                ];
            }

            // Save job config
            $config = [
                'format' => $format,
                'dpi' => $dpi,
                'source_path' => $jobSourcePath,
                'original_name' => $originalName,
                'quota' => $quotaInfo,
                'created_at' => now()->toIso8601String(),
            ];
            file_put_contents($jobDir . '/config.json', json_encode($config, JSON_PRETTY_PRINT));

            // Write initial status
            file_put_contents($jobDir . '/status.json', json_encode([
                'status' => 'processing',
                'started_at' => now()->toIso8601String(),
            ], JSON_PRETTY_PRINT));

            // Launch background conversion via queue
            ConvertPdfPendingJob::dispatch(
                jobId: $jobId,
                format: $format,
                dpi: $dpi,
                sourcePath: $jobSourcePath,
                originalName: $originalName,
                quotaInfo: $quotaInfo,
            );

            Log::info('Conversion job dispatched', [
                'job_id' => $jobId,
                'user_id' => $user?->id ?? 'guest',
                'format' => $format,
                'dpi' => $dpi,
                'file_size' => filesize($jobSourcePath),
            ]);

            return response()->json([
                'success' => true,
                'job_id' => $jobId,
                'status_url' => route('converter.job-status', $jobId),
            ]);
        } catch (Throwable $e) {
            $reservation?->release();

            // Cleanup on failure
            if (isset($jobDir) && File::isDirectory($jobDir)) {
                File::deleteDirectory($jobDir);
            }

            Log::error('Failed to start conversion job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Gagal memulai konversi. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Get the status of a conversion job.
     */
    public function jobStatus(string $jobId): JsonResponse
    {
        $statusPath = storage_path('app/jobs/' . $jobId . '/status.json');

        if (!file_exists($statusPath)) {
            return response()->json([
                'success' => false,
                'error' => 'Job tidak ditemukan.',
            ], 404);
        }

        $status = json_decode(file_get_contents($statusPath), true);

        return response()->json([
            'success' => true,
            'job_id' => $jobId,
            'status' => $status['status'] ?? 'unknown',
            'error' => $status['error'] ?? null,
            'error_type' => $status['error_type'] ?? null,
            'elapsed_sec' => $status['elapsed_sec'] ?? null,
            'page_count' => $status['page_count'] ?? null,
            'is_zip' => $status['is_zip'] ?? null,
        ]);
    }

    /**
     * Download the result of a completed conversion job.
     */
    public function jobDownload(string $jobId): BinaryFileResponse|JsonResponse
    {
        $statusPath = storage_path('app/jobs/' . $jobId . '/status.json');

        if (!file_exists($statusPath)) {
            return response()->json([
                'success' => false,
                'error' => 'Job tidak ditemukan.',
            ], 404);
        }

        $status = json_decode(file_get_contents($statusPath), true);

        if (($status['status'] ?? '') !== 'completed') {
            return response()->json([
                'success' => false,
                'error' => 'Konversi belum selesai atau gagal.',
            ], 400);
        }

        $filePath = $status['file_path'] ?? null;
        if (!$filePath || !file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'error' => 'File hasil tidak ditemukan.',
            ], 404);
        }

        $fileName = $status['file_name'] ?? 'converted_file';
        $mimeType = $status['mime_type'] ?? 'application/octet-stream';

        return response()->download(
            $filePath,
            $fileName,
            [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        )->deleteFileAfterSend(true);
    }

    /**
     * Cleanup old conversion jobs (older than 30 minutes).
     */
    public function cleanupJobs(): void
    {
        $jobsDir = storage_path('app/jobs');
        if (!File::isDirectory($jobsDir)) {
            return;
        }

        $directories = File::directories($jobsDir);
        $threshold = time() - 1800; // 30 minutes

        foreach ($directories as $dir) {
            if (filemtime($dir) < $threshold) {
                File::deleteDirectory($dir);
            }
        }
    }
}
