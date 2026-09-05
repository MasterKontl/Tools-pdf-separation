<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PakasirService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Handle incoming Pakasir webhook.
     * POST /payment/webhook/pakasir
     */
    public function webhook(
        Request $request,
        PakasirService $pakasir,
        PaymentService $paymentService
    ): JsonResponse {
        $orderId = (string) $request->input('order_id');
        $amount = (float) $request->input('amount');
        $status = strtolower((string) $request->input('status', ''));

        if (empty($orderId)) {
            return response()->json(['success' => false, 'message' => 'Missing order_id'], 400);
        }

        try {
            // Find existing payment
            $payment = Payment::where('provider_reference', $orderId)->first();
            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            // IDEMPOTENCY: If already paid, respond immediately
            if ($payment->status === 'PAID') {
                return response()->json(['success' => true, 'message' => 'Payment already processed']);
            }

            // Verify with Pakasir server directly
            $verification = $pakasir->verifyTransaction($orderId, $payment->amount);

            if ($verification['verified']) {
                $paymentService->processSuccessfulPayment($orderId, $request->all());
                return response()->json(['success' => true, 'message' => 'Payment confirmed and subscription activated']);
            }

            if (in_array($status, ['failed', 'expired', 'cancelled'], true)) {
                $paymentService->markAsFailed($orderId, $status);
                return response()->json(['success' => true, 'message' => "Payment marked as {$status}"]);
            }

            return response()->json(['success' => false, 'message' => 'Payment not completed yet'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error during verification'], 500);
        }
    }

    /**
     * Handle return from Pakasir payment checkout.
     */
    public function finish(
        Request $request,
        string $orderId,
        PakasirService $pakasir,
        PaymentService $paymentService
    ): RedirectResponse {
        $payment = Payment::where('provider_reference', $orderId)->first();

        if (!$payment) {
            return redirect()->route('dashboard')->with('error', 'Transaksi pembayaran tidak ditemukan.');
        }

        // If still pending, attempt quick active verification check
        if ($payment->status === 'PENDING') {
            try {
                $verification = $pakasir->verifyTransaction($orderId, (float) $payment->amount);
                if ($verification['verified']) {
                    $paymentService->processSuccessfulPayment($orderId, ['source' => 'finish_redirect']);
                }
            } catch (Exception $e) {
                // Ignore failure on redirect check, webhook will process it
            }
        }

        $payment->refresh();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'status' => $payment->status,
                'is_paid' => $payment->status === 'PAID',
                'payment' => $payment,
            ]);
        }

        if ($payment->status === 'PAID') {
            return redirect()->route('dashboard')->with('success', 'Pembayaran berhasil! Paket Anda telah aktif.');
        }

        return redirect()->route('dashboard')->with('status', 'Pembayaran sedang diproses. Status langganan akan otomatis diperbarui saat konfirmasi diterima.');
    }

    /**
     * Check payment status by orderId (API).
     */
    public function status(string $orderId): JsonResponse
    {
        $payment = Payment::with('plan')->where('provider_reference', $orderId)->first();
        if (!$payment) {
            return response()->json(['success' => false, 'error' => 'Transaksi tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'is_paid' => $payment->status === 'PAID',
            'payment' => $payment,
        ]);
    }
}
