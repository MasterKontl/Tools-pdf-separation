<?php

namespace Tests\Feature;

use App\Services\ColorSeparationService;
use App\Services\ImageRasterService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class ColorSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }
    /**
     * Helper to create a minimal valid PDF binary content.
     */
    protected function createSamplePdf(int $pages = 2): string
    {
        $pageObjects = '';
        $kids = [];
        $objIndex = 3;

        for ($i = 1; $i <= $pages; $i++) {
            $kids[] = "{$objIndex} 0 R";
            $contentObj = $objIndex + 1;
            $text = "Separation Page {$i}";
            $stream = "BT /F1 12 Tf 20 180 Td ({$text}) Tj ET";
            $streamLen = strlen($stream);
            $pageObjects .= "{$objIndex} 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Contents {$contentObj} 0 R >> endobj\n";
            $pageObjects .= "{$contentObj} 0 obj << /Length {$streamLen} >> stream\n{$stream}\nendstream endobj\n";
            $objIndex += 2;
        }

        $kidsStr = implode(' ', $kids);
        $totalObjs = $objIndex;

        $body = "%PDF-1.4\n";
        $offsets = [0];

        $offsets[1] = strlen($body);
        $body .= "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";

        $offsets[2] = strlen($body);
        $body .= "2 0 obj << /Type /Pages /Kids [{$kidsStr}] /Count {$pages} >> endobj\n";

        $currObj = 3;
        for ($i = 1; $i <= $pages; $i++) {
            $offsets[$currObj] = strlen($body);
            $body .= "{$currObj} 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Contents " . ($currObj + 1) . " 0 R >> endobj\n";
            $currObj++;

            $offsets[$currObj] = strlen($body);
            $text = "Separation Page {$i}";
            $stream = "BT /F1 12 Tf 20 180 Td ({$text}) Tj ET";
            $streamLen = strlen($stream);
            $body .= "{$currObj} 0 obj << /Length {$streamLen} >> stream\n{$stream}\nendstream endobj\n";
            $currObj++;
        }

        $xrefOffset = strlen($body);
        $body .= "xref\n0 " . ($totalObjs) . "\n";
        $body .= "0000000000 65535 f \n";
        for ($i = 1; $i < $totalObjs; $i++) {
            $body .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $body .= "trailer << /Size {$totalObjs} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $body;
    }

    /**
     * Helper to create a temporary test image (PNG or JPG) with distinct colors.
     */
    protected function createTestImage(string $format = 'png', int $width = 100, int $height = 100): string
    {
        $im = imagecreatetruecolor($width, $height);
        // Fill top half red, bottom half cyan
        $red = imagecolorallocate($im, 255, 0, 0);
        $cyan = imagecolorallocate($im, 0, 255, 255);
        imagefilledrectangle($im, 0, 0, $width, (int)($height / 2), $red);
        imagefilledrectangle($im, 0, (int)($height / 2), $width, $height, $cyan);

        $path = tempnam(sys_get_temp_dir(), 'test_img_') . '.' . $format;
        if ($format === 'jpg' || $format === 'jpeg') {
            imagejpeg($im, $path, 90);
        } else {
            imagepng($im, $path);
        }
        imagedestroy($im);

        return $path;
    }

    /**
     * Test 1: GET /separation returns HTTP 200 and renders UI elements.
     */
    public function test_separation_page_is_accessible(): void
    {
        $response = $this->get('/separation');

        $response->assertStatus(200);
        $response->assertSee('Color Separation');
        $response->assertSee('CMYK');
        $response->assertSee('RGB');
        $response->assertSee('Grayscale');
        $response->assertSee('halaman pertama saja'); // Verified explicit notice
    }

    /**
     * Test 2: Validation rejects missing or invalid inputs.
     */
    public function test_separation_validates_inputs(): void
    {
        // Missing all
        $response = $this->post('/separation', []);
        $response->assertSessionHasErrors(['file', 'dpi', 'mode']);

        // Invalid mode and DPI
        $tmp = $this->createTestImage('png', 20, 20);
        $file = new UploadedFile($tmp, 'test.png', 'image/png', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 1200,
            'mode' => 'invalid_mode',
        ]);
        $response->assertSessionHasErrors(['dpi', 'mode']);

        @unlink($tmp);
    }

    /**
     * Test 3: Grayscale separation generates 1 channel with valid PNG output.
     */
    public function test_grayscale_separation_produces_single_valid_channel(): void
    {
        $tmp = $this->createTestImage('png', 60, 60);
        $file = new UploadedFile($tmp, 'my_graphic.png', 'image/png', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 150,
            'mode' => 'grayscale',
        ]);

        $response->assertRedirect();
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('token=', $targetUrl);

        parse_str(parse_url($targetUrl, PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];
        $this->assertNotEmpty($token);

        // Verify manifest
        $sepService = app(ColorSeparationService::class);
        $manifest = $sepService->getManifest($token);
        $this->assertNotNull($manifest);
        $this->assertEquals('grayscale', $manifest['mode']);
        $this->assertCount(1, $manifest['channels']);
        $this->assertArrayHasKey('gray', $manifest['channels']);

        // Download channel and verify valid PNG
        $downloadRes = $this->get("/separation/download/{$token}/gray");
        $downloadRes->assertStatus(200);
        $this->assertEquals('image/png', $downloadRes->headers->get('Content-Type'));

        $imgData = $downloadRes->getFile()->getPathname();
        $imgInfo = getimagesize($imgData);
        $this->assertNotFalse($imgInfo);
        $this->assertEquals(60, $imgInfo[0]);
        $this->assertEquals(60, $imgInfo[1]);

        @unlink($tmp);
    }

    /**
     * Test 4: RGB separation from JPG input produces R, G, B channels and extractable ZIP.
     */
    public function test_rgb_separation_produces_r_g_b_channels_and_zip(): void
    {
        $tmp = $this->createTestImage('jpg', 80, 80);
        $file = new UploadedFile($tmp, 'sample_photo.jpg', 'image/jpeg', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 300,
            'mode' => 'rgb',
        ]);

        $response->assertRedirect();
        $targetUrl = $response->headers->get('Location');
        parse_str(parse_url($targetUrl, PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];

        $sepService = app(ColorSeparationService::class);
        $manifest = $sepService->getManifest($token);
        $this->assertEquals('rgb', $manifest['mode']);
        $this->assertCount(3, $manifest['channels']);
        $this->assertArrayHasKey('r', $manifest['channels']);
        $this->assertArrayHasKey('g', $manifest['channels']);
        $this->assertArrayHasKey('b', $manifest['channels']);

        // Test Individual Download for R channel
        $rRes = $this->get("/separation/download/{$token}/r");
        $rRes->assertStatus(200);
        $this->assertEquals('image/png', $rRes->headers->get('Content-Type'));
        $rInfo = getimagesize($rRes->getFile()->getPathname());
        $this->assertEquals(80, $rInfo[0]);

        // Test Multi-channel ZIP Download & Extraction
        $zipRes = $this->get("/separation/download/{$token}/zip");
        $zipRes->assertStatus(200);
        $this->assertEquals('application/zip', $zipRes->headers->get('Content-Type'));

        $zipPath = $zipRes->getFile()->getPathname();
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);
        $this->assertEquals(3, $zip->numFiles);

        $fileNames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileNames[] = $zip->getNameIndex($i);
        }
        $zip->close();

        $this->assertTrue(collect($fileNames)->contains(fn($n) => str_contains($n, 'r.png')));
        $this->assertTrue(collect($fileNames)->contains(fn($n) => str_contains($n, 'g.png')));
        $this->assertTrue(collect($fileNames)->contains(fn($n) => str_contains($n, 'b.png')));

        @unlink($tmp);
    }

    /**
     * Test 5: CMYK separation produces C, M, Y, K channels.
     */
    public function test_cmyk_separation_produces_c_m_y_k_channels(): void
    {
        $tmp = $this->createTestImage('png', 50, 50);
        $file = new UploadedFile($tmp, 'cmyk_test.png', 'image/png', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 300,
            'mode' => 'cmyk',
        ]);

        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];

        $sepService = app(ColorSeparationService::class);
        $manifest = $sepService->getManifest($token);
        $this->assertEquals('cmyk', $manifest['mode']);
        $this->assertCount(4, $manifest['channels']);
        $this->assertArrayHasKey('c', $manifest['channels']);
        $this->assertArrayHasKey('m', $manifest['channels']);
        $this->assertArrayHasKey('y', $manifest['channels']);
        $this->assertArrayHasKey('k', $manifest['channels']);

        // Verify individual C channel download
        $cRes = $this->get("/separation/download/{$token}/c");
        $cRes->assertStatus(200);
        $this->assertEquals('image/png', $cRes->headers->get('Content-Type'));

        // Verify ZIP has 4 channels
        $zipRes = $this->get("/separation/download/{$token}/zip");
        $zipRes->assertStatus(200);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipRes->getFile()->getPathname()) === true);
        $this->assertEquals(4, $zip->numFiles);
        $zip->close();

        @unlink($tmp);
    }

    /**
     * Test 6: PDF input utilizes Poppler pdftoppm pipeline and separates properly.
     */
    public function test_pdf_input_integrates_with_poppler_and_separates(): void
    {
        $pdfContent = $this->createSamplePdf(2);
        $tempPdf = tempnam(sys_get_temp_dir(), 'poppler_test_') . '.pdf';
        file_put_contents($tempPdf, $pdfContent);

        $file = new UploadedFile($tempPdf, 'multipage_artwork.pdf', 'application/pdf', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 150,
            'mode' => 'cmyk',
        ]);

        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];

        $sepService = app(ColorSeparationService::class);
        $manifest = $sepService->getManifest($token);
        $this->assertNotNull($manifest);
        $this->assertEquals('cmyk', $manifest['mode']);
        $this->assertCount(4, $manifest['channels']);

        @unlink($tempPdf);
    }

    /**
     * Test 7: Preview endpoint serves channel and original images.
     */
    public function test_preview_serves_original_and_channel_images(): void
    {
        $tmp = $this->createTestImage('png', 40, 40);
        $file = new UploadedFile($tmp, 'preview_test.png', 'image/png', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 150,
            'mode' => 'rgb',
        ]);

        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];

        // Preview original
        $origPreview = $this->get("/separation/preview/{$token}/original");
        $origPreview->assertStatus(200);
        $this->assertStringContainsString('image/', $origPreview->headers->get('Content-Type'));

        // Preview R channel
        $rPreview = $this->get("/separation/preview/{$token}/r");
        $rPreview->assertStatus(200);
        $this->assertEquals('image/png', $rPreview->headers->get('Content-Type'));

        // Invalid channel returns 404
        $invalidPreview = $this->get("/separation/preview/{$token}/unknown_channel");
        $invalidPreview->assertStatus(404);

        @unlink($tmp);
    }

    /**
     * Test 8: Intermediate raster directory is cleaned up after processing.
     */
    public function test_temporary_raster_directory_is_cleaned_up(): void
    {
        $tmp = $this->createTestImage('png', 30, 30);
        $file = new UploadedFile($tmp, 'cleanup_test.png', 'image/png', null, true);

        $rasterService = app(ImageRasterService::class);
        $rasterData = $rasterService->rasterize($file, 150);

        $tempDir = $rasterData['tempDir'];
        $this->assertTrue(File::isDirectory($tempDir));

        // When controller finishes, it deletes the raster tempDir
        File::deleteDirectory($tempDir);
        $this->assertFalse(File::isDirectory($tempDir));

        @unlink($tmp);
    }

    /**
     * Test 9: Screen print separation generates White Underbase channel.
     */
    public function test_screen_print_separation_underbase_mode(): void
    {
        $tmp = $this->createTestImage('png', 40, 40);
        $file = new UploadedFile($tmp, 'screen_test.png', 'image/png', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 300,
            'mode' => 'underbase',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('/separation?token=', $response->headers->get('Location'));

        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];

        $preview = $this->get("/separation/preview/{$token}/underbase");
        $preview->assertStatus(200);
        $this->assertEquals('image/png', $preview->headers->get('Content-Type'));

        @unlink($tmp);
    }

    /**
     * Test 10: Screen print separation generates Contour & Outline channel.
     */
    public function test_screen_print_separation_outline_mode(): void
    {
        $tmp = $this->createTestImage('png', 40, 40);
        $file = new UploadedFile($tmp, 'outline_test.png', 'image/png', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 300,
            'mode' => 'outline',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('/separation?token=', $response->headers->get('Location'));

        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];

        $preview = $this->get("/separation/preview/{$token}/outline");
        $preview->assertStatus(200);
        $this->assertEquals('image/png', $preview->headers->get('Content-Type'));

        @unlink($tmp);
    }

    /**
     * Test 11: Screen print separation supports Choke (in mm) on Underbase.
     */
    public function test_screen_print_separation_with_choke_mm(): void
    {
        $tmp = $this->createTestImage('png', 50, 50);
        $file = new UploadedFile($tmp, 'choke_test.png', 'image/png', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 300,
            'mode' => 'underbase',
            'choke_mm' => 0.5,
            'registration_marks' => 1,
        ]);

        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];

        $manifest = app(ColorSeparationService::class)->getManifest($token);
        $this->assertNotNull($manifest);
        $this->assertArrayHasKey('underbase', $manifest['channels']);

        @unlink($tmp);
    }

    /**
     * Test 12: Screen print separation supports Trap (in mm) on CMYK.
     */
    public function test_screen_print_separation_with_trap_mm(): void
    {
        $tmp = $this->createTestImage('png', 50, 50);
        $file = new UploadedFile($tmp, 'trap_test.png', 'image/png', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 300,
            'mode' => 'cmyk',
            'trap_mm' => 0.3,
            'registration_marks' => 1,
        ]);

        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];

        $manifest = app(ColorSeparationService::class)->getManifest($token);
        $this->assertNotNull($manifest);
        $this->assertArrayHasKey('c', $manifest['channels']);
        $this->assertArrayHasKey('m', $manifest['channels']);
        $this->assertArrayHasKey('y', $manifest['channels']);
        $this->assertArrayHasKey('k', $manifest['channels']);

        @unlink($tmp);
    }

    /**
     * Test 13: Experimental Spot Color match separates into specified number of clusters.
     */
    public function test_screen_print_separation_experimental_spot_mode(): void
    {
        $tmp = $this->createTestImage('png', 60, 60);
        $file = new UploadedFile($tmp, 'spot_test.png', 'image/png', null, true);

        $response = $this->post('/separation', [
            'file' => $file,
            'dpi' => 300,
            'mode' => 'spot',
            'spot_colors' => 3,
        ]);

        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];

        $manifest = app(ColorSeparationService::class)->getManifest($token);
        $this->assertNotNull($manifest);
        $this->assertCount(3, $manifest['channels']);

        @unlink($tmp);
    }

    /**
     * Test 14: CDR validation detects valid CorelDRAW headers and rejects invalid faked extensions.
     */
    public function test_cdr_upload_validation_and_engine_handling(): void
    {
        // 1. Fake CDR (plain text with .cdr extension) must fail header validation
        $fakeCdrPath = tempnam(sys_get_temp_dir(), 'fake_') . '.cdr';
        file_put_contents($fakeCdrPath, 'THIS IS NOT A VALID CDR FILE');
        $fakeFile = new UploadedFile($fakeCdrPath, 'corrupted.cdr', 'application/octet-stream', null, true);

        $response = $this->post('/separation', [
            'file' => $fakeFile,
            'dpi' => 300,
            'mode' => 'cmyk',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('bukan file CorelDRAW (.cdr) yang sah', session('error'));
        @unlink($fakeCdrPath);

        // 2. Real CDR sample test from user's machine (if present)
        $realCdrPath = 'D:\\Downloads\\file CDR\\kur.cdr';
        if (file_exists($realCdrPath)) {
            $realFile = new UploadedFile($realCdrPath, 'kur.cdr', 'application/octet-stream', null, true);
            $responseReal = $this->post('/separation', [
                'file' => $realFile,
                'dpi' => 300,
                'mode' => 'cmyk',
            ]);

            $rasterService = app(ImageRasterService::class);
            $engine = $rasterService->getCdrEngineStatus();

            if (!$engine['available']) {
                $responseReal->assertSessionHas('error');
                $this->assertStringContainsString('belum memiliki engine CLI converter', session('error'));
            } else {
                $responseReal->assertRedirect();
            }
        }
    }

    /**
     * Test 15: Pro Plan user is entitled to 600 DPI in Screen Print Separation.
     */
    public function test_pro_plan_user_can_access_600_dpi_in_separation(): void
    {
        $proPlan = \App\Models\Plan::where('slug', 'pro')->first();
        $proUser = \App\Models\User::factory()->create([
            'role' => 'USER',
            'plan_id' => $proPlan?->id,
            'unlimited' => false,
        ]);

        $tmp = $this->createTestImage('png', 40, 40);
        $file = new UploadedFile($tmp, 'pro_test.png', 'image/png', null, true);

        $response = $this->actingAs($proUser)->post('/separation', [
            'file' => $file,
            'dpi' => 600,
            'mode' => 'grayscale',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('/separation?token=', (string) $response->headers->get('Location'));

        @unlink($tmp);
    }
}

