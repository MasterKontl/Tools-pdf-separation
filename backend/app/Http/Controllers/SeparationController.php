<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeparationRequest;
use App\Services\ColorSeparationService;
use App\Services\ImageRasterService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SeparationController extends Controller
{
    /**
     * Display the Color Separation interface.
     */
    public function index(
        Request $request,
        ColorSeparationService $sepService,
        ImageRasterService $rasterService,
        \App\Services\QuotaService $quotaService
    ) {
        $manifest = null;
        $token = $request->query('token');

        if ($token && is_string($token)) {
            $manifest = $sepService->getManifest($token);
        }

        $user = $request->user();
        $isAdmin = $user && $user->isAdmin();
        $canHighDpi = $user && $user->canAccessHighDpi();
        $allowedDpis = $canHighDpi ? [150, 300, 600] : [150, 300];
        $cdrEngine = $rasterService->getCdrEngineStatus();
        $usageInfo = $user ? $quotaService->getUsageInfo($user) : $quotaService->getGuestUsageInfo($request);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'manifest' => $manifest,
                'token' => $token,
                'allowedDpis' => $allowedDpis,
                'isAdmin' => $isAdmin,
                'canHighDpi' => $canHighDpi,
                'allowedModes' => [
                    'cmyk' => 'CMYK Process (4 Channels: C, M, Y, K)',
                    'underbase' => 'White Underbase (Dasar Putih Sablon + Choke)',
                    'spot' => 'Spot / Color Match (Eksperimental)',
                    'grayscale' => 'Luminosity / Grayscale (1 Channel Tonal Film)',
                    'outline' => 'Contour & Outline (1 Channel Garis Luar / Keyline)',
                    'rgb' => 'RGB Reference (3 Channels: R, G, B)',
                ],
                'modesConfig' => ColorSeparationService::SCREEN_PRINT_MODES,
                'cdrEngine' => $cdrEngine,
                'usageInfo' => $usageInfo,
                'user' => $user,
            ]);
        }

        return view('separation', [
            'manifest' => $manifest,
            'token' => $token,
            'allowedDpis' => $allowedDpis,
            'isAdmin' => $isAdmin,
            'canHighDpi' => $canHighDpi,
            'allowedModes' => [
                'cmyk' => 'CMYK Process (4 Channels: C, M, Y, K)',
                'underbase' => 'White Underbase (Dasar Putih Sablon + Choke)',
                'spot' => 'Spot / Color Match (Eksperimental)',
                'grayscale' => 'Luminosity / Grayscale (1 Channel Tonal Film)',
                'outline' => 'Contour & Outline (1 Channel Garis Luar / Keyline)',
                'rgb' => 'RGB Reference (3 Channels: R, G, B)',
            ],
            'modesConfig' => ColorSeparationService::SCREEN_PRINT_MODES,
            'cdrEngine' => $cdrEngine,
            'usageInfo' => $usageInfo,
            'user' => $user,
        ]);
    }

    /**
     * Return separation manifest for a token via JSON.
     */
    public function manifest(string $token, ColorSeparationService $sepService)
    {
        $manifest = $sepService->getManifest($token);
        if (!$manifest) {
            return response()->json([
                'success' => false,
                'error' => 'Data separasi tidak ditemukan atau sudah kedaluwarsa.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'manifest' => $manifest,
        ]);
    }

    /**
     * Handle file rasterization and color separation.
     */
    public function process(
        SeparationRequest $request,
        ImageRasterService $rasterService,
        ColorSeparationService $sepService
    ) {
        $rasterData = null;

        try {
            $uploadedFile = $request->file('file');
            $dpi = (int) $request->input('dpi', 300);
            $mode = $request->input('mode', 'cmyk');
            $options = [
                'choke_mm' => (float) $request->input('choke_mm', 0.0),
                'trap_mm' => (float) $request->input('trap_mm', 0.0),
                'spot_colors' => (int) $request->input('spot_colors', 4),
                'registration_marks' => $request->boolean('registration_marks', false),
            ];

            // 1. Rasterize input (PDF, CDR, or PNG/JPG)
            $rasterData = $rasterService->rasterize($uploadedFile, $dpi);

            // 2. Perform color separation
            $result = $sepService->separate(
                $rasterData['rasterPath'],
                $rasterData['safeName'],
                $mode,
                $dpi,
                $options
            );

            // 3. Clean up intermediate raster directory
            if (isset($rasterData['tempDir']) && File::isDirectory($rasterData['tempDir'])) {
                File::deleteDirectory($rasterData['tempDir']);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'token' => $result['token'],
                    'manifest' => $result['manifest'] ?? $sepService->getManifest($result['token']),
                ]);
            }

            return redirect()
                ->route('separation.index', ['token' => $result['token']])
                ->with('success', 'Separasi warna berhasil diproses!');
        } catch (Exception $e) {
            if ($rasterData && isset($rasterData['tempDir']) && File::isDirectory($rasterData['tempDir'])) {
                File::deleteDirectory($rasterData['tempDir']);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gagal memproses separasi warna: ' . $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Gagal memproses separasi warna: ' . $e->getMessage());
        }
    }

    /**
     * Serve channel preview image for tabs.
     */
    public function preview(string $token, string $channel, ColorSeparationService $sepService)
    {
        $manifest = $sepService->getManifest($token);
        if (!$manifest) {
            abort(404, 'Data preview separasi tidak ditemukan atau sudah kedaluwarsa.');
        }

        $baseDir = storage_path("app/temp/separation_{$token}");

        if ($channel === 'original') {
            $filePath = $baseDir . DIRECTORY_SEPARATOR . ($manifest['originalFile'] ?? 'original.png');
        } elseif (isset($manifest['channels'][$channel])) {
            $filePath = $baseDir . DIRECTORY_SEPARATOR . $manifest['channels'][$channel]['file'];
        } else {
            abort(404, 'Channel tidak ditemukan.');
        }

        if (!file_exists($filePath)) {
            abort(404, 'File gambar preview tidak tersedia.');
        }

        $mime = str_ends_with(strtolower($filePath), '.jpg') || str_ends_with(strtolower($filePath), '.jpeg')
            ? 'image/jpeg'
            : 'image/png';

        return response()->file($filePath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Download individual channel PNG or full multi-channel ZIP bundle.
     */
    public function download(string $token, ?string $channel = null, ColorSeparationService $sepService)
    {
        $manifest = $sepService->getManifest($token);
        if (!$manifest) {
            abort(404, 'Data unduhan separasi tidak ditemukan atau sudah kedaluwarsa.');
        }

        $baseDir = storage_path("app/temp/separation_{$token}");
        $safeName = $manifest['safeName'] ?? 'separation';

        // Individual channel download
        if ($channel && $channel !== 'zip') {
            if (!isset($manifest['channels'][$channel])) {
                abort(404, 'Channel yang diminta tidak valid.');
            }

            $chanInfo = $manifest['channels'][$channel];
            $filePath = $baseDir . DIRECTORY_SEPARATOR . $chanInfo['file'];

            if (!file_exists($filePath)) {
                abort(404, 'File channel tidak ditemukan di disk.');
            }

            $downloadName = "{$safeName}_{$chanInfo['file']}";

            return response()->download($filePath, $downloadName, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        // Full ZIP bundle download (or direct image if single-channel grayscale)
        if (!empty($manifest['zipFileName'])) {
            $zipPath = $baseDir . DIRECTORY_SEPARATOR . $manifest['zipFileName'];
            if (!file_exists($zipPath)) {
                abort(404, 'File arsip ZIP tidak ditemukan.');
            }

            return response()->download($zipPath, $manifest['zipFileName'], [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        // Fallback for single channel (e.g. grayscale)
        $firstChan = reset($manifest['channels']);
        if ($firstChan) {
            $filePath = $baseDir . DIRECTORY_SEPARATOR . $firstChan['file'];
            return response()->download($filePath, "{$safeName}_{$firstChan['file']}", [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        abort(404, 'File hasil separasi tidak tersedia.');
    }
}
