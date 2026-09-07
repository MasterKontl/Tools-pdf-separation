<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    /**
     * Display payments table.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'subscription']);

        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        if ($search = $request->input('search')) {
            $escaped = $this->escapeLikeWildcard($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('provider_reference', 'like', "%{$escaped}%")
                  ->orWhereHas('user', function ($uq) use ($escaped) {
                      $uq->where('name', 'like', "%{$escaped}%")
                         ->orWhere('email', 'like', "%{$escaped}%");
                  });
            });
        }

        $payments = $query->latest()->paginate(20)->withQueryString();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'payments' => $payments,
            ]);
        }

        return view('admin.payments.index', ['payments' => $payments]);
    }

    /**
     * Escape LIKE wildcard characters in a search string.
     */
    private function escapeLikeWildcard(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
