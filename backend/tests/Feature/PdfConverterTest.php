<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ZipArchive;

class PdfConverterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Helper to generate a minimal valid 2-page PDF binary content.
     */
    protected function createSamplePdfContent(int $pages = 2): string
    {
        // Simple valid PDF structure
        $pageObjects = '';
        $kids = [];
        $streamObjects = '';
        $objIndex = 3;

        for ($i = 1; $i <= $pages; $i++) {
            $kids[] = "{$objIndex} 0 R";
            $contentObj = $objIndex + 1;
            $pageObjects .= "{$objIndex} 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Contents {$contentObj} 0 R >> endobj\n";
            $text = "Page {$i} Content";
            $stream = "BT /F1 12 Tf 20 180 Td ({$text}) Tj ET";
            $streamLen = strlen($stream);
            $streamObjects .= "{$contentObj} 0 obj << /Length {$streamLen} >> stream\n{$stream}\nendstream endobj\n";
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
            $text = "Page {$i} Content";
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
     * Test the index page loads successfully with UI elements.
     */
    public function test_converter_index_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('PDF Converter V1');
        $response->assertSee('pdftoppm');
        $response->assertSee('150');
        $response->assertSee('300');
        $response->assertSee('600');
    }

    /**
     * Test validation requires a valid PDF, valid DPI, and format.
     */
    public function test_convert_validates_required_and_invalid_inputs(): void
    {
        // 1. Missing all fields
        $response = $this->post('/convert', []);
        $response->assertSessionHasErrors(['pdf', 'format', 'dpi']);

        // 2. Invalid DPI and format
        $file = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');
        $response = $this->post('/convert', [
            'pdf' => $file,
            'format' => 'bmp',
            'dpi' => 1200,
        ]);
        $response->assertSessionHasErrors(['format', 'dpi']);
    }

    /**
     * Test single page PDF conversion returns image file.
     */
    public function test_convert_single_page_pdf_to_png_150_dpi(): void
    {
        $pdfContent = $this->createSamplePdfContent(1);
        $tempPdf = tempnam(sys_get_temp_dir(), 'pdf_test_') . '.pdf';
        file_put_contents($tempPdf, $pdfContent);

        $file = new UploadedFile($tempPdf, 'single_page.pdf', 'application/pdf', null, true);

        $response = $this->post('/convert', [
            'pdf' => $file,
            'format' => 'png',
            'dpi' => 150,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('single-page_150dpi.png', (string) $response->headers->get('Content-Disposition'));

        @unlink($tempPdf);
    }

    /**
     * Test multi-page PDF conversion returns ZIP file containing pages.
     */
    public function test_convert_multipage_pdf_to_jpg_300_dpi_returns_zip(): void
    {
        $pdfContent = $this->createSamplePdfContent(2);
        $tempPdf = tempnam(sys_get_temp_dir(), 'pdf_test_') . '.pdf';
        file_put_contents($tempPdf, $pdfContent);

        $file = new UploadedFile($tempPdf, 'multipage_doc.pdf', 'application/pdf', null, true);

        $response = $this->post('/convert', [
            'pdf' => $file,
            'format' => 'jpg',
            'dpi' => 300,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('application/zip', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('multipage-doc_300dpi.zip', (string) $response->headers->get('Content-Disposition'));

        // Verify zip contents directly from response binary file
        $zipPath = $response->getFile()->getPathname();
        $this->assertFileExists($zipPath);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);
        $this->assertEquals(2, $zip->numFiles);
        $this->assertStringContainsString('page_01.jpg', $zip->getNameIndex(0));
        $this->assertStringContainsString('page_02.jpg', $zip->getNameIndex(1));
        $zip->close();

        @unlink($tempPdf);
    }

    /**
     * Test conversion with 600 DPI (Ultra High Res).
     */
    public function test_convert_with_600_dpi_png(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'unlimited' => true,
        ]);

        $pdfContent = $this->createSamplePdfContent(1);
        $tempPdf = tempnam(sys_get_temp_dir(), 'pdf_test_') . '.pdf';
        file_put_contents($tempPdf, $pdfContent);

        $file = new UploadedFile($tempPdf, 'ultra_res.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($admin)->post('/convert', [
            'pdf' => $file,
            'format' => 'png',
            'dpi' => 600,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('ultra-res_600dpi.png', (string) $response->headers->get('Content-Disposition'));

        @unlink($tempPdf);
    }

    /**
     * Test validation failure when a non-PDF file is uploaded.
     */
    public function test_convert_rejects_non_pdf_file(): void
    {
        $tempTxt = tempnam(sys_get_temp_dir(), 'fake_pdf_') . '.pdf';
        file_put_contents($tempTxt, 'This is plain text and not a PDF');

        $file = new UploadedFile($tempTxt, 'plain_text.pdf', 'application/pdf', null, true);

        $response = $this->from('/')->post('/convert', [
            'pdf' => $file,
            'format' => 'png',
            'dpi' => 300,
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['pdf']);

        @unlink($tempTxt);
    }

    /**
     * Test error handling when a corrupted/broken PDF structure is processed.
     */
    public function test_convert_handles_corrupted_pdf_gracefully(): void
    {
        $tempCorrupt = tempnam(sys_get_temp_dir(), 'pdf_corrupt_') . '.pdf';
        // Has PDF magic header so mimes:pdf passes, but internal structure is completely broken
        file_put_contents($tempCorrupt, "%PDF-1.4\n%%BROKEN_STREAM_CORRUPTED_TRAILER_ETC%%");

        $file = new UploadedFile($tempCorrupt, 'corrupted.pdf', 'application/pdf', null, true);

        $response = $this->from('/')->post('/convert', [
            'pdf' => $file,
            'format' => 'png',
            'dpi' => 300,
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('error');

        @unlink($tempCorrupt);
    }
}
