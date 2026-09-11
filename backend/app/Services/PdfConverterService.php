<?php

namespace App\Services;

use App\Exceptions\ConversionException;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

class PdfConverterService
{
    /**
     * Convert an uploaded PDF file into PNG or JPG image(s).
     *
     * @param  UploadedFile|string  $pdfFile
     * @param  string  $format  ('png' or 'jpg'/'jpeg')
     * @param  int  $dpi  (150, 300, 600)
     * @return array{
     *     filePath: string,
     *     fileName: string,
     *     mimeType: string,
     *     isZip: bool,
     *     pageCount: int,
     *     tempDir: string
     * }
     *
     * @throws ConversionException
     */
    public function convert(UploadedFile|string $pdfFile, string $format = 'png', int $dpi = 300): array
    {
        $this->purgeOldTempDirectories();

        $allowedDpis = config('converter.allowed_dpis', [150, 300, 600]);
        if (!in_array($dpi, $allowedDpis, true)) {
            throw new ConversionException('invalid_dpi', "DPI {$dpi} tidak didukung.");
        }

        $format = strtolower($format);
        if ($format === 'jpg') {
            $format = 'jpeg';
        }

        if (!in_array($format, ['png', 'jpeg'], true)) {
            throw new ConversionException('invalid_format', "Format {$format} tidak didukung.");
        }

        // Determine original name & path
        if ($pdfFile instanceof UploadedFile) {
            $originalName = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
            $sourcePath = $pdfFile->getRealPath();
        } else {
            $originalName = pathinfo($pdfFile, PATHINFO_FILENAME);
            $sourcePath = $pdfFile;
        }

        if (!file_exists($sourcePath)) {
            throw new ConversionException('file_not_found', 'File sumber PDF tidak ditemukan.');
        }

        $safeBaseName = Str::slug($originalName) ?: 'converted-document';
        $tempDir = storage_path('app/temp/pdf_conv_' . uniqid('', true));

        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true, true);
        }

        try {
            return $this->runConversion($sourcePath, $format, $dpi, $safeBaseName, $tempDir);
        } catch (ConversionException $e) {
            File::deleteDirectory($tempDir);
            throw $e;
        } catch (Exception $e) {
            File::deleteDirectory($tempDir);

            Log::error('PDF conversion unexpected error', [
                'source' => $sourcePath,
                'format' => $format,
                'dpi' => $dpi,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new ConversionException('conversion_failed', 'Konversi PDF gagal. Pastikan file PDF valid dan tidak rusak.', $e);
        }
    }

    /**
     * Run the actual pdftoppm conversion and zip result.
     */
    private function runConversion(
        string $sourcePath,
        string $format,
        int $dpi,
        string $safeBaseName,
        string $tempDir,
    ): array {
        $outputPrefix = $tempDir . DIRECTORY_SEPARATOR . 'page';
        $binPath = config('converter.bin_path', 'pdftoppm');

        $flag = ($format === 'png') ? '-png' : '-jpeg';
        $command = [
            $binPath,
            $flag,
            '-r',
            (string) $dpi,
            $sourcePath,
            $outputPrefix,
        ];

        $timeout = config('converter.timeout', 600);
        $process = new Process($command);
        $process->setTimeout($timeout);
        $process->setIdleTimeout($timeout);

        try {
            $process->run();
        } catch (Exception $e) {
            Log::error('PDF conversion process error', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
            ]);

            throw new ConversionException('conversion_failed', 'Gagal menjalankan proses konversi.', $e);
        }

        if (!$process->isSuccessful()) {
            $exitCode = $process->getExitCode();
            $errorOutput = trim($process->getErrorOutput() ?: $process->getOutput());

            Log::warning('PDF conversion process failed', [
                'source' => $sourcePath,
                'exit_code' => $exitCode,
                'error_output' => $errorOutput,
            ]);

            // pdftoppm not found
            if ($exitCode === 127 || str_contains(strtolower($errorOutput), 'not found')) {
                throw new ConversionException('engine_unavailable', 'Engine konversi PDF tidak tersedia pada server.');
            }

            // OOM killed (exit code 137 = SIGKILL, typically from cgroups OOM)
            if ($exitCode === 137 || str_contains($errorOutput, 'Killed') || str_contains($errorOutput, 'SIGKILL')) {
                throw new ConversionException('insufficient_memory', 'Server kehabisan memori saat memproses PDF. Coba kurangi DPI atau gunakan PDF yang lebih sederhana.');
            }

            // Timeout / SIGTERM
            if ($exitCode === null || $exitCode === 143 || str_contains($errorOutput, 'SIGTERM') || str_contains($errorOutput, 'timeout')) {
                throw new ConversionException('timeout', 'Proses konversi terlalu lama. Coba kurangi DPI atau gunakan file yang lebih sederhana.');
            }

            // Generic failure — no internal details exposed
            throw new ConversionException('conversion_failed', 'Konversi PDF gagal. Pastikan file PDF valid dan tidak terenkripsi/rusak.');
        }

        // Find generated image files
        $ext = ($format === 'jpeg') ? 'jpg' : 'png';
        $pattern = $tempDir . DIRECTORY_SEPARATOR . 'page*.' . $ext;
        $files = glob($pattern);

        if (empty($files) && $format === 'jpeg') {
            $files = glob($tempDir . DIRECTORY_SEPARATOR . 'page*.jpeg');
            $ext = 'jpg';
        }

        if (empty($files)) {
            throw new ConversionException('no_output', 'Tidak ada gambar yang berhasil dihasilkan dari PDF ini.');
        }

        // Sort files naturally
        natsort($files);
        $files = array_values($files);
        $pageCount = count($files);

        // Multi-page: bundle into ZIP
        if ($pageCount > 1) {
            return $this->createZipArchive($files, $safeBaseName, $dpi, $ext, $tempDir);
        }

        // Single page
        return $this->createSingleFile($files[0], $safeBaseName, $dpi, $ext, $tempDir);
    }

    /**
     * Bundle multiple page images into a ZIP archive.
     */
    private function createZipArchive(
        array $files,
        string $safeBaseName,
        int $dpi,
        string $ext,
        string $tempDir,
    ): array {
        if (!class_exists(ZipArchive::class)) {
            throw new ConversionException('zip_unavailable', 'Ekstensi PHP ZipArchive belum diaktifkan di server.');
        }

        $zipFileName = "{$safeBaseName}_{$dpi}dpi.zip";
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;

        $zip = new ZipArchive();
        $zipStatus = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($zipStatus !== true) {
            throw new ConversionException('zip_failed', 'Gagal membuat file arsip ZIP untuk dokumen multi-halaman.');
        }

        $digits = max(2, strlen((string) count($files)));
        foreach ($files as $index => $filePath) {
            $pageNumber = sprintf("%0{$digits}d", $index + 1);
            $entryName = "{$safeBaseName}_page_{$pageNumber}.{$ext}";
            $zip->addFile($filePath, $entryName);
        }

        $zip->close();

        // Clean up intermediate raw image files
        foreach ($files as $filePath) {
            @unlink($filePath);
        }

        return [
            'filePath' => $zipPath,
            'fileName' => $zipFileName,
            'mimeType' => 'application/zip',
            'isZip' => true,
            'pageCount' => count($files),
            'tempDir' => $tempDir,
        ];
    }

    /**
     * Prepare a single-page output file.
     */
    private function createSingleFile(
        string $sourceFile,
        string $safeBaseName,
        int $dpi,
        string $ext,
        string $tempDir,
    ): array {
        $singleFileName = "{$safeBaseName}_{$dpi}dpi.{$ext}";
        $singleFilePath = $tempDir . DIRECTORY_SEPARATOR . $singleFileName;

        if ($sourceFile !== $singleFilePath) {
            rename($sourceFile, $singleFilePath);
        }

        $mimeType = ($ext === 'png') ? 'image/png' : 'image/jpeg';

        return [
            'filePath' => $singleFilePath,
            'fileName' => $singleFileName,
            'mimeType' => $mimeType,
            'isZip' => false,
            'pageCount' => 1,
            'tempDir' => $tempDir,
        ];
    }

    /**
     * Purge temporary converter directories older than 15 minutes.
     */
    protected function purgeOldTempDirectories(): void
    {
        $tempBase = storage_path('app/temp');
        if (!File::isDirectory($tempBase)) {
            return;
        }

        $directories = File::directories($tempBase);
        $threshold = time() - 900; // 15 minutes ago

        foreach ($directories as $dir) {
            if (filemtime($dir) < $threshold) {
                File::deleteDirectory($dir);
            }
        }
    }
}
