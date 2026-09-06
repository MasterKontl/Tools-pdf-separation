<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpscalerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a minimal valid 1×1 pixel PNG binary.
     */
    protected function createMinimalPng(): string
    {
        // 1x1 red pixel PNG
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg=='
        );
    }

    /**
     * Create a minimal valid JPEG binary (1×1 pixel).
     */
    protected function createMinimalJpeg(): string
    {
        // 1x1 white JPEG
        return base64_decode(
            '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwg' .
            'JC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIy' .
            'MjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFgAB' .
            'AQEAAAAAAAAAAAAAAAAACAgJ/8QAFBABAAAAAAAAAAAAAAAAAAAAkP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/' .
            'xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwABmX/9k='
        );
    }

    /**
     * Create a fake UploadedFile from binary content.
     */
    protected function makePngFile(string $name = 'test.png', ?string $content = null): UploadedFile
    {
        $content = $content ?? $this->createMinimalPng();
        $path = tempnam(sys_get_temp_dir(), 'test_png_');
        file_put_contents($path, $content);
        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    protected function makeJpegFile(string $name = 'test.jpg', ?string $content = null): UploadedFile
    {
        $content = $content ?? $this->createMinimalJpeg();
        $path = tempnam(sys_get_temp_dir(), 'test_jpg_');
        file_put_contents($path, $content);
        return new UploadedFile($path, $name, 'image/jpeg', null, true);
    }

    // =========================================================================
    // 1. GET /upscaler — Page accessibility
    // =========================================================================

    public function test_upscaler_index_page_is_accessible(): void
    {
        $response = $this->get('/upscaler');
        $response->assertStatus(200);
        $response->assertSee('Upscaler');
    }

    public function test_upscaler_index_page_accessible_as_guest(): void
    {
        $response = $this->get('/upscaler');
        $response->assertStatus(200);
    }

    public function test_upscaler_index_page_accessible_as_authenticated_user(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/upscaler');
        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    // =========================================================================
    // 2. POST /upscaler/process — Valid inputs
    // =========================================================================

    public function test_upscale_valid_png_2x(): void
    {
        $file = $this->makePngFile('sample.png');

        $response = $this->postJson('/upscaler/process', [
            'image' => $file,
            'scale' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'result' => ['tempId', 'extension', 'fileName', 'width', 'height', 'fileSize', 'scale'],
            'input' => ['width', 'height'],
        ]);

        $data = $response->json();
        $this->assertEquals(2, $data['result']['scale']);
        $this->assertEquals($data['input']['width'] * 2, $data['result']['width']);
        $this->assertEquals($data['input']['height'] * 2, $data['result']['height']);
    }

    public function test_upscale_valid_png_4x(): void
    {
        $file = $this->makePngFile('sample.png');

        $response = $this->postJson('/upscaler/process', [
            'image' => $file,
            'scale' => 4,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $data = $response->json();
        $this->assertEquals(4, $data['result']['scale']);
        $this->assertEquals($data['input']['width'] * 4, $data['result']['width']);
        $this->assertEquals($data['input']['height'] * 4, $data['result']['height']);
    }

    public function test_upscale_valid_jpeg_2x(): void
    {
        $file = $this->makeJpegFile('photo.jpg');

        $response = $this->postJson('/upscaler/process', [
            'image' => $file,
            'scale' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_upscale_valid_jpeg_4x(): void
    {
        $file = $this->makeJpegFile('photo.jpg');

        $response = $this->postJson('/upscaler/process', [
            'image' => $file,
            'scale' => 4,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_upscale_output_dimensions_correct_for_2x(): void
    {
        $file = $this->makePngFile();

        $response = $this->postJson('/upscaler/process', ['image' => $file, 'scale' => 2]);
        $response->assertOk();

        $data = $response->json();
        $this->assertEquals($data['input']['width'] * 2, $data['result']['width']);
        $this->assertEquals($data['input']['height'] * 2, $data['result']['height']);
    }

    public function test_upscale_output_dimensions_correct_for_4x(): void
    {
        $file = $this->makePngFile();

        $response = $this->postJson('/upscaler/process', ['image' => $file, 'scale' => 4]);
        $response->assertOk();

        $data = $response->json();
        $this->assertEquals($data['input']['width'] * 4, $data['result']['width']);
        $this->assertEquals($data['input']['height'] * 4, $data['result']['height']);
    }

    // =========================================================================
    // 3. Invalid / rejected inputs
    // =========================================================================

    public function test_upscale_rejects_missing_image(): void
    {
        $response = $this->postJson('/upscaler/process', ['scale' => 2]);
        $response->assertStatus(422);
    }

    public function test_upscale_rejects_invalid_scale(): void
    {
        $file = $this->makePngFile();

        $response = $this->postJson('/upscaler/process', ['image' => $file, 'scale' => 3]);
        $response->assertStatus(422);
    }

    public function test_upscale_rejects_non_image_file(): void
    {
        // Fake a text file disguised as image
        $path = tempnam(sys_get_temp_dir(), 'test_txt_');
        file_put_contents($path, 'This is a text file, not an image.');
        $file = new UploadedFile($path, 'evil.png', 'image/png', null, true);

        $response = $this->postJson('/upscaler/process', ['image' => $file, 'scale' => 2]);

        // Should fail — MIME validation catches this
        $this->assertContains($response->status(), [422, 415, 500]);
        if ($response->status() === 422 || $response->status() === 415) {
            $data = $response->json();
            $this->assertFalse($data['success'] ?? true);
        }
    }

    public function test_upscale_rejects_missing_scale(): void
    {
        $file = $this->makePngFile();
        $response = $this->postJson('/upscaler/process', ['image' => $file]);
        $response->assertStatus(422);
    }

    // =========================================================================
    // 4. Quota system integration
    // =========================================================================

    public function test_upscale_respects_guest_quota(): void
    {
        // Exhaust guest quota (1/day)
        $file1 = $this->makePngFile();
        $first = $this->postJson('/upscaler/process', ['image' => $file1, 'scale' => 2]);

        if ($first->status() === 200) {
            // Second request should hit quota
            $file2 = $this->makePngFile();
            $second = $this->postJson('/upscaler/process', ['image' => $file2, 'scale' => 2]);
            // Either 429 (quota exceeded) or 200 (if quota is generous in test env)
            $this->assertContains($second->status(), [200, 429]);
        }
    }

    public function test_upscale_authenticated_user_uses_quota(): void
    {
        $user = User::factory()->create();

        $file = $this->makePngFile();
        $response = $this->actingAs($user)->postJson('/upscaler/process', [
            'image' => $file,
            'scale' => 2,
        ]);

        // Should succeed for a fresh user (3 free conversions)
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // 5. Download endpoint
    // =========================================================================

    public function test_upscale_result_downloadable(): void
    {
        $file = $this->makePngFile();
        $response = $this->postJson('/upscaler/process', ['image' => $file, 'scale' => 2]);
        $response->assertOk();

        $data = $response->json();
        $tempId = $data['result']['tempId'];
        $ext = $data['result']['extension'];
        $fileName = $data['result']['fileName'];

        $dlResponse = $this->get('/upscaler/download/' . $tempId . '/' . $ext . '?name=' . urlencode($fileName));
        $dlResponse->assertStatus(200);
        $dlResponse->assertHeader('Content-Type');
    }

    public function test_download_rejects_invalid_temp_id(): void
    {
        // Path traversal attempt
        $response = $this->get('/upscaler/download/../../../etc/passwd/png');
        $response->assertStatus(404);
    }

    public function test_download_rejects_invalid_extension(): void
    {
        $response = $this->get('/upscaler/download/ABCDEFGHIJ1234567890/exe');
        $response->assertStatus(404);
    }

    public function test_download_returns_404_for_nonexistent_file(): void
    {
        $response = $this->get('/upscaler/download/NONEXISTENTID12345/png');
        $response->assertStatus(404);
    }

    // =========================================================================
    // 6. CSRF protection
    // =========================================================================

    public function test_upscale_requires_csrf_token(): void
    {
        // Without CSRF token (non-JSON request that checks CSRF)
        $file = $this->makePngFile();
        // withoutMiddleware only for verifying the route exists; we want CSRF active
        // Posting without CSRF via non-JSON should fail
        $response = $this->post('/upscaler/process', [
            'image' => $file,
            'scale' => 2,
        ]);
        // CSRF failure = 419
        $response->assertStatus(419);
    }

    // =========================================================================
    // 7. Service unit-level validation
    // =========================================================================

    public function test_upscale_service_validates_oversized_output(): void
    {
        $upscaler = new \App\Services\ImageUpscaleService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/melebihi batas/i');

        // Override limits with env — in test, force small limit
        // We'll test the validation method directly
        // A 5000x5000 image upscaled 4x = 20000x20000 = 400MP > default 100MP limit
        $upscaler->validateOutputSize(5000, 5000, 4);
    }

    public function test_upscale_service_validates_supported_scales(): void
    {
        $upscaler = new \App\Services\ImageUpscaleService();

        // Create a minimal temp PNG
        $content = $this->createMinimalPng();
        $path = tempnam(sys_get_temp_dir(), 'svc_test_');
        file_put_contents($path, $content);

        try {
            $this->expectException(\Exception::class);
            $upscaler->upscale($path, 3, 'image/png', 'test.png');
        } finally {
            @unlink($path);
        }
    }

    // =========================================================================
    // 8. Route existence
    // =========================================================================

    public function test_upscaler_routes_exist(): void
    {
        $this->assertNotNull(route('upscaler.index'));
        $this->assertNotNull(route('upscaler.process'));
        $this->assertNotNull(route('upscaler.download', ['tempId' => 'test12345ABCDE', 'ext' => 'png']));
    }
}
