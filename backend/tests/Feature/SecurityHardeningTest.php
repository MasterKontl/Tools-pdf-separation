<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        // Malicious temp_file_id with directory traversal
        $response = $this->postJson('/convert', [
            'temp_file_id' => '../../../../etc/passwd',
            'format' => 'png',
            'dpi' => 150,
        ]);

        // Should be rejected with 422 (validation/processing error)
        $this->assertContains($response->status(), [422, 400]);
        $response->assertJson(['success' => false]);
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
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }
}
