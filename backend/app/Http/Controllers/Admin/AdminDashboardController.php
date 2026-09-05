<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConversionUsage;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Show admin dashboard overview metrics.
     */
    public function index(Request $request)
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        $freePlan = Plan::where('slug', 'free')->first();
        $freeUsers = User::where('plan_id', $freePlan?->id)
            ->orWhereNull('plan_id')
            ->count();

        $premiumUsers = User::whereNotNull('plan_id')
            ->where('plan_id', '!=', $freePlan?->id)
            ->count();

        $unlimitedUsers = User::where('unlimited', true)->count();

        $today = now()->toDateString();
        $conversionsToday = (int) ConversionUsage::where('usage_date', $today)->sum('count');

        $totalRevenue = (float) Payment::where('status', 'PAID')->sum('amount');

        $recentUsers = User::with('plan')->latest()->take(5)->get();
        $recentPayments = Payment::with('user')->latest()->take(5)->get();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers,
                'freeUsers' => $freeUsers,
                'premiumUsers' => $premiumUsers,
                'unlimitedUsers' => $unlimitedUsers,
                'conversionsToday' => $conversionsToday,
                'totalRevenue' => $totalRevenue,
                'recentUsers' => $recentUsers,
                'recentPayments' => $recentPayments,
            ]);
        }

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'freeUsers' => $freeUsers,
            'premiumUsers' => $premiumUsers,
            'unlimitedUsers' => $unlimitedUsers,
            'conversionsToday' => $conversionsToday,
            'totalRevenue' => $totalRevenue,
            'recentUsers' => $recentUsers,
            'recentPayments' => $recentPayments,
        ]);
    }
}
