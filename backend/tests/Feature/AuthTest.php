<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        RateLimiter::clear('login:127.0.0.1');
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Daftar Akun');
    }

    public function test_new_users_can_register_and_receive_free_plan(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('USER', $user->role);
        $this->assertFalse($user->unlimited);
        $this->assertNotNull($user->plan_id);
        $this->assertEquals('free', $user->plan->slug);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_fails_if_password_confirmation_mismatches(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi',
            'email' => 'budi2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'login_test@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'login_test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'login_fail@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'login_fail@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        $ip = '127.0.0.1';
        $throttleKey = 'login:'.$ip;

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($throttleKey, 60);
        }

        $response = $this->post('/login', [
            'email' => 'throttled@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_form_action_uses_https_when_behind_proxy(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Port' => '443',
            'X-Forwarded-Host' => 'kurniawansatya.xyz',
        ])->get('/login');

        $response->assertStatus(200);
        $response->assertSee('action="https://', false);
    }

    public function test_security_headers_are_attached_to_responses(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    public function test_forgot_password_does_not_reveal_reset_url_in_session_or_view(): void
    {
        $user = User::factory()->create(['email' => 'victim@example.com']);

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'victim@example.com',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('status', 'Jika alamat email terdaftar, tautan pengaturan ulang kata sandi akan dikirimkan ke email Anda.');
        $response->assertSessionMissing('reset_url');

        $followUp = $this->get('/forgot-password');
        $followUp->assertStatus(200);
        $followUp->assertDontSee('reset-password');
        $followUp->assertDontSee('Klik di sini untuk langsung mengatur ulang kata sandi');
    }

    public function test_forgot_password_returns_identical_generic_response_for_non_existing_email(): void
    {
        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('status', 'Jika alamat email terdaftar, tautan pengaturan ulang kata sandi akan dikirimkan ke email Anda.');
        $response->assertSessionMissing('reset_url');
    }

    public function test_forgot_password_requests_are_rate_limited(): void
    {
        RateLimiter::clear('forgot-pwd-ip:127.0.0.1');
        RateLimiter::clear('forgot-pwd-email:spam@example.com');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/forgot-password', ['email' => 'spam@example.com']);
        }

        $throttledResponse = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'spam@example.com',
        ]);

        $throttledResponse->assertRedirect('/forgot-password');
        $throttledResponse->assertSessionHas('status', 'Jika alamat email terdaftar, tautan pengaturan ulang kata sandi akan dikirimkan ke email Anda.');
    }

    public function test_forgot_password_sends_notification_to_existing_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'registered@example.com',
        ]);

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'registered@example.com',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('status', 'Jika alamat email terdaftar, tautan pengaturan ulang kata sandi akan dikirimkan ke email Anda.');
        $response->assertSessionMissing('reset_url');
        $response->assertSessionMissing('token');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $mail = $notification->toMail($user);
            return !empty($notification->token)
                && str_contains($mail->subject, 'Tools DKV')
                && str_contains($mail->actionUrl, $notification->token)
                && str_contains($mail->actionUrl, urlencode($user->email));
        });
    }

    public function test_forgot_password_does_not_send_notification_to_non_existing_user(): void
    {
        Notification::fake();

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'ghost@example.com',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('status', 'Jika alamat email terdaftar, tautan pengaturan ulang kata sandi akan dikirimkan ke email Anda.');
        $response->assertSessionMissing('reset_url');
        $response->assertSessionMissing('token');

        Notification::assertNothingSent();
    }

    public function test_reset_password_notification_mail_content_meets_requirements(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $notification = new ResetPasswordNotification('sample-reset-token-12345');
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Atur Ulang Kata Sandi', $mail->subject);
        $this->assertStringContainsString('Tools DKV', $mail->subject);
        $this->assertEquals('Atur Ulang Kata Sandi', $mail->actionText);
        $this->assertStringContainsString('sample-reset-token-12345', $mail->actionUrl);
        $this->assertStringContainsString(urlencode('john@example.com'), $mail->actionUrl);

        $rendered = (string) $mail->render();
        $this->assertStringContainsString('Tools DKV', $rendered);
        $this->assertStringContainsString('Atur Ulang Kata Sandi', $rendered);
        $this->assertStringContainsString('menit', $rendered);
        $this->assertStringContainsString('sample-reset-token-12345', $rendered);
    }

    public function test_password_reset_url_uses_https_in_production(): void
    {
        config(['app.url' => 'https://kurniawansatya.xyz']);
        \Illuminate\Support\Facades\URL::forceRootUrl('https://kurniawansatya.xyz');
        \Illuminate\Support\Facades\URL::forceScheme('https');

        $user = User::factory()->create([
            'email' => 'https_test@example.com',
        ]);

        $notification = new ResetPasswordNotification('secure-token-abc');
        $mail = $notification->toMail($user);

        $this->assertStringStartsWith('https://kurniawansatya.xyz/reset-password/secure-token-abc', $mail->actionUrl);
    }
}

