<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
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
     * @throws Exception
     */
    public function convert(UploadedFile|string $pdfFile, string $format = 'png', int $dpi = 300): array
    {
        $this->purgeOldTempDirectories();

        $allowedDpis = config('converter.allowed_dpis', [150, 300, 600]);
        if (!in_array($dpi, $allowedDpis, true)) {
            throw new Exception("DPI {$dpi} tidak didukung. Pilihan yang tersedia: " . implode(', ', $allowedDpis));
        }

        $format = strtolower($format);
        if ($format === 'jpg') {
            $format = 'jpeg';
        }

        if (!in_array($format, ['png', 'jpeg'], true)) {
            throw new Exception("Format {$format} tidak didukung. Pilihan: PNG atau JPG.");
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
            throw new Exception("File sumber PDF tidak ditemukan.");
        }

        $safeBaseName = Str::slug($originalName) ?: 'converted-document';
        $tempDir = storage_path('app/temp/pdf_conv_' . uniqid('', true));

        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true, true);
        }

        // Output prefix inside temp directory
        $outputPrefix = $tempDir . DIRECTORY_SEPARATOR . 'page';
        $binPath = config('converter.bin_path', 'pdftoppm');

        // Command: pdftoppm -<format> -r <dpi> <sourcePath> <outputPrefix>
        $flag = ($format === 'png') ? '-png' : '-jpeg';
        $command = [
            $binPath,
            $flag,
            '-r',
            (string) $dpi,
            $sourcePath,
            $outputPrefix,
        ];

        $timeout = config('converter.timeout', 300);
        $process = new Process($command);
        $process->setTimeout($timeout);

        try {
            $process->run();
        } catch (Exception $e) {
            File::deleteDirectory($tempDir);
            throw new Exception("Gagal menjalankan proses konversi: " . $e->getMessage());
        }

        if (!$process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput() ?: $process->getOutput());
            File::deleteDirectory($tempDir);
            throw new Exception("Konversi PDF gagal. Pastikan file PDF valid dan tidak terenkripsi/rusak. Detail: " . ($errorOutput ?: 'Unknown error'));
        }

        // Find generated image files
        $ext = ($format === 'jpeg') ? 'jpg' : 'png';
        $pattern = $tempDir . DIRECTORY_SEPARATOR . 'page*.' . $ext;
        $files = glob($pattern);

        if (empty($files)) {
            // Check if jpeg produced .jpeg
            if ($format === 'jpeg') {
                $files = glob($tempDir . DIRECTORY_SEPARATOR . 'page*.jpeg');
                $ext = 'jpg';
            }
        }

        if (empty($files)) {
            File::deleteDirectory($tempDir);
            throw new Exception("Tidak ada gambar yang berhasil dihasilkan dari PDF ini.");
        }

        // Sort files naturally by name (page-1, page-2, ... page-10)
        natsort($files);
        $files = array_values($files);
        $pageCount = count($files);

        // If multi-page: bundle into ZIP
        if ($pageCount > 1) {
            if (!class_exists(ZipArchive::class)) {
                File::deleteDirectory($tempDir);
                throw new Exception("Ekstensi PHP ZipArchive belum diaktifkan di server.");
            }

            $zipFileName = "{$safeBaseName}_{$dpi}dpi.zip";
            $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;

            $zip = new ZipArchive();
            $zipStatus = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($zipStatus !== true) {
                File::deleteDirectory($tempDir);
                throw new Exception("Gagal membuat file arsip ZIP untuk dokumen multi-halaman.");
            }

            $digits = max(2, strlen((string) $pageCount));
            foreach ($files as $index => $filePath) {
                $pageNumber = sprintf("%0{$digits}d", $index + 1);
                $entryName = "{$safeBaseName}_page_{$pageNumber}.{$ext}";
                $zip->addFile($filePath, $entryName);
            }

            $zip->close();

            // Clean up intermediate raw image files; only zip remains
            foreach ($files as $filePath) {
                @unlink($filePath);
            }

            return [
                'filePath' => $zipPath,
                'fileName' => $zipFileName,
                'mimeType' => 'application/zip',
                'isZip' => true,
                'pageCount' => $pageCount,
                'tempDir' => $tempDir,
            ];
        }

        // Single page file
        $singleFile = $files[0];
        $singleFileName = "{$safeBaseName}_{$dpi}dpi.{$ext}";
        $singleFilePath = $tempDir . DIRECTORY_SEPARATOR . $singleFileName;

        if ($singleFile !== $singleFilePath) {
            rename($singleFile, $singleFilePath);
        }

        $mimeType = ($format === 'png') ? 'image/png' : 'image/jpeg';

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
