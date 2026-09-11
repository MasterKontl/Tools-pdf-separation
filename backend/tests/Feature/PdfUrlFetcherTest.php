<?php

namespace Tests\Feature;

use App\Services\PdfConverterService;
use App\Services\PdfUrlFetcherService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PdfUrlFetcherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cache()->flush();
    }
    /**
     * Helper to generate minimal valid 1-page PDF binary string.
     */
    protected function createValidPdfContent(): string
    {
        $body = "%PDF-1.4\n";
        $body .= "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
        $body .= "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
        $body .= "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Contents 4 0 R >> endobj\n";
        $stream = "BT /F1 12 Tf 20 180 Td (Testing PDF Fetch) Tj ET";
        $streamLen = strlen($stream);
        $body .= "4 0 obj << /Length {$streamLen} >> stream\n{$stream}\nendstream endobj\n";
        $body .= "xref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000212 00000 n \n";
        $body .= "trailer << /Size 5 /Root 1 0 R >>\nstartxref\n320\n%%EOF";
        return $body;
    }

    /**
     * 1. Test public PDF URL successfully fetched.
     */
    public function test_public_pdf_url_successfully_fetched(): void
    {
        $validPdf = $this->createValidPdfContent();

        $service = new PdfUrlFetcherService();
        $service->setDnsResolver(fn ($host) => ['93.184.216.34']); // public IP (example.com)
        $service->setHttpTransport(function ($url, $dest) use ($validPdf) {
            file_put_contents($dest, $validPdf);
            return [
                'statusCode' => 200,
                'headers' => [
                    'content-type' => 'application/pdf',
                    'content-disposition' => 'attachment; filename="test_doc.pdf"',
                ],
            ];
        });

        $this->app->instance(PdfUrlFetcherService::class, $service);

        $response = $this->postJson('/convert/fetch-url', [
            'url' => 'https://example.com/test_doc.pdf',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'fileName' => 'test-doc.pdf',
        ]);

        $tempId = $response->json('tempId');
        $this->assertNotEmpty($tempId);
        $savedFile = storage_path('app/temp/url_import_' . $tempId . '.pdf');
        $this->assertFileExists($savedFile);

        // Cleanup
        @unlink($savedFile);
    }

    /**
     * 2. Test invalid scheme is rejected.
     */
    public function test_invalid_scheme_is_rejected(): void
    {
        $invalidUrls = [
            'ftp://example.com/file.pdf',
            'file:///etc/passwd',
            'gopher://example.com/',
            'javascript:alert(1)',
            'data:application/pdf;base64,JVBERi0xLjQK',
        ];

        foreach ($invalidUrls as $url) {
            cache()->flush();
            $response = $this->postJson('/convert/fetch-url', ['url' => $url]);
            $response->assertStatus(422);
            $response->assertJson(['success' => false]);
            $this->assertStringContainsString('Skema URL tidak diizinkan', $response->json('message'));
        }
    }

    /**
     * 3. Test localhost hostnames are rejected.
     */
    public function test_localhost_is_rejected(): void
    {
        $localhostUrls = [
            'http://localhost/test.pdf',
            'http://localhost:8000/secret.pdf',
            'http://sub.localhost/file.pdf',
            'http://127.0.0.1/test.pdf',
            'http://[::1]/test.pdf',
        ];

        foreach ($localhostUrls as $url) {
            cache()->flush();
            $response = $this->postJson('/convert/fetch-url', ['url' => $url]);
            $response->assertStatus(422);
            $response->assertJson(['success' => false]);
        }
    }

    /**
     * 4. Test private and reserved IPs are rejected.
     */
    public function test_private_and_reserved_ips_are_rejected(): void
    {
        $privateIps = [
            'http://10.0.0.1/test.pdf',
            'http://172.16.0.1/test.pdf',
            'http://192.168.1.1/test.pdf',
            'http://169.254.169.254/latest/meta-data', // AWS/GCP/Azure metadata
            'http://0.0.0.0/test.pdf',
            'http://100.64.0.1/test.pdf', // CGNAT
            'http://224.0.0.1/multicast.pdf',
        ];

        foreach ($privateIps as $url) {
            cache()->flush();
            $response = $this->postJson('/convert/fetch-url', ['url' => $url]);
            $response->assertStatus(422);
            $response->assertJson(['success' => false]);
            $this->assertStringContainsString('tidak diizinkan', $response->json('message'));
        }
    }

    /**
     * 5. Test redirect to private IP is rejected.
     */
    public function test_redirect_to_private_ip_is_rejected(): void
    {
        $service = new PdfUrlFetcherService();
        $service->setDnsResolver(function ($host) {
            if ($host === 'safe-domain.com') {
                return ['93.184.216.34'];
            }
            if ($host === 'internal-evil.com') {
                return ['192.168.1.50'];
            }
            return ['127.0.0.1'];
        });

        $service->setHttpTransport(function ($url, $dest) {
            // First hop redirects to internal-evil.com
            return [
                'statusCode' => 302,
                'headers' => [
                    'Location' => 'http://internal-evil.com/secret.pdf',
                ],
            ];
        });

        $this->app->instance(PdfUrlFetcherService::class, $service);

        $response = $this->postJson('/convert/fetch-url', [
            'url' => 'https://safe-domain.com/start.pdf',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('alamat IP privat', $response->json('message'));
    }

    /**
     * 6. Test file >250 MB is rejected.
     */
    public function test_file_exceeding_250mb_is_rejected(): void
    {
        $service = new PdfUrlFetcherService();
        $service->setDnsResolver(fn ($host) => ['93.184.216.34']);
        $service->setHttpTransport(function ($url, $dest) {
            // Simulate Content-Length > 250MB
            return [
                'statusCode' => 200,
                'headers' => [
                    'content-length' => (string) (260 * 1024 * 1024),
                    'content-type' => 'application/pdf',
                ],
                'error' => 'Ukuran file melebihi batas maksimum 250 MB.',
            ];
        });

        $this->app->instance(PdfUrlFetcherService::class, $service);

        $response = $this->postJson('/convert/fetch-url', [
            'url' => 'https://example.com/huge.pdf',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('250 MB', $response->json('message'));
    }

    /**
     * 7. Test response that is not PDF (e.g. HTML/error page) is rejected.
     */
    public function test_response_that_is_html_or_not_pdf_is_rejected(): void
    {
        $htmlContent = "<!DOCTYPE html><html><body>404 File Not Found</body></html>";

        $service = new PdfUrlFetcherService();
        $service->setDnsResolver(fn ($host) => ['93.184.216.34']);
        $service->setHttpTransport(function ($url, $dest) use ($htmlContent) {
            file_put_contents($dest, $htmlContent);
            return [
                'statusCode' => 200,
                'headers' => [
                    'content-type' => 'text/html',
                ],
            ];
        });

        $this->app->instance(PdfUrlFetcherService::class, $service);

        $response = $this->postJson('/convert/fetch-url', [
            'url' => 'https://example.com/error-page.pdf',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('halaman web/HTML', $response->json('message'));
    }

    /**
     * 8. Test invalid PDF magic bytes is rejected.
     */
    public function test_invalid_pdf_magic_bytes_is_rejected(): void
    {
        $corruptBinary = "BINARY_DATA_WITHOUT_PDF_MAGIC_BYTES_123456789";

        $service = new PdfUrlFetcherService();
        $service->setDnsResolver(fn ($host) => ['93.184.216.34']);
        $service->setHttpTransport(function ($url, $dest) use ($corruptBinary) {
            file_put_contents($dest, $corruptBinary);
            return [
                'statusCode' => 200,
                'headers' => [
                    'content-type' => 'application/pdf',
                ],
            ];
        });

        $this->app->instance(PdfUrlFetcherService::class, $service);

        $response = $this->postJson('/convert/fetch-url', [
            'url' => 'https://example.com/fake.pdf',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('magic bytes tidak sesuai', $response->json('message'));
    }

    /**
     * 9. Test valid fetched PDF enters converter and successfully produces download.
     */
    public function test_valid_fetched_pdf_enters_converter(): void
    {
        $validPdf = $this->createValidPdfContent();

        $service = new PdfUrlFetcherService();
        $service->setDnsResolver(fn ($host) => ['93.184.216.34']);
        $service->setHttpTransport(function ($url, $dest) use ($validPdf) {
            file_put_contents($dest, $validPdf);
            return [
                'statusCode' => 200,
                'headers' => [
                    'content-type' => 'application/pdf',
                    'content-disposition' => 'filename="online_source.pdf"',
                ],
            ];
        });

        $this->app->instance(PdfUrlFetcherService::class, $service);

        // Step A: Fetch URL
        $fetchResponse = $this->postJson('/convert/fetch-url', [
            'url' => 'https://example.com/online_source.pdf',
        ]);

        $fetchResponse->assertStatus(200);
        $tempId = $fetchResponse->json('tempId');

        // Step B: Submit to convert with temp_file_id
        $convertResponse = $this->post('/convert', [
            'temp_file_id' => $tempId,
            'format' => 'png',
            'dpi' => 150,
        ]);

        $convertResponse->assertStatus(200);
        $this->assertEquals('image/png', $convertResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('.png', (string) $convertResponse->headers->get('Content-Disposition'));
    }

    /**
     * 10. Test temporary file is cleaned up after conversion and on failed fetch.
     */
    public function test_temporary_file_is_cleaned_up(): void
    {
        $validPdf = $this->createValidPdfContent();

        $service = new PdfUrlFetcherService();
        $service->setDnsResolver(fn ($host) => ['93.184.216.34']);
        $service->setHttpTransport(function ($url, $dest) use ($validPdf) {
            file_put_contents($dest, $validPdf);
            return [
                'statusCode' => 200,
                'headers' => ['content-type' => 'application/pdf'],
            ];
        });

        $this->app->instance(PdfUrlFetcherService::class, $service);

        $fetchResponse = $this->postJson('/convert/fetch-url', [
            'url' => 'https://example.com/cleanup_test.pdf',
        ]);

        $tempId = $fetchResponse->json('tempId');
        $tempFilePath = storage_path('app/temp/url_import_' . $tempId . '.pdf');
        $this->assertFileExists($tempFilePath);

        // Run conversion
        $this->post('/convert', [
            'temp_file_id' => $tempId,
            'format' => 'png',
            'dpi' => 150,
        ]);

        // After conversion, the fetched temporary file must be unlinked/deleted
        $this->assertFileDoesNotExist($tempFilePath);
    }
}
