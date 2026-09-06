<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ImageRasterService
{
    /**
     * Rasterize an uploaded PDF or image file into a standardized local PNG bitmap path.
     * For multi-page PDF documents in V2.1, only the first page is processed.
     *
     * @param  UploadedFile|string  $input
     * @param  int  $dpi
     * @return array{
     *     rasterPath: string,
     *     originalName: string,
     *     safeName: string,
     *     isPdf: bool,
     *     width: int,
     *     height: int,
     *     tempDir: string
     * }
     *
     * @throws Exception
     */
    public function rasterize(UploadedFile|string $input, int $dpi = 300): array
    {
        $this->purgeOldRasterDirectories();

        if ($input instanceof UploadedFile) {
            $originalName = pathinfo($input->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = strtolower($input->getClientOriginalExtension());
            $sourcePath = $input->getRealPath();
        } else {
            $originalName = pathinfo($input, PATHINFO_FILENAME);
            $extension = strtolower(pathinfo($input, PATHINFO_EXTENSION));
            $sourcePath = $input;
        }

        if (!file_exists($sourcePath)) {
            throw new Exception("File sumber tidak ditemukan.");
        }

        $safeName = Str::slug($originalName) ?: 'raster-image';
        $tempDir = storage_path('app/temp/raster_' . uniqid('', true));

        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true, true);
        }

        $isPdf = in_array($extension, ['pdf'], true);
        $isCdr = in_array($extension, ['cdr'], true);

        if ($isCdr) {
            $rasterPath = $this->convertCdrToRaster($sourcePath, $tempDir, $dpi);
        } elseif ($isPdf) {
            $outputPrefix = $tempDir . DIRECTORY_SEPARATOR . 'first_page';
            $binPath = config('converter.bin_path', 'pdftoppm');
            $timeout = config('converter.timeout', 300);

            // -singlefile converts only page 1 and generates first_page.png
            $command = [
                $binPath,
                '-png',
                '-r',
                (string) $dpi,
                '-singlefile',
                $sourcePath,
                $outputPrefix,
            ];

            $process = new Process($command);
            $process->setTimeout($timeout);

            try {
                $process->run();
            } catch (Exception $e) {
                File::deleteDirectory($tempDir);
                throw new Exception("Gagal menjalankan rasterisasi PDF: " . $e->getMessage());
            }

            if (!$process->isSuccessful()) {
                $errorOutput = trim($process->getErrorOutput() ?: $process->getOutput());
                File::deleteDirectory($tempDir);

                if ($process->getExitCode() === 127 || str_contains(strtolower($errorOutput), 'not found')) {
                    throw new Exception("Engine konversi PDF (pdftoppm/poppler-utils) tidak tersedia pada server.");
                }

                throw new Exception("Rasterisasi PDF gagal. Pastikan file PDF valid dan tidak terenkripsi/rusak. Detail: " . ($errorOutput ?: 'Unknown error'));
            }

            $rasterPath = $outputPrefix . '.png';
            if (!file_exists($rasterPath)) {
                // Fallback check if output produced with digit or alternate name
                $matched = glob($tempDir . DIRECTORY_SEPARATOR . 'first_page*.png');
                if (!empty($matched)) {
                    $rasterPath = $matched[0];
                } else {
                    File::deleteDirectory($tempDir);
                    throw new Exception("Hasil rasterisasi PDF tidak ditemukan.");
                }
            }
        } else {
            // PNG or JPG/JPEG input
            $targetExt = in_array($extension, ['jpg', 'jpeg'], true) ? 'jpg' : 'png';
            $rasterPath = $tempDir . DIRECTORY_SEPARATOR . "input_raster.{$targetExt}";
            copy($sourcePath, $rasterPath);
        }

        // Validate image dimensions via getimagesize
        $imgInfo = @getimagesize($rasterPath);
        if ($imgInfo === false) {
            File::deleteDirectory($tempDir);
            throw new Exception("File gambar tidak dapat dibaca atau rusak.");
        }

        $width = $imgInfo[0];
        $height = $imgInfo[1];

        return [
            'rasterPath' => $rasterPath,
            'originalName' => $originalName,
            'safeName' => $safeName,
            'isPdf' => $isPdf,
            'isCdr' => $isCdr,
            'width' => $width,
            'height' => $height,
            'tempDir' => $tempDir,
        ];
    }

    /**
     * Check whether an external CDR rendering engine (such as Inkscape CLI) is installed on the host.
     *
     * @return array{available: bool, binary: ?string, version: ?string}
     */
    public function getCdrEngineStatus(): array
    {
        $binaries = ['inkscape'];
        if (PHP_OS_FAMILY === 'Windows') {
            $binaries[] = 'C:\\Program Files\\Inkscape\\bin\\inkscape.exe';
            $binaries[] = 'C:\\Program Files\\Inkscape\\inkscape.exe';
        }

        foreach ($binaries as $bin) {
            $process = new Process([$bin, '--version']);
            try {
                $process->run();
                if ($process->isSuccessful()) {
                    return [
                        'available' => true,
                        'binary' => $bin,
                        'version' => trim($process->getOutput()),
                    ];
                }
            } catch (Exception $e) {
                // Not found or cannot execute
            }
        }

        return [
            'available' => false,
            'binary' => null,
            'version' => null,
        ];
    }

    /**
     * Validate and convert CorelDRAW (.cdr) file to an intermediate raster image.
     *
     * @throws Exception
     */
    protected function convertCdrToRaster(string $sourcePath, string $tempDir, int $dpi): string
    {
        // 1. Validate CDR magic bytes (either modern ZIP PK.. or legacy RIFF....CDR)
        $this->validateCdrHeader($sourcePath);

        // 2. Check engine availability
        $engine = $this->getCdrEngineStatus();
        if (!$engine['available']) {
            File::deleteDirectory($tempDir);
            throw new Exception(
                "File CorelDRAW (.cdr) terdeteksi valid, namun server belum memiliki engine CLI converter (Inkscape / libcdr). " .
                "Untuk saat ini, silakan ekspor desain Anda dari CorelDRAW ke format PDF (Press Quality / 300 DPI) atau PNG beresolusi tinggi sebelum diunggah."
            );
        }

        // 3. Convert CDR -> PDF intermediate using Inkscape CLI
        $intermediatePdf = $tempDir . DIRECTORY_SEPARATOR . 'cdr_intermediate.pdf';
        $inkscapeCmd = [
            $engine['binary'],
            $sourcePath,
            '--export-filename=' . $intermediatePdf,
            '--export-dpi=' . $dpi,
        ];

        $process = new Process($inkscapeCmd);
        $process->setTimeout(180);
        $process->run();

        if (!$process->isSuccessful() || !file_exists($intermediatePdf)) {
            File::deleteDirectory($tempDir);
            $err = trim($process->getErrorOutput() ?: $process->getOutput());
            throw new Exception("Gagal mengonversi file CDR melalui engine vector: " . ($err ?: 'Unknown engine error'));
        }

        // 4. Rasterize intermediate PDF via pdftoppm
        $outputPrefix = $tempDir . DIRECTORY_SEPARATOR . 'cdr_raster';
        $binPath = config('converter.bin_path', 'pdftoppm');
        $timeout = config('converter.timeout', 300);

        $pdftoppmCmd = [
            $binPath,
            '-png',
            '-r',
            (string) $dpi,
            '-singlefile',
            $intermediatePdf,
            $outputPrefix,
        ];

        $pdfProcess = new Process($pdftoppmCmd);
        $pdfProcess->setTimeout($timeout);
        $pdfProcess->run();

        $rasterPath = $outputPrefix . '.png';
        if (!file_exists($rasterPath)) {
            File::deleteDirectory($tempDir);
            throw new Exception("Gagal me-rasterize intermediate vector PDF dari CorelDRAW.");
        }

        return $rasterPath;
    }

    /**
     * Inspect file header to verify genuine CorelDRAW format.
     *
     * @throws Exception
     */
    protected function validateCdrHeader(string $path): void
    {
        $fp = @fopen($path, 'rb');
        if (!$fp) {
            throw new Exception("Tidak dapat membaca file CDR.");
        }

        $header = fread($fp, 32);
        fclose($fp);

        if (strlen($header) < 4) {
            throw new Exception("File CorelDRAW rusak atau terlalu kecil.");
        }

        // Check if ZIP container (CorelDRAW X5+)
        $isZip = (substr($header, 0, 4) === "PK\x03\x04");
        // Check if RIFF container (CorelDRAW 7 - X4)
        $isRiff = (substr($header, 0, 4) === "RIFF" && substr($header, 8, 4) === "CDR ");

        if (!$isZip && !$isRiff) {
            throw new Exception("File yang diunggah bukan file CorelDRAW (.cdr) yang sah (header invalid).");
        }
    }

    /**
     * Purge temporary raster directories older than 15 minutes.
     */
    protected function purgeOldRasterDirectories(): void
    {
        $tempBase = storage_path('app/temp');
        if (!File::isDirectory($tempBase)) {
            return;
        }

        $directories = glob($tempBase . DIRECTORY_SEPARATOR . 'raster_*', GLOB_ONLYDIR);
        $threshold = time() - 900; // 15 minutes ago

        if ($directories) {
            foreach ($directories as $dir) {
                if (filemtime($dir) < $threshold) {
                    File::deleteDirectory($dir);
                }
            }
        }
    }
}

