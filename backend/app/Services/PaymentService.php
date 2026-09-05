<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected PakasirService $pakasirService
    ) {}

    /**
     * Create a new pending payment order for a user and plan.
     */
    public function createPaymentOrder(User $user, Plan $plan): Payment
    {
        $orderId = 'TDKV-' . strtoupper(Str::random(6)) . '-' . time();

        return Payment::create([
            'user_id' => $user->id,
            'subscription_id' => null,
            'provider' => 'pakasir',
            'provider_reference' => $orderId,
            'amount' => $plan->price,
            'currency' => $plan->currency ?: 'IDR',
            'status' => 'PENDING',
            'paid_at' => null,
            'metadata' => [
                'plan_id' => $plan->id,
                'plan_slug' => $plan->slug,
                'plan_name' => $plan->name,
            ],
        ]);
    }

    /**
     * Process and finalize a successful payment idempotently.
     *
     * @throws Exception
     */
    public function processSuccessfulPayment(string $providerReference, array $webhookPayload = []): Payment
    {
        return DB::transaction(function () use ($providerReference, $webhookPayload) {
            $payment = Payment::where('provider_reference', $providerReference)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                throw new Exception("Pembayaran dengan referensi {$providerReference} tidak ditemukan.");
            }

            // IDEMPOTENCY CHECK: If already paid, return immediately without duplicate subscription activation!
            if ($payment->status === 'PAID') {
                return $payment;
            }

            $planId = $payment->metadata['plan_id'] ?? null;
            $plan = $planId ? Plan::find($planId) : null;

            if (!$plan) {
                throw new Exception("Paket langganan terkait tidak ditemukan.");
            }

            // 1. Activate or extend the subscription deterministically
            $subscription = $this->subscriptionService->activateOrExtend(
                user: $payment->user,
                plan: $plan,
                providerReference: $payment->provider_reference
            );

            // 2. Mark payment as PAID
            $payment->update([
                'status' => 'PAID',
                'subscription_id' => $subscription->id,
                'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'webhook' => $webhookPayload,
                    'processed_at' => now()->toIso8601String(),
                ]),
            ]);

            return $payment;
        });
    }

    /**
     * Mark payment as failed or cancelled.
     */
    public function markAsFailed(string $providerReference, string $reason = ''): ?Payment
    {
        return DB::transaction(function () use ($providerReference, $reason) {
            $payment = Payment::where('provider_reference', $providerReference)
                ->lockForUpdate()
                ->first();

            if (!$payment || $payment->status === 'PAID') {
                return $payment;
            }

            $payment->update([
                'status' => 'FAILED',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'failure_reason' => $reason,
                ]),
            ]);

            return $payment;
        });
    }
}
