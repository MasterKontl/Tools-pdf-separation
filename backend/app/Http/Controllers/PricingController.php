<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\PakasirService;
use App\Services\PaymentService;
use App\Services\PlanService;
use App\Services\QuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PricingController extends Controller
{
    /**
     * Show the pricing page.
     */
    public function index(Request $request, PlanService $planService, QuotaService $quotaService)
    {
        $plans = $planService->getActivePlans();
        $user = $request->user();
        $currentPlanId = $user?->plan_id;
        $usageInfo = $user ? $quotaService->getUsageInfo($user) : null;

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'plans' => $plans,
                'user' => $user,
                'currentPlanId' => $currentPlanId,
                'usageInfo' => $usageInfo,
            ]);
        }

        return view('pricing', [
            'plans' => $plans,
            'user' => $user,
            'currentPlanId' => $currentPlanId,
            'usageInfo' => $usageInfo,
        ]);
    }

    /**
     * Checkout a specific plan.
     */
    public function checkout(
        Request $request,
        Plan $plan,
        PaymentService $paymentService,
        PakasirService $pakasirService
    ) {
        $user = $request->user();

        if (!$plan->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'error' => 'Paket yang dipilih saat ini tidak aktif.'], 422);
            }
            return back()->with('error', 'Paket yang dipilih saat ini tidak aktif.');
        }

        // Free plan checkout: directly update user plan
        if ($plan->price <= 0 || $plan->slug === 'free') {
            $user->update([
                'plan_id' => $plan->id,
                'daily_limit' => null,
                'unlimited' => false,
            ]);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'is_free' => true,
                    'message' => 'Paket Free Anda telah diaktifkan.',
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Paket Free Anda telah diaktifkan.');
        }

        // Create a pending payment order
        $payment = $paymentService->createPaymentOrder($user, $plan);

        try {
            // Build official Pakasir checkout URL
            $returnUrl = route('payment.finish', ['orderId' => $payment->provider_reference]);
            $checkoutUrl = $pakasirService->buildCheckoutUrl(
                orderId: $payment->provider_reference,
                amount: (float) $payment->amount,
                redirectUrl: $returnUrl
            );

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'checkout_url' => $checkoutUrl,
                    'order_id' => $payment->provider_reference,
                    'amount' => (float) $payment->amount,
                    'payment' => $payment,
                ]);
            }

            Log::info('Pricing checkout: redirecting to Pakasir', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'order_id' => $payment->provider_reference,
                'checkout_url' => $checkoutUrl,
            ]);

            return redirect()->away($checkoutUrl);
        } catch (\Throwable $e) {
            Log::error('Pricing checkout: redirect failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'order_id' => $payment->provider_reference,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal membuat link pembayaran. Silakan coba lagi atau hubungi admin.');
        }
    }
}
