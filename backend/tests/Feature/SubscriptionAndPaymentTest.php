<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_pricing_page_is_accessible(): void
    {
        $response = $this->get('/pricing');
        $response->assertStatus(200);
        $response->assertSee('Tingkatkan Kuota Konversi Anda');
        $response->assertSee('Student');
        $response->assertSee('Pro');
        $response->assertSee('Unlimited');
    }

    public function test_user_can_initiate_checkout_and_receive_pakasir_redirect_url(): void
    {
        $proPlan = Plan::where('slug', 'pro')->first();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post("/pricing/checkout/{$proPlan->id}");

        $response->assertRedirect();
        $targetUrl = $response->headers->get('Location');

        $this->assertStringContainsString('https://app.pakasir.com/pay/', $targetUrl);
        $this->assertStringContainsString((string) (int) $proPlan->price, $targetUrl);

        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('PENDING', $payment->status);
        $this->assertEquals($proPlan->price, $payment->amount);
        $this->assertEquals($proPlan->id, $payment->metadata['plan_id']);
        $this->assertStringStartsWith('TDKV-', $payment->provider_reference);
    }

    public function test_webhook_completes_payment_and_activates_subscription(): void
    {
        $proPlan = Plan::where('slug', 'pro')->first();
        $user = User::factory()->create([
            'plan_id' => Plan::where('slug', 'free')->value('id'),
        ]);

        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);
        $payment = $paymentService->createPaymentOrder($user, $proPlan);

        // Mock Pakasir verification API endpoint
        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response([
                'project' => config('services.pakasir.slug', 'tools-dkv'),
                'order_id' => $payment->provider_reference,
                'amount' => $payment->amount,
                'status' => 'completed',
                'payment_method' => 'qris',
                'completed_at' => now()->toIso8601String(),
            ], 200),
        ]);

        $response = $this->postJson('/payment/webhook/pakasir', [
            'project' => config('services.pakasir.slug', 'tools-dkv'),
            'order_id' => $payment->provider_reference,
            'amount' => $payment->amount,
            'status' => 'completed',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $payment->refresh();
        $this->assertEquals('PAID', $payment->status);
        $this->assertNotNull($payment->paid_at);

        $subscription = Subscription::where('user_id', $user->id)
            ->where('plan_id', $proPlan->id)
            ->first();

        $this->assertNotNull($subscription);
        $this->assertEquals('ACTIVE', $subscription->status);
        $this->assertTrue($subscription->isActive());

        $user->refresh();
        $this->assertEquals($proPlan->id, $user->plan_id);
    }

    public function test_webhook_is_idempotent_when_called_multiple_times(): void
    {
        $proPlan = Plan::where('slug', 'pro')->first();
        $user = User::factory()->create();

        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);
        $payment = $paymentService->createPaymentOrder($user, $proPlan);

        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response([
                'status' => 'completed',
                'order_id' => $payment->provider_reference,
                'amount' => $payment->amount,
            ], 200),
        ]);

        $payload = [
            'project' => config('services.pakasir.slug', 'tools-dkv'),
            'order_id' => $payment->provider_reference,
            'amount' => $payment->amount,
            'status' => 'completed',
        ];

        // First webhook call
        $r1 = $this->postJson('/payment/webhook/pakasir', $payload);
        $r1->assertStatus(200);

        $sub = Subscription::where('user_id', $user->id)->first();
        $initialExpiresAt = $sub->expires_at->toDateTimeString();

        // Second webhook call (replay / duplicate webhook)
        $r2 = $this->postJson('/payment/webhook/pakasir', $payload);
        $r2->assertStatus(200);

        // Verify no duplicate subscriptions created and expires_at was not double extended
        $this->assertEquals(1, Subscription::where('user_id', $user->id)->count());
        $sub->refresh();
        $this->assertEquals($initialExpiresAt, $sub->expires_at->toDateTimeString());
    }

    public function test_cumulative_subscription_extension_preserves_remaining_days(): void
    {
        $proPlan = Plan::where('slug', 'pro')->first();
        $user = User::factory()->create(['plan_id' => $proPlan->id]);

        // Existing subscription valid for another 20 days
        $existingExpiry = now()->addDays(20);
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'status' => 'ACTIVE',
            'starts_at' => now()->subDays(10),
            'expires_at' => $existingExpiry,
        ]);

        /** @var SubscriptionService $subService */
        $subService = app(SubscriptionService::class);
        $newSub = $subService->activateOrExtend($user, $proPlan, 'TDKV-RENEW-123', 30);

        // Cumulative extension: existing expiry + 30 days = approximately 50 days from now
        $this->assertEquals($existingExpiry->copy()->addDays(30)->toDateString(), $newSub->expires_at->toDateString());
    }

    public function test_expired_subscription_is_recognized_as_inactive(): void
    {
        $proPlan = Plan::where('slug', 'pro')->first();
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'status' => 'ACTIVE',
            'starts_at' => now()->subDays(60),
            'expires_at' => now()->subDays(1), // Expired yesterday
        ]);

        /** @var SubscriptionService $subService */
        $subService = app(SubscriptionService::class);
        $this->assertFalse($subService->hasActiveSubscription($user));
    }
}
