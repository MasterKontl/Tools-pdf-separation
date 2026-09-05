<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use App\Services\AuthTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'daily_limit' => 3,
            'unlimited' => false,
            'price' => 0,
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'daily_limit' => 50,
            'unlimited' => false,
            'price' => 49000,
            'currency' => 'IDR',
            'is_active' => true,
        ]);
    }

    public function test_api_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'service' => 'Tools DKV API',
            ]);
    }

    public function test_api_plans_endpoint_returns_active_plans(): void
    {
        $response = $this->getJson('/api/plans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'plans' => [
                    '*' => ['id', 'name', 'slug', 'price'],
                ],
            ]);
    }

    public function test_api_auth_login_and_me_with_bearer_token(): void
    {
        $user = User::factory()->create([
            'email' => 'designer@toolsdkv.com',
            'password' => bcrypt('password123'),
            'role' => 'USER',
            'is_active' => true,
        ]);

        // Login via API
        $loginRes = $this->postJson('/api/auth/login', [
            'email' => 'designer@toolsdkv.com',
            'password' => 'password123',
        ]);

        $loginRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'user' => ['id', 'email', 'role'],
            ]);

        $token = $loginRes->json('token');
        $this->assertNotEmpty($token);

        // Access /api/auth/me with Bearer token
        $meRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        $meRes->assertStatus(200)
            ->assertJsonPath('user.email', 'designer@toolsdkv.com');
    }

    public function test_api_quota_endpoint_for_guest_and_user(): void
    {
        // 1. Guest quota
        $guestRes = $this->getJson('/api/quota');
        $guestRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('usageInfo.daily_limit', 1);

        // 2. Authenticated user quota
        $user = User::factory()->create([
            'role' => 'USER',
            'daily_limit' => 3,
            'is_active' => true,
        ]);

        $token = AuthTokenService::generateToken($user);

        $userRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/quota');

        $userRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('usageInfo.daily_limit', 3);
    }

    public function test_api_separation_config_endpoint(): void
    {
        $response = $this->getJson('/api/separation/config');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'allowedModes',
                'modesConfig',
                'cdrEngine',
            ]);
    }

    public function test_api_admin_dashboard_protection(): void
    {
        // 1. Unauthenticated request
        $unauthRes = $this->getJson('/api/admin/dashboard');
        $unauthRes->assertStatus(401);

        // 2. Regular user request
        $regularUser = User::factory()->create(['role' => 'USER', 'is_active' => true]);
        $regularToken = AuthTokenService::generateToken($regularUser);

        $forbiddenRes = $this->withHeader('Authorization', 'Bearer ' . $regularToken)
            ->getJson('/api/admin/dashboard');
        $forbiddenRes->assertStatus(403);

        // 3. Admin request
        $adminUser = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $adminToken = AuthTokenService::generateToken($adminUser);

        $adminRes = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/admin/dashboard');
        $adminRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['totalUsers', 'totalRevenue', 'conversionsToday']);
    }

    public function test_api_ssrf_protection_on_fetch_url(): void
    {
        $response = $this->postJson('/api/convert/fetch-url', [
            'url' => 'http://127.0.0.1:8000/secret.pdf',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_api_separation_process_returns_manifest(): void
    {
        // Create sample RGB image in memory
        $im = imagecreatetruecolor(60, 60);
        $red = imagecolorallocate($im, 255, 0, 0);
        imagefilledrectangle($im, 0, 0, 59, 59, $red);
        ob_start();
        imagepng($im);
        $pngData = ob_get_clean();
        imagedestroy($im);

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('artwork.png', $pngData);

        $response = $this->postJson('/api/separation/process', [
            'file' => $file,
            'mode' => 'grayscale',
            'dpi' => 150,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'token',
                'manifest' => ['channels', 'safeName', 'mode'],
            ]);

        $token = $response->json('token');
        $this->assertNotEmpty($token);

        // Verify manifest endpoint
        $manifestRes = $this->getJson("/api/separation/manifest/{$token}");
        $manifestRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('token', $token);
    }
}
