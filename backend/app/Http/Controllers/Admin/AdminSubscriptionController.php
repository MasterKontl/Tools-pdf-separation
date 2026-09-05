<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubscriptionController extends Controller
{
    /**
     * Display subscriptions list.
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'plan']);

        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->latest('starts_at')->paginate(20)->withQueryString();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'subscriptions' => $subscriptions,
            ]);
        }

        return view('admin.subscriptions.index', ['subscriptions' => $subscriptions]);
    }
}
