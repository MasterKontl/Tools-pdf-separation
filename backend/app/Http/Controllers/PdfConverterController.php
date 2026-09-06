<?php

namespace App\Http\Controllers;

use App\Exceptions\QuotaExceededException;
use App\Http\Requests\ConvertPdfRequest;
use App\Services\PdfConverterService;
use App\Services\PdfUrlFetcherService;
use App\Services\QuotaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
        } catch (Throwable $e) {
            // Rollback quota reservation if conversion failed!
            $reservation?->release();

            if ($tempFetchedPath && file_exists($tempFetchedPath)) {
                @unlink($tempFetchedPath);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gagal memproses file PDF: ' . $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Gagal memproses file PDF: ' . $e->getMessage());
        }
    }
}
