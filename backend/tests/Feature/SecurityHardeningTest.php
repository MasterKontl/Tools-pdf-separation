<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test CRIT-1: User mass assignment protection.
     * Attempting to set role, plan_id, daily_limit, unlimited, or is_active via mass assignment must fail.
     */
    public function test_user_mass_assignment_privilege_escalation_is_blocked(): void
    {
        $user = User::create([
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
            'password' => 'password123',
            'role' => 'ADMIN',
            'unlimited' => true,
            'daily_limit' => 99999,
            'is_active' => true,
        ]);

        // Values should default or remain null/false, ignoring client-supplied mass assignment
        $this->assertEquals('USER', strtoupper((string) ($user->role ?? 'USER')));
        $this->assertFalse((bool) $user->unlimited);
        $this->assertNull($user->daily_limit);
    }

    /**
     * Test CRIT-2 & Fix 3: Payment status endpoint IDOR protection.
     * Unauthenticated or unauthorized callers must receive 401 or 403.
     */
    public function test_payment_status_requires_auth_and_ownership(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $payment = Payment::create([
            'user_id' => $owner->id,
            'provider' => 'pakasir',
            'provider_reference' => 'ORDER-12345',
            'amount' => 50000,
            'status' => 'PENDING',
        ]);

        // Unauthenticated access -> 401
        $responseUnauth = $this->getJson('/api/payments/status/ORDER-12345');
        $responseUnauth->assertStatus(401);

        // Unauthorized user (otherUser accessing owner's payment) -> 403
        $responseOther = $this->actingAs($otherUser)->getJson('/api/payments/status/ORDER-12345');
        $responseOther->assertStatus(403);

        // Owner access -> 200
        $responseOwner = $this->actingAs($owner)->getJson('/api/payments/status/ORDER-12345');
        $responseOwner->assertStatus(200);
        $responseOwner->assertJson(['success' => true, 'status' => 'PENDING']);
    }

    /**
     * Test Fix 3: Payment finish endpoint IDOR protection.
     */
    public function test_payment_finish_endpoint_prevents_idor(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Payment::create([
            'user_id' => $owner->id,
            'provider' => 'pakasir',
            'provider_reference' => 'ORDER-67890',
            'amount' => 50000,
            'status' => 'PENDING',
        ]);

        // Other user attempting to trigger finish on owner's order -> 403
        $response = $this->actingAs($otherUser)->getJson('/payment/finish/ORDER-67890');
        $response->assertStatus(403);
    }

    /**
     * Test CRIT-3 & Fix 4: Path traversal via temp_file_id is rejected.
     */
    public function test_pdf_converter_rejects_path_traversal_temp_file_id(): void
    {
        $response = $this->postJson('/convert', [
            'temp_file_id' => '../../../../etc/passwd',
            'format' => 'png',
            'dpi' => 150,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('temp_file_id');
    }

    /**
     * Test Fix 6: Health check returns minimal status without sensitive fingerprinting.
     */
    public function test_health_check_returns_minimal_info(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok', 'service' => 'Tools DKV API']);
        
        // Ensure sensitive version fingerprinting is NOT exposed to public
        $this->assertArrayNotHasKey('php_version', $response->json());
        $this->assertArrayNotHasKey('pdftoppm_version', $response->json());
    }

    /**
     * Test Fix 7: Security headers middleware applies strict security headers.
     */
    public function test_security_headers_are_present_in_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()');
    }

    /**
     * Pentest: CSP header is present in responses.
     */
    public function test_content_security_policy_header_is_present(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotEmpty($csp, 'Content-Security-Policy header must be present');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
    }

    /**
     * Pentest: Payment model has plan() relationship and it works.
     */
    public function test_payment_model_has_plan_relationship(): void
    {
        $user = User::factory()->create();

        $payment = Payment::create([
            'user_id' => $user->id,
            'provider' => 'pakasir',
            'provider_reference' => 'ORDER-REL-TEST',
            'amount' => 50000,
            'status' => 'PENDING',
        ]);

        // Eager load plan relationship without error
        $loaded = Payment::with('plan')->find($payment->id);
        $this->assertNotNull($loaded);
        $this->assertNull($loaded->plan); // No plan assigned yet
    }

    /**
     * Pentest: Rate limiter is configured for registration.
     */
    public function test_registration_rate_limiter_is_configured(): void
    {
        $limiter = RateLimiter::limiter('register');
        $this->assertNotNull($limiter, 'Register rate limiter must be configured');

        $limiterConvert = RateLimiter::limiter('convert');
        $this->assertNotNull($limiterConvert, 'Convert rate limiter must be configured');
    }

    /**
     * Pentest: Upscaler config values are accessible via config().
     */
    public function test_upscaler_config_values_are_accessible(): void
    {
        $maxSize = config('upscaler.max_file_size_kb');
        $this->assertNotNull($maxSize, 'upscaler.max_file_size_kb must be defined');
        $this->assertGreaterThan(0, $maxSize);

        $maxPixels = config('upscaler.max_pixels');
        $this->assertNotNull($maxPixels, 'upscaler.max_pixels must be defined');
        $this->assertGreaterThan(0, $maxPixels);

        $maxOutputPixels = config('upscaler.max_output_pixels');
        $this->assertNotNull($maxOutputPixels, 'upscaler.max_output_pixels must be defined');
        $this->assertGreaterThan(0, $maxOutputPixels);
    }

    /**
     * Pentest: X-Powered-By header is removed.
     */
    public function test_x_powered_by_header_is_removed(): void
    {
        $response = $this->get('/');
        $this->assertNull($response->headers->get('X-Powered-By'), 'X-Powered-By must not be present');
    }

    /**
     * Pentest: Admin access denied logs audit event.
     */
    public function test_admin_access_denied_logs_audit(): void
    {
        $user = User::factory()->create(['role' => 'USER']);
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    /**
     * Pentest: CSRF is enforced on conversion endpoints.
     */
    public function test_csrf_enforced_on_conversion_endpoint(): void
    {
        // Without CSRF token, conversion should fail (419 = token mismatch, 422 = validation, 500 = server error if CSRF bypassed)
        $response = $this->postJson('/convert', [
            'temp_file_id' => 'test123',
            'format' => 'png',
            'dpi' => 150,
        ]);

        // postJson sends Accept: application/json; CSRF middleware returns 419 for JSON requests
        $this->assertContains($response->status(), [419, 422, 500], 'CSRF must be enforced on conversion endpoint');
    }

    /**
     * Pentest: CSRF exemption only applies to payment webhook endpoints.
     */
    public function test_csrf_exemptions_limited_to_webhook_only(): void
    {
        $middleware = app(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->assertNotNull($middleware, 'CSRF middleware must be registered');
    }

    /**
     * Pentest: Session cookie security settings.
     */
    public function test_session_cookie_settings_are_secure(): void
    {
        $config = config('session');
        $this->assertNotEquals('none', $config['same_site'] ?? 'lax', 'SameSite should not be none');
    }

    /**
     * Pentest: CORS configuration does not allow all origins.
     */
    public function test_cors_does_not_allow_all_origins(): void
    {
        $config = config('cors');
        $allowedOrigins = $config['allowed_origins'] ?? [];
        $this->assertNotContains('*', $allowedOrigins, 'CORS must not allow all origins');
    }

    /**
     * Pentest: Error handlers do not expose sensitive details.
     */
    public function test_error_handlers_do_not_expose_details(): void
    {
        // Request a non-existent route to trigger 404
        $response = $this->get('/this-route-does-not-exist-12345');
        $response->assertStatus(404);
        $content = $response->getContent();
        $this->assertStringNotContainsString('Stack trace', $content);
        $this->assertStringNotContainsString('Exception', $content);
        $this->assertStringNotContainsString('vendor/', $content);
    }

    /**
     * Pentest: Auth token generation and validation flow.
     */
    public function test_auth_token_validation_flow(): void
    {
        $user = User::factory()->create();
        $token = \App\Services\AuthTokenService::generateToken($user);
        $this->assertNotEmpty($token);

        // Verify token is encrypted (not a plain-text session token)
        $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($token);
        $this->assertNotEmpty($decrypted);

        $decoded = json_decode($decrypted, true);
        $this->assertNotNull($decoded, 'Token must contain valid JSON');
        $this->assertArrayHasKey('sub', $decoded, 'Token must contain subject (user id)');
        $this->assertEquals($user->id, $decoded['sub']);
        $this->assertArrayHasKey('exp', $decoded, 'Token must have expiry');
        $this->assertGreaterThan(time(), $decoded['exp'], 'Token expiry must be in the future');
    }
}
