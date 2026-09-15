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
            $parallelChunks = config('converter.parallel_chunks', 1);

            if ($parallelChunks > 1) {
                $pageCount = $this->getPageCount($sourcePath);

                if ($pageCount > 3) {
                    return $this->runConversionParallel(
                        $sourcePath, $format, $dpi, $safeBaseName, $tempDir, $pageCount
                    );
                }
            }

            return $this->runConversionSingle($sourcePath, $format, $dpi, $safeBaseName, $tempDir);
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
     * Run the actual pdftoppm conversion and zip result (single process).
     */
    private function runConversionSingle(
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
     * Get the page count of a PDF file using pdfinfo.
     */
    private function getPageCount(string $pdfPath): int
    {
        $binPath = config('converter.bin_path', 'pdftoppm');
        $pdfinfoPath = dirname($binPath) . '/pdfinfo';

        // If dirname doesn't resolve to a real directory, fall back to PATH lookup
        if (dirname($binPath) === '.' || !is_executable($pdfinfoPath)) {
            $pdfinfoPath = 'pdfinfo';
        }

        $process = new Process([$pdfinfoPath, $pdfPath]);
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ConversionException('conversion_failed', 'Gagal membaca informasi PDF.');
        }

        $output = $process->getOutput();

        if (preg_match('/^Pages:\s+(\d+)/m', $output, $matches)) {
            return (int) $matches[1];
        }

        throw new ConversionException('conversion_failed', 'Tidak dapat menentukan jumlah halaman PDF.');
    }

    /**
     * Calculate page ranges for parallel processing.
     *
     * @return array{first: int, last: int}[]
     */
    private static function calculatePageRanges(int $totalPages, int $chunks): array
    {
        if ($chunks <= 1 || $totalPages <= 1) {
            return [['first' => 1, 'last' => $totalPages]];
        }

        $chunks = min($chunks, $totalPages);
        $base = (int) ceil($totalPages / $chunks);
        $ranges = [];

        for ($i = 0; $i < $chunks; $i++) {
            $first = ($i * $base) + 1;
            $last = min(($i + 1) * $base, $totalPages);

            if ($first > $totalPages) {
                break;
            }

            $ranges[] = ['first' => $first, 'last' => $last];
        }

        return $ranges;
    }

    /**
     * Run parallel pdftoppm processes for faster conversion of multi-page PDFs.
     */
    private function runConversionParallel(
        string $sourcePath,
        string $format,
        int $dpi,
        string $safeBaseName,
        string $tempDir,
        int $pageCount,
    ): array {
        $parallelChunks = config('converter.parallel_chunks', 1);
        $ranges = self::calculatePageRanges($pageCount, $parallelChunks);
        $binPath = config('converter.bin_path', 'pdftoppm');
        $timeout = config('converter.timeout', 600);
        $ext = ($format === 'jpeg') ? 'jpg' : 'png';
        $flag = ($format === 'png') ? '-png' : '-jpeg';

        $entries = [];

        foreach ($ranges as $index => $range) {
            $chunkDir = $tempDir . DIRECTORY_SEPARATOR . 'chunk_' . $index;

            if (!File::isDirectory($chunkDir)) {
                File::makeDirectory($chunkDir, 0755, true, true);
            }

            $outputPrefix = $chunkDir . DIRECTORY_SEPARATOR . 'page';

            $command = [
                $binPath,
                $flag,
                '-r', (string) $dpi,
                '-f', (string) $range['first'],
                '-l', (string) $range['last'],
                $sourcePath,
                $outputPrefix,
            ];

            $chunkStart = microtime(true);

            Log::info('PDF parallel chunk START', [
                'chunk' => $index,
                'first_page' => $range['first'],
                'last_page' => $range['last'],
            ]);

            $process = new Process($command);
            $process->setTimeout($timeout);
            $process->setIdleTimeout($timeout);
            $process->start();

            $entries[] = [
                'process' => $process,
                'chunk_dir' => $chunkDir,
                'range' => $range,
                'start_time' => $chunkStart,
            ];
        }

        // Wait for all processes and collect errors
        $errors = [];

        foreach ($entries as $entry) {
            try {
                $entry['process']->wait();

                $chunkDuration = round(microtime(true) - $entry['start_time'], 2);

                Log::info('PDF parallel chunk END', [
                    'chunk' => $entry['range']['first'] . '-' . $entry['range']['last'],
                    'duration_sec' => $chunkDuration,
                    'exit_code' => $entry['process']->getExitCode(),
                ]);
            } catch (\Exception $e) {
                $errors[] = [
                    'entry' => $entry,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Check for process failures (exit code != 0)
        foreach ($entries as $entry) {
            if (!$entry['process']->isSuccessful()) {
                $exitCode = $entry['process']->getExitCode();
                $errorOutput = trim(
                    $entry['process']->getErrorOutput() ?: $entry['process']->getOutput()
                );

                // Cleanup all chunk directories
                foreach ($entries as $cleanupEntry) {
                    File::deleteDirectory($cleanupEntry['chunk_dir']);
                }

                // OOM killed
                if ($exitCode === 137
                    || str_contains($errorOutput, 'Killed')
                    || str_contains($errorOutput, 'SIGKILL')
                ) {
                    throw new ConversionException(
                        'insufficient_memory',
                        'Server kehabisan memori saat memproses PDF paralel. Coba kurangi DPI atau gunakan PDF yang lebih sederhana.'
                    );
                }

                // pdftoppm not found
                if ($exitCode === 127 || str_contains(strtolower($errorOutput), 'not found')) {
                    throw new ConversionException(
                        'engine_unavailable',
                        'Engine konversi PDF tidak tersedia pada server.'
                    );
                }

                // Timeout
                if ($exitCode === null
                    || $exitCode === 143
                    || str_contains($errorOutput, 'SIGTERM')
                    || str_contains($errorOutput, 'timeout')
                ) {
                    throw new ConversionException(
                        'timeout',
                        'Proses konversi paralel terlalu lama.'
                    );
                }

                $range = $entry['range'];
                throw new ConversionException(
                    'conversion_failed',
                    "Konversi gagal pada halaman {$range['first']}-{$range['last']}."
                );
            }
        }

        // Check for wait exceptions
        if (!empty($errors)) {
            foreach ($entries as $cleanupEntry) {
                File::deleteDirectory($cleanupEntry['chunk_dir']);
            }

            throw new ConversionException(
                'conversion_failed',
                'Konversi paralel gagal: ' . $errors[0]['error']
            );
        }

        // Collect all output files across chunks
        $allFiles = [];

        foreach ($entries as $entry) {
            $pattern = $entry['chunk_dir'] . DIRECTORY_SEPARATOR . 'page*.' . $ext;
            $files = glob($pattern);

            if (empty($files) && $format === 'jpeg') {
                $files = glob($entry['chunk_dir'] . DIRECTORY_SEPARATOR . 'page*.jpeg');
                $ext = 'jpg';
            }

            $allFiles = array_merge($allFiles, $files);
        }

        // Sort naturally for correct page order
        natsort($allFiles);
        $allFiles = array_values($allFiles);

        // Validate output count
        if (count($allFiles) !== $pageCount) {
            foreach ($entries as $cleanupEntry) {
                File::deleteDirectory($cleanupEntry['chunk_dir']);
            }

            throw new ConversionException(
                'no_output',
                "Expected {$pageCount} pages, got " . count($allFiles) . '.'
            );
        }

        // Create ZIP from all collected files
        Log::info('PDF ZIP START', [
            'file_count' => count($allFiles),
        ]);

        $zipStart = microtime(true);
        $result = $this->createZipArchive($allFiles, $safeBaseName, $dpi, $ext, $tempDir);

        Log::info('PDF ZIP END', [
            'duration_sec' => round(microtime(true) - $zipStart, 2),
            'file_size' => filesize($result['filePath']),
        ]);

        return $result;
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
