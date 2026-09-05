<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ColorSeparationService
{
    /**
     * Screen Print Separation modes catalog and roadmap status.
     */
    public const SCREEN_PRINT_MODES = [
        'cmyk' => [
            'label' => 'CMYK Process',
            'desc' => 'Separasi proses 4 warna (Cyan, Magenta, Yellow, Black) standar cetak offset / sablon raster halftone.',
            'status' => 'ready',
        ],
        'grayscale' => [
            'label' => 'Luminosity / Grayscale',
            'desc' => 'Film 1 channel densitas monokrom untuk sablon 1 warna / cetak BW.',
            'status' => 'ready',
        ],
        'underbase' => [
            'label' => 'White Underbase',
            'desc' => 'Dasaran tinta putih khusus sablon kaos gelap (dark garment). Dilengkapi kontrol Choke (mm) untuk mencegah peaking.',
            'status' => 'ready',
        ],
        'outline' => [
            'label' => 'Contour & Outline',
            'desc' => 'Ekstraksi garis kontur / keyline berbasis konvolusi deteksi tepi (edge detection).',
            'status' => 'ready',
        ],
        'rgb' => [
            'label' => 'RGB Channels',
            'desc' => 'Separasi 3 kanal digital (Red, Green, Blue) untuk analisis warna aditif.',
            'status' => 'ready',
        ],
        'spot' => [
            'label' => 'Spot / Color Match (Eksperimental)',
            'desc' => 'Isolasi warna spot berbasis k-means clustering palet. Cocok untuk simulasi sablon multi-warna solid (2-8 warna).',
            'status' => 'experimental',
        ],
    ];

    /**
     * Perform color separation on a rasterized image file using PHP GD.
     *
     * @param  string  $rasterPath  Path to the raster image (PNG or JPG)
     * @param  string  $safeName  Slug base name for files
     * @param  string  $mode  'grayscale', 'rgb', or 'cmyk'
     * @param  int  $dpi  150, 300, or 600
     * @return array{
     *     token: string,
     *     mode: string,
     *     dpi: int,
     *     width: int,
     *     height: int,
     *     channels: array<string, array{file: string, label: string, path: string}>,
     *     zipPath: ?string,
     *     zipFileName: ?string,
     *     originalPath: string,
     *     tempDir: string
     * }
     *
     * @throws Exception
     */
    public function separate(
        string $rasterPath,
        string $safeName,
        string $mode = 'cmyk',
        int $dpi = 300,
        array $options = []
    ): array {
        $this->purgeOldSeparationDirectories();

        if (!file_exists($rasterPath)) {
            throw new Exception("File raster tidak ditemukan: {$rasterPath}");
        }

        $mode = strtolower($mode);
        if (!in_array($mode, ['grayscale', 'rgb', 'cmyk', 'underbase', 'outline', 'spot'], true)) {
            throw new Exception("Separation mode '{$mode}' tidak valid. Pilihan: cmyk, grayscale, underbase, outline, rgb, spot.");
        }

        $chokeMm = isset($options['choke_mm']) ? max(0.0, (float) $options['choke_mm']) : 0.0;
        $trapMm = isset($options['trap_mm']) ? max(0.0, (float) $options['trap_mm']) : 0.0;
        $spotColorsCount = isset($options['spot_colors']) ? max(2, min(8, (int) $options['spot_colors'])) : 4;
        $withRegMarks = !empty($options['registration_marks']);

        $token = Str::random(24);
        $tempDir = storage_path('app/temp/separation_' . $token);
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true, true);
        }

        // Copy original image into separation directory for preview & reference
        $originalExt = strtolower(pathinfo($rasterPath, PATHINFO_EXTENSION)) ?: 'png';
        $originalDest = $tempDir . DIRECTORY_SEPARATOR . "original.{$originalExt}";
        copy($rasterPath, $originalDest);

        // Load image using GD
        $imageData = file_get_contents($rasterPath);
        $srcImg = @imagecreatefromstring($imageData);
        unset($imageData);

        if (!$srcImg) {
            File::deleteDirectory($tempDir);
            throw new Exception("Gagal memuat citra menggunakan PHP GD. File mungkin rusak atau format tidak didukung.");
        }

        // Ensure truecolor
        if (!imageistruecolor($srcImg)) {
            imagepalettetotruecolor($srcImg);
        }

        $width = imagesx($srcImg);
        $height = imagesy($srcImg);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($srcImg);
            File::deleteDirectory($tempDir);
            throw new Exception("Dimensi citra tidak valid.");
        }

        // Helper to allocate 256 grayscale palette in truecolor image
        $createGrayscalePalette = function ($im): array {
            $grays = [];
            for ($i = 0; $i < 256; $i++) {
                $grays[$i] = imagecolorallocate($im, $i, $i, $i);
            }
            return $grays;
        };

        $channels = [];
        $zipPath = null;
        $zipFileName = null;

        if ($mode === 'grayscale') {
            $grayImg = imagecreatetruecolor($width, $height);
            $grays = $createGrayscalePalette($grayImg);

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($srcImg, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    // Standard luminance Rec. 601
                    $val = (int) round(0.299 * $r + 0.587 * $g + 0.114 * $b);
                    imagesetpixel($grayImg, $x, $y, $grays[$val]);
                }
            }

            $channelFile = "grayscale.png";
            $channelPath = $tempDir . DIRECTORY_SEPARATOR . $channelFile;
            imagepng($grayImg, $channelPath, 6);
            imagedestroy($grayImg);

            $channels['gray'] = [
                'file' => $channelFile,
                'label' => 'Grayscale',
                'path' => $channelPath,
                'color' => '#111827',
            ];
        } elseif ($mode === 'rgb') {
            $rImg = imagecreatetruecolor($width, $height);
            $gImg = imagecreatetruecolor($width, $height);
            $bImg = imagecreatetruecolor($width, $height);

            $graysR = $createGrayscalePalette($rImg);
            $graysG = $createGrayscalePalette($gImg);
            $graysB = $createGrayscalePalette($bImg);

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($srcImg, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    imagesetpixel($rImg, $x, $y, $graysR[$r]);
                    imagesetpixel($gImg, $x, $y, $graysG[$g]);
                    imagesetpixel($bImg, $x, $y, $graysB[$b]);
                }
            }

            $rFile = "r.png";
            $gFile = "g.png";
            $bFile = "b.png";

            $rPath = $tempDir . DIRECTORY_SEPARATOR . $rFile;
            $gPath = $tempDir . DIRECTORY_SEPARATOR . $gFile;
            $bPath = $tempDir . DIRECTORY_SEPARATOR . $bFile;

            imagepng($rImg, $rPath, 6);
            imagepng($gImg, $gPath, 6);
            imagepng($bImg, $bPath, 6);

            imagedestroy($rImg);
            imagedestroy($gImg);
            imagedestroy($bImg);

            $channels = [
                'r' => ['file' => $rFile, 'label' => 'Red (R)', 'path' => $rPath, 'color' => '#EF4444'],
                'g' => ['file' => $gFile, 'label' => 'Green (G)', 'path' => $gPath, 'color' => '#22C55E'],
                'b' => ['file' => $bFile, 'label' => 'Blue (B)', 'path' => $bPath, 'color' => '#3B82F6'],
            ];
        } elseif ($mode === 'cmyk') {
            $cImg = imagecreatetruecolor($width, $height);
            $mImg = imagecreatetruecolor($width, $height);
            $yImg = imagecreatetruecolor($width, $height);
            $kImg = imagecreatetruecolor($width, $height);

            $graysC = $createGrayscalePalette($cImg);
            $graysM = $createGrayscalePalette($mImg);
            $graysY = $createGrayscalePalette($yImg);
            $graysK = $createGrayscalePalette($kImg);

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($srcImg, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    $rf = $r / 255.0;
                    $gf = $g / 255.0;
                    $bf = $b / 255.0;

                    $kVal = 1.0 - max($rf, $gf, $bf);
                    if ($kVal >= 0.9999) {
                        $cVal = 0.0;
                        $mVal = 0.0;
                        $yVal = 0.0;
                    } else {
                        $denom = 1.0 - $kVal;
                        $cVal = ($denom - $rf) / $denom;
                        $mVal = ($denom - $gf) / $denom;
                        $yVal = ($denom - $bf) / $denom;
                    }

                    // Film Positive Prepress Convention:
                    // 100% ink coverage = Black (0), 0% ink (paper) = White (255)
                    $cGray = (int) round((1.0 - $cVal) * 255);
                    $mGray = (int) round((1.0 - $mVal) * 255);
                    $yGray = (int) round((1.0 - $yVal) * 255);
                    $kGray = (int) round((1.0 - $kVal) * 255);

                    imagesetpixel($cImg, $x, $y, $graysC[$cGray]);
                    imagesetpixel($mImg, $x, $y, $graysM[$mGray]);
                    imagesetpixel($yImg, $x, $y, $graysY[$yGray]);
                    imagesetpixel($kImg, $x, $y, $graysK[$kGray]);
                }
            }

            // Apply Trap (dilation) between CMYK colors if requested
            if ($trapMm > 0.0) {
                $trapPixels = $this->mmToPixels($trapMm, $dpi);
                $this->applyTrap($cImg, $width, $height, $trapPixels, $graysC);
                $this->applyTrap($mImg, $width, $height, $trapPixels, $graysM);
                $this->applyTrap($yImg, $width, $height, $trapPixels, $graysY);
            }

            $cFile = "c.png";
            $mFile = "m.png";
            $yFile = "y.png";
            $kFile = "k.png";

            $cPath = $tempDir . DIRECTORY_SEPARATOR . $cFile;
            $mPath = $tempDir . DIRECTORY_SEPARATOR . $mFile;
            $yPath = $tempDir . DIRECTORY_SEPARATOR . $yFile;
            $kPath = $tempDir . DIRECTORY_SEPARATOR . $kFile;

            imagepng($cImg, $cPath, 6);
            imagepng($mImg, $mPath, 6);
            imagepng($yImg, $yPath, 6);
            imagepng($kImg, $kPath, 6);

            imagedestroy($cImg);
            imagedestroy($mImg);
            imagedestroy($yImg);
            imagedestroy($kImg);

            $channels = [
                'c' => ['file' => $cFile, 'label' => 'Cyan (C)', 'path' => $cPath, 'color' => '#00A3E0'],
                'm' => ['file' => $mFile, 'label' => 'Magenta (M)', 'path' => $mPath, 'color' => '#EC008C'],
                'y' => ['file' => $yFile, 'label' => 'Yellow (Y)', 'path' => $yPath, 'color' => '#FFF200'],
                'k' => ['file' => $kFile, 'label' => 'Key / Black (K)', 'path' => $kPath, 'color' => '#111827'],
            ];
        } elseif ($mode === 'underbase') {
            $ubImg = imagecreatetruecolor($width, $height);
            $graysUb = $createGrayscalePalette($ubImg);

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $rgba = imagecolorat($srcImg, $x, $y);
                    $a = ($rgba >> 24) & 0x7F; // 0 (opaque) to 127 (transparent in GD)
                    $r = ($rgba >> 16) & 0xFF;
                    $g = ($rgba >> 8) & 0xFF;
                    $b = $rgba & 0xFF;

                    if ($a >= 120) {
                        imagesetpixel($ubImg, $x, $y, $graysUb[255]);
                        continue;
                    }

                    // Luminance calculation
                    $lum = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255.0;
                    $alphaFactor = (127 - $a) / 127.0;

                    // Film Positive: 100% ink coverage = 0 (Black), 0% ink = 255 (White)
                    // If pixel is near pure black (e.g. background of dark garment), do not ink underbase
                    $isDarkBackground = ($r < 18 && $g < 18 && $b < 18);
                    if ($isDarkBackground) {
                        $ubGray = 255;
                    } else {
                        $inkCoverage = max(0.0, min(1.0, (1.0 - $lum * 0.4) * $alphaFactor));
                        $ubGray = (int) round((1.0 - $inkCoverage) * 255);
                    }

                    imagesetpixel($ubImg, $x, $y, $graysUb[$ubGray]);
                }
            }

            // Apply Underbase Choke (in mm) if requested (> 0)
            if ($chokeMm > 0.0) {
                $chokePixels = $this->mmToPixels($chokeMm, $dpi);
                $this->applyChoke($ubImg, $width, $height, $chokePixels, $graysUb);
            }

            $ubFile = "underbase.png";
            $ubPath = $tempDir . DIRECTORY_SEPARATOR . $ubFile;
            imagepng($ubImg, $ubPath, 6);
            imagedestroy($ubImg);

            $channels['underbase'] = [
                'file' => $ubFile,
                'label' => 'White Underbase (Dasar Putih Sablon)',
                'path' => $ubPath,
                'color' => '#FFFFFF',
            ];
        } elseif ($mode === 'outline') {
            $grayBase = imagecreatetruecolor($width, $height);
            $grays = $createGrayscalePalette($grayBase);

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($srcImg, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $lum = (int) round(0.299 * $r + 0.587 * $g + 0.114 * $b);
                    imagesetpixel($grayBase, $x, $y, $grays[$lum]);
                }
            }

            $matrix = [
                [-1, -1, -1],
                [-1,  8, -1],
                [-1, -1, -1]
            ];
            imageconvolution($grayBase, $matrix, 1, 0);

            $outlineImg = imagecreatetruecolor($width, $height);
            $graysOut = $createGrayscalePalette($outlineImg);

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $c = imagecolorat($grayBase, $x, $y) & 0xFF;
                    $edgeVal = min(255, $c * 3);
                    $filmVal = 255 - $edgeVal; // Black ink on white film
                    imagesetpixel($outlineImg, $x, $y, $graysOut[$filmVal]);
                }
            }
            imagedestroy($grayBase);

            // Apply Trap (dilation) on outline if requested
            if ($trapMm > 0.0) {
                $trapPixels = $this->mmToPixels($trapMm, $dpi);
                $this->applyTrap($outlineImg, $width, $height, $trapPixels, $graysOut);
            }

            $outFile = "outline.png";
            $outPath = $tempDir . DIRECTORY_SEPARATOR . $outFile;
            imagepng($outlineImg, $outPath, 6);
            imagedestroy($outlineImg);

            $channels['outline'] = [
                'file' => $outFile,
                'label' => 'Contour & Outline (Garis Luar Sablon)',
                'path' => $outPath,
                'color' => '#111827',
            ];
        } elseif ($mode === 'spot') {
            // Experimental Spot Color / Color Match Separation via Color Quantization & Distance
            $channels = $this->separateSpotColors(
                $srcImg,
                $width,
                $height,
                $spotColorsCount,
                $tempDir,
                $createGrayscalePalette,
                $trapMm,
                $dpi
            );
        }

        imagedestroy($srcImg);

        // Apply Registration Marks if requested
        if ($withRegMarks) {
            foreach ($channels as $key => $chan) {
                $this->addRegistrationMarks($chan['path'], $chan['label']);
            }
        }

        // Multi-channel ZIP generation
        if (count($channels) > 1) {
            $zipFileName = "{$safeName}_{$mode}_separation_{$dpi}dpi.zip";
            $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($channels as $key => $chan) {
                    $zip->addFile($chan['path'], "{$safeName}_{$chan['file']}");
                }
                $zip->close();
            } else {
                $zipPath = null;
                $zipFileName = null;
            }
        }

        // Save manifest to disk
        $manifest = [
            'token' => $token,
            'safeName' => $safeName,
            'mode' => $mode,
            'dpi' => $dpi,
            'width' => $width,
            'height' => $height,
            'originalFile' => "original.{$originalExt}",
            'channels' => $channels,
            'zipFileName' => $zipFileName,
            'createdAt' => time(),
        ];
        file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        return [
            'token' => $token,
            'mode' => $mode,
            'dpi' => $dpi,
            'width' => $width,
            'height' => $height,
            'channels' => $channels,
            'zipPath' => $zipPath,
            'zipFileName' => $zipFileName,
            'originalPath' => $originalDest,
            'tempDir' => $tempDir,
        ];
    }

    /**
     * Convert millimeters to pixels at a given DPI.
     * Formula: pixels = round(mm * (dpi / 25.4))
     */
    public function mmToPixels(float $mm, int $dpi): int
    {
        return (int) round($mm * ($dpi / 25.4));
    }

    /**
     * Apply morphological erosion (Choke) on a film positive grayscale mask.
     * In film positive convention: Black (0) = Ink, White (255) = No ink.
     * To shrink/choke the ink boundary, we perform a morphological MAXIMUM on pixel values
     * (i.e. brighter pixels expand into darker pixels, shrinking the black ink boundary inward).
     */
    protected function applyChoke($im, int $width, int $height, int $radius, array $palette): void
    {
        if ($radius <= 0) {
            return;
        }

        $radius = min($radius, 15); // Safety clamp to avoid excessive computation
        $clone = imagecreatetruecolor($width, $height);
        imagecopy($clone, $im, 0, 0, 0, 0, $width, $height);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $maxVal = 0;
                for ($dy = -$radius; $dy <= $radius; $dy++) {
                    $ny = $y + $dy;
                    if ($ny < 0 || $ny >= $height) {
                        continue;
                    }
                    for ($dx = -$radius; $dx <= $radius; $dx++) {
                        if (($dx * $dx + $dy * $dy) > ($radius * $radius)) {
                            continue;
                        }
                        $nx = $x + $dx;
                        if ($nx < 0 || $nx >= $width) {
                            continue;
                        }
                        $val = imagecolorat($clone, $nx, $ny) & 0xFF;
                        if ($val > $maxVal) {
                            $maxVal = $val;
                        }
                    }
                }
                imagesetpixel($im, $x, $y, $palette[$maxVal]);
            }
        }
        imagedestroy($clone);
    }

    /**
     * Apply morphological dilation (Trap) on a film positive grayscale mask.
     * In film positive convention: Black (0) = Ink, White (255) = No ink.
     * To expand/trap the ink boundary, we perform a morphological MINIMUM on pixel values
     * (i.e. darker pixels expand into lighter pixels, spreading ink outward).
     */
    protected function applyTrap($im, int $width, int $height, int $radius, array $palette): void
    {
        if ($radius <= 0) {
            return;
        }

        $radius = min($radius, 15); // Safety clamp
        $clone = imagecreatetruecolor($width, $height);
        imagecopy($clone, $im, 0, 0, 0, 0, $width, $height);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $minVal = 255;
                for ($dy = -$radius; $dy <= $radius; $dy++) {
                    $ny = $y + $dy;
                    if ($ny < 0 || $ny >= $height) {
                        continue;
                    }
                    for ($dx = -$radius; $dx <= $radius; $dx++) {
                        if (($dx * $dx + $dy * $dy) > ($radius * $radius)) {
                            continue;
                        }
                        $nx = $x + $dx;
                        if ($nx < 0 || $nx >= $width) {
                            continue;
                        }
                        $val = imagecolorat($clone, $nx, $ny) & 0xFF;
                        if ($val < $minVal) {
                            $minVal = $val;
                        }
                    }
                }
                imagesetpixel($im, $x, $y, $palette[$minVal]);
            }
        }
        imagedestroy($clone);
    }

    /**
     * Experimental Spot Color / Color Match Separation.
     * Samples image colors, clusters into $k dominant colors, and outputs 1 grayscale mask per color.
     */
    protected function separateSpotColors(
        $srcImg,
        int $width,
        int $height,
        int $k,
        string $tempDir,
        callable $createGrayscalePalette,
        float $trapMm,
        int $dpi
    ): array {
        // 1. Sample up to 2,000 opaque pixels to detect dominant color clusters
        $samples = [];
        $stepX = max(1, (int) floor($width / 50));
        $stepY = max(1, (int) floor($height / 50));

        for ($y = 0; $y < $height; $y += $stepY) {
            for ($x = 0; $x < $width; $x += $stepX) {
                $rgba = imagecolorat($srcImg, $x, $y);
                $a = ($rgba >> 24) & 0x7F;
                if ($a > 100) {
                    continue; // Skip transparent
                }
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $samples[] = [$r, $g, $b];
            }
        }

        if (empty($samples)) {
            // Fallback sample
            $samples = [[255, 0, 0], [0, 255, 0], [0, 0, 255], [0, 0, 0]];
        }

        // Initialize centroids
        $numSamples = count($samples);
        $centroids = [];
        $stride = max(1, (int) floor($numSamples / $k));
        for ($i = 0; $i < $k; $i++) {
            $idx = min($numSamples - 1, $i * $stride);
            $centroids[$i] = $samples[$idx];
        }

        // 3 iterations of K-Means clustering
        for ($iter = 0; $iter < 3; $iter++) {
            $clusters = array_fill(0, $k, []);
            foreach ($samples as $pixel) {
                $bestDist = PHP_INT_MAX;
                $bestCluster = 0;
                foreach ($centroids as $cIdx => $c) {
                    $dr = $pixel[0] - $c[0];
                    $dg = $pixel[1] - $c[1];
                    $db = $pixel[2] - $c[2];
                    $dist = $dr * $dr + $dg * $dg + $db * $db;
                    if ($dist < $bestDist) {
                        $bestDist = $dist;
                        $bestCluster = $cIdx;
                    }
                }
                $clusters[$bestCluster][] = $pixel;
            }

            foreach ($clusters as $cIdx => $clusterPixels) {
                if (empty($clusterPixels)) {
                    continue;
                }
                $sumR = 0;
                $sumG = 0;
                $sumB = 0;
                $count = count($clusterPixels);
                foreach ($clusterPixels as $p) {
                    $sumR += $p[0];
                    $sumG += $p[1];
                    $sumB += $p[2];
                }
                $centroids[$cIdx] = [
                    (int) round($sumR / $count),
                    (int) round($sumG / $count),
                    (int) round($sumB / $count),
                ];
            }
        }

        // Create images for each spot color channel
        $spotImages = [];
        $palettes = [];
        for ($i = 0; $i < $k; $i++) {
            $im = imagecreatetruecolor($width, $height);
            $p = $createGrayscalePalette($im);
            // Default all white (no ink)
            imagefilledrectangle($im, 0, 0, $width, $height, $p[255]);
            $spotImages[$i] = $im;
            $palettes[$i] = $p;
        }

        // Assign each pixel in the source image to closest spot color
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($srcImg, $x, $y);
                $a = ($rgba >> 24) & 0x7F;
                if ($a > 100) {
                    continue; // Skip transparent
                }
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                $bestDist = PHP_INT_MAX;
                $bestIdx = 0;
                foreach ($centroids as $cIdx => $c) {
                    $dr = $r - $c[0];
                    $dg = $g - $c[1];
                    $db = $b - $c[2];
                    $dist = $dr * $dr + $dg * $dg + $db * $db;
                    if ($dist < $bestDist) {
                        $bestDist = $dist;
                        $bestIdx = $cIdx;
                    }
                }

                // Film positive: 0 = Ink, 255 = No ink
                imagesetpixel($spotImages[$bestIdx], $x, $y, $palettes[$bestIdx][0]);
            }
        }

        $channels = [];
        $trapPixels = ($trapMm > 0.0) ? $this->mmToPixels($trapMm, $dpi) : 0;

        for ($i = 0; $i < $k; $i++) {
            $hexColor = sprintf("#%02X%02X%02X", $centroids[$i][0], $centroids[$i][1], $centroids[$i][2]);
            $channelKey = "spot_" . ($i + 1);
            $fileName = "spot_{$i}_{$hexColor}.png";
            $fileName = str_replace('#', '', $fileName);
            $filePath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

            if ($trapPixels > 0) {
                $this->applyTrap($spotImages[$i], $width, $height, $trapPixels, $palettes[$i]);
            }

            imagepng($spotImages[$i], $filePath, 6);
            imagedestroy($spotImages[$i]);

            $channels[$channelKey] = [
                'file' => $fileName,
                'label' => "Spot Color #" . ($i + 1) . " ({$hexColor})",
                'path' => $filePath,
                'color' => $hexColor,
            ];
        }

        return $channels;
    }

    /**
     * Add registration targets (crosshairs ⊕) and channel label text onto the film margin.
     */
    protected function addRegistrationMarks(string $filePath, string $channelLabel): void
    {
        $im = @imagecreatefrompng($filePath);
        if (!$im) {
            return;
        }

        $w = imagesx($im);
        $h = imagesy($im);
        $margin = 25;
        $newW = $w + ($margin * 2);
        $newH = $h + ($margin * 2);

        $out = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($out, 255, 255, 255);
        $black = imagecolorallocate($out, 0, 0, 0);

        imagefilledrectangle($out, 0, 0, $newW, $newH, $white);
        imagecopy($out, $im, $margin, $margin, 0, 0, $w, $h);

        // Draw registration crosshairs in 4 corners
        $drawCrosshair = function ($cx, $cy) use ($out, $black) {
            $r = 7;
            imagearc($out, $cx, $cy, $r * 2, $r * 2, 0, 360, $black);
            imageline($out, $cx - $r - 3, $cy, $cx + $r + 3, $cy, $black);
            imageline($out, $cx, $cy - $r - 3, $cx, $cy + $r + 3, $black);
        };

        $drawCrosshair(12, 12);
        $drawCrosshair($newW - 13, 12);
        $drawCrosshair(12, $newH - 13);
        $drawCrosshair($newW - 13, $newH - 13);

        // Label text at bottom margin
        imagestring($out, 2, 30, $newH - 18, $channelLabel, $black);

        imagepng($out, $filePath, 6);
        imagedestroy($out);
        imagedestroy($im);
    }

    /**
     * Retrieve manifest by separation token.
     *
     * @param  string  $token
     * @return array|null
     */
    public function getManifest(string $token): ?array
    {
        $manifestPath = storage_path("app/temp/separation_{$token}/manifest.json");
        if (!file_exists($manifestPath)) {
            return null;
        }

        $json = file_get_contents($manifestPath);
        return json_decode($json, true);
    }

    /**
     * Purge temporary separation directories older than 30 minutes.
     */
    protected function purgeOldSeparationDirectories(): void
    {
        $tempBase = storage_path('app/temp');
        if (!File::isDirectory($tempBase)) {
            return;
        }

        $directories = glob($tempBase . DIRECTORY_SEPARATOR . 'separation_*', GLOB_ONLYDIR);
        $threshold = time() - 1800; // 30 minutes ago

        if ($directories) {
            foreach ($directories as $dir) {
                if (filemtime($dir) < $threshold) {
                    File::deleteDirectory($dir);
                }
            }
        }
    }
}
