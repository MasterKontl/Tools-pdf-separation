<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageUpscaleService
{
    /**
     * Maximum input file size in KB.
     */
    public function maxFileSizeKb(): int
    {
        return (int) env('UPSCALE_MAX_FILE_SIZE_KB', 10240); // 10 MB
    }

    /**
     * Maximum total pixels for input image (width × height).
     */
    public function maxInputPixels(): int
    {
        return (int) env('UPSCALE_MAX_PIXELS', 25_000_000); // 25 MP
    }

    /**
     * Maximum total pixels for output image after scaling.
     */
    public function maxOutputPixels(): int
    {
        return (int) env('UPSCALE_MAX_OUTPUT_PIXELS', 100_000_000); // 100 MP
    }

    /**
     * Allowed MIME types (validated server-side against actual file content).
     */
    public function allowedMimes(): array
    {
        return ['image/jpeg', 'image/png', 'image/webp'];
    }

    /**
     * Allowed extensions.
     */
    public function allowedExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'webp'];
    }

    /**
     * Read image dimensions without loading entire image into memory.
     *
     * @return array{width: int, height: int}
     * @throws Exception
     */
    public function getImageDimensions(string $path): array
    {
        $info = @getimagesize($path);
        if ($info === false || !isset($info[0], $info[1])) {
            throw new Exception('Gagal membaca dimensi gambar. File mungkin rusak atau bukan gambar valid.');
        }

        return [
            'width' => $info[0],
            'height' => $info[1],
        ];
    }

    /**
     * Validate the uploaded image before processing.
     *
     * @throws Exception
     */
    public function validateImage(string $path, string $originalName): array
    {
        // 1. Validate MIME from actual content (not client header)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path);
        if (!in_array($mime, $this->allowedMimes(), true)) {
            throw new Exception("Format gambar tidak didukung ({$mime}). Gunakan JPG, PNG, atau WEBP.");
        }

        // 2. Validate extension
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions(), true)) {
            throw new Exception("Ekstensi file tidak diizinkan (.{$ext}). Gunakan .jpg, .png, atau .webp.");
        }

        // 3. File size
        $sizeKb = filesize($path) / 1024;
        if ($sizeKb > $this->maxFileSizeKb()) {
            $maxMb = round($this->maxFileSizeKb() / 1024, 1);
            throw new Exception("Ukuran file terlalu besar (" . round($sizeKb / 1024, 1) . " MB). Maksimum {$maxMb} MB.");
        }

        // 4. Dimensions and pixel count
        $dims = $this->getImageDimensions($path);
        $pixels = $dims['width'] * $dims['height'];
        if ($pixels > $this->maxInputPixels()) {
            $maxMp = round($this->maxInputPixels() / 1_000_000, 1);
            throw new Exception("Resolusi gambar terlalu besar ({$dims['width']}×{$dims['height']} = " . round($pixels / 1_000_000, 1) . " MP). Maksimum {$maxMp} MP.");
        }

        return [
            'mime' => $mime,
            'extension' => $ext,
            'width' => $dims['width'],
            'height' => $dims['height'],
            'pixels' => $pixels,
            'fileSize' => (int) filesize($path),
        ];
    }

    /**
     * Validate output dimensions won't exceed limits.
     *
     * @throws Exception
     */
    public function validateOutputSize(int $width, int $height, int $scale): void
    {
        $outW = $width * $scale;
        $outH = $height * $scale;
        $outPixels = $outW * $outH;

        if ($outPixels > $this->maxOutputPixels()) {
            $maxMp = round($this->maxOutputPixels() / 1_000_000, 1);
            throw new Exception("Hasil upscale {$scale}× ({$outW}×{$outH} = " . round($outPixels / 1_000_000, 1) . " MP) melebihi batas {$maxMp} MP. Gunakan gambar lebih kecil atau skala lebih rendah.");
        }

        // GD memory safety: ~5 bytes per pixel (RGBA + overhead)
        $estimatedMemoryMb = ($outPixels * 5 + $width * $height * 5) / 1024 / 1024;
        $memLimitMb = $this->getMemoryLimitMb();
        if ($memLimitMb > 0 && $estimatedMemoryMb > $memLimitMb * 0.7) {
            throw new Exception("Upscale {$scale}× membutuhkan ~" . round($estimatedMemoryMb) . " MB memory, melebihi batas aman server. Gunakan gambar lebih kecil.");
        }
    }

    /**
     * Upscale image using PHP GD with high-quality bicubic resampling.
     *
     * This is NOT AI super-resolution. It uses standard bicubic interpolation
     * which produces smooth results but does not add detail beyond the original.
     *
     * @param string $inputPath Path to source image
     * @param int $scale 2 or 4
     * @param string $mime Validated MIME type of input
     * @return array{filePath: string, fileName: string, mimeType: string, width: int, height: int, fileSize: int}
     * @throws Exception
     */
    public function upscale(string $inputPath, int $scale, string $mime, string $originalName): array
    {
        if (!in_array($scale, [2, 4], true)) {
            throw new Exception('Skala harus 2× atau 4×.');
        }

        // Create GD resource from input
        $srcImage = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($inputPath),
            'image/png' => @imagecreatefrompng($inputPath),
            'image/webp' => @imagecreatefromwebp($inputPath),
            default => throw new Exception("Format {$mime} tidak dapat diproses."),
        };

        if ($srcImage === false) {
            throw new Exception('Gagal membuka gambar. File mungkin rusak.');
        }

        $srcW = imagesx($srcImage);
        $srcH = imagesy($srcImage);
        $dstW = $srcW * $scale;
        $dstH = $srcH * $scale;

        // Create destination image
        $dstImage = imagecreatetruecolor($dstW, $dstH);
        if ($dstImage === false) {
            imagedestroy($srcImage);
            throw new Exception('Gagal mengalokasikan memory untuk gambar hasil. Coba gambar lebih kecil.');
        }

        // Preserve transparency for PNG and WEBP
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
            imagefill($dstImage, 0, 0, $transparent);
        }

        // High-quality bicubic resampling
        $success = imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagedestroy($srcImage);

        if (!$success) {
            imagedestroy($dstImage);
            throw new Exception('Gagal melakukan resampling gambar.');
        }

        // Determine output format and write to temp file
        $outputDir = storage_path('app/temp');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $tempId = Str::random(20);
        $safeBaseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'image';
        $safeBaseName = Str::limit($safeBaseName, 60, '');

        // Determine output extension/format
        $outputExt = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'png',
        };

        $outputMime = match ($outputExt) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        $tempPath = $outputDir . '/upscale_' . $tempId . '.' . $outputExt;
        $downloadName = $safeBaseName . '-upscaled-' . $scale . 'x.' . $outputExt;

        $writeSuccess = match ($outputExt) {
            'jpg' => imagejpeg($dstImage, $tempPath, 92),
            'png' => imagepng($dstImage, $tempPath, 6),
            'webp' => imagecreatetruecolor($dstW, $dstH) ? imagewebp($dstImage, $tempPath, 90) : false,
            default => imagepng($dstImage, $tempPath, 6),
        };

        imagedestroy($dstImage);

        if (!$writeSuccess || !file_exists($tempPath)) {
            throw new Exception('Gagal menyimpan gambar hasil upscale.');
        }

        return [
            'filePath' => $tempPath,
            'fileName' => $downloadName,
            'mimeType' => $outputMime,
            'width' => $dstW,
            'height' => $dstH,
            'fileSize' => (int) filesize($tempPath),
            'tempId' => $tempId,
            'extension' => $outputExt,
        ];
    }

    /**
     * Get PHP memory_limit in MB.
     */
    protected function getMemoryLimitMb(): int
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1' || $limit === false) {
            return 0; // unlimited
        }

        $unit = strtoupper(substr($limit, -1));
        $value = (int) $limit;

        return match ($unit) {
            'G' => $value * 1024,
            'M' => $value,
            'K' => (int) ($value / 1024),
            default => (int) ($value / 1024 / 1024),
        };
    }

    /**
     * Purge old upscale temp files (older than 15 minutes).
     */
    public function purgeOldTempFiles(): void
    {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            return;
        }

        $cutoff = time() - 900; // 15 minutes
        foreach (glob($tempDir . '/upscale_*') as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }
}
