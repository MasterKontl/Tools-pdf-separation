<?php

namespace App\Http\Controllers;

use App\Exceptions\QuotaExceededException;
use App\Services\ImageUpscaleService;
use App\Services\QuotaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class UpscalerController extends Controller
{
    /**
     * Display the upscaler interface.
     */
    public function index(Request $request, QuotaService $quotaService)
    {
        $user = $request->user();
        $usageInfo = $user ? $quotaService->getUsageInfo($user) : $quotaService->getGuestUsageInfo($request);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'usageInfo' => $usageInfo,
                'user' => $user,
            ]);
        }

        return view('upscaler', [
            'usageInfo' => $usageInfo,
            'user' => $user,
        ]);
    }

    /**
     * Process image upscale with atomic quota reservation.
     */
    public function process(Request $request, ImageUpscaleService $upscaler, QuotaService $quotaService): JsonResponse
    {
        // Validate request
        $request->validate([
            'image' => ['required', 'file', 'max:' . $upscaler->maxFileSizeKb()],
            'scale' => ['required', 'integer', 'in:2,4'],
        ], [
            'image.required' => 'Silakan pilih gambar untuk di-upscale.',
            'image.file' => 'Upload gambar tidak valid.',
            'image.max' => 'Ukuran file melebihi batas maksimum (' . round($upscaler->maxFileSizeKb() / 1024, 1) . ' MB).',
            'scale.required' => 'Pilih skala upscale (2× atau 4×).',
            'scale.in' => 'Skala harus 2× atau 4×.',
        ]);

        $user = $request->user();
        $reservation = null;

        // 1. Quota reservation (same pattern as PDF Converter)
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
            $file = $request->file('image');
            $scale = (int) $request->input('scale');
            $tempPath = $file->getRealPath();
            $originalName = $file->getClientOriginalName();

            // 2. Server-side validation (MIME, dimensions, pixel count)
            $info = $upscaler->validateImage($tempPath, $originalName);

            // 3. Validate output size won't exceed limits
            $upscaler->validateOutputSize($info['width'], $info['height'], $scale);

            // 4. Perform upscale
            $result = $upscaler->upscale($tempPath, $scale, $info['mime'], $originalName);

            // 5. Purge old temp files
            $upscaler->purgeOldTempFiles();

            // 6. Confirm quota
            $reservation?->confirm();

            return response()->json([
                'success' => true,
                'result' => [
                    'tempId' => $result['tempId'],
                    'extension' => $result['extension'],
                    'fileName' => $result['fileName'],
                    'mimeType' => $result['mimeType'],
                    'width' => $result['width'],
                    'height' => $result['height'],
                    'fileSize' => $result['fileSize'],
                    'fileSizeFormatted' => $this->formatFileSize($result['fileSize']),
                    'scale' => $scale,
                ],
                'input' => [
                    'width' => $info['width'],
                    'height' => $info['height'],
                    'fileSize' => $info['fileSize'],
                    'fileSizeFormatted' => $this->formatFileSize($info['fileSize']),
                ],
            ]);
        } catch (Throwable $e) {
            $reservation?->release();

            $message = $e instanceof Exception
                ? $e->getMessage()
                : 'Terjadi kesalahan saat memproses gambar. Silakan coba lagi.';

            return response()->json([
                'success' => false,
                'error' => $message,
            ], 422);
        }
    }

    /**
     * Download the upscaled result.
     */
    public function download(Request $request, string $tempId, string $ext)
    {
        // Validate tempId format (alphanumeric only, prevent path traversal)
        if (!preg_match('/^[A-Za-z0-9]{10,30}$/', $tempId)) {
            abort(404);
        }

        // Validate extension
        if (!in_array($ext, ['jpg', 'png', 'webp'], true)) {
            abort(404);
        }

        $filePath = storage_path('app/temp/upscale_' . $tempId . '.' . $ext);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan atau sudah kedaluwarsa. Silakan upscale ulang.');
        }

        $mime = match ($ext) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        // Generate safe download name from query param or default
        $downloadName = $request->query('name', 'upscaled-image.' . $ext);
        $downloadName = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $downloadName);
        $downloadName = substr($downloadName, 0, 120);

        return response()->download(
            $filePath,
            $downloadName,
            [
                'Content-Type' => $mime,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        )->deleteFileAfterSend(true);
    }

    /**
     * Format file size to human-readable string.
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        return round($bytes / 1024, 1) . ' KB';
    }
}
