<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\QuotaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the user dashboard.
     */
    public function index(Request $request, QuotaService $quotaService)
    {
        $user = $request->user();
        $usageInfo = $quotaService->getUsageInfo($user);

        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->with('plan')
            ->latest('starts_at')
            ->first();

        $recentPayments = Payment::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'usageInfo' => $usageInfo,
                'activeSubscription' => $activeSubscription,
                'recentPayments' => $recentPayments,
            ]);
        }

        return view('dashboard', [
            'user' => $user,
            'usageInfo' => $usageInfo,
            'activeSubscription' => $activeSubscription,
            'recentPayments' => $recentPayments,
        ]);
    }
}
