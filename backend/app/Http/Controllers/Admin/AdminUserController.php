<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\QuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(
        protected QuotaService $quotaService
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with(['plan']);

        if ($search = $request->input('search')) {
            $escaped = $this->escapeLikeWildcard($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                  ->orWhere('email', 'like', "%{$escaped}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $plans = Plan::all();

        // Attach usage info to each user
        $today = now()->toDateString();
        foreach ($users as $user) {
            $user->today_usage = $this->quotaService->getUsageCount($user, $today);
            $user->entitlement = $this->quotaService->resolveEntitlement($user);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'users' => $users,
                'plans' => $plans,
            ]);
        }

        return view('admin.users.index', [
            'users' => $users,
            'plans' => $plans,
        ]);
    }

    /**
     * Show the edit form for a user.
     */
    public function edit(User $user)
    {
        $plans = Plan::all();
        $today = now()->toDateString();
        $user->today_usage = $this->quotaService->getUsageCount($user, $today);
        $user->entitlement = $this->quotaService->resolveEntitlement($user);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'plans' => $plans,
            ]);
        }

        return view('admin.users.edit', [
            'user' => $user,
            'plans' => $plans,
        ]);
    }

    /**
     * Update user details, role, plan, and custom limits.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:USER,ADMIN'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'daily_limit' => ['nullable', 'integer', 'min:0'],
            'unlimited' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'plan_id' => $validated['plan_id'] ?: null,
            'daily_limit' => $request->filled('daily_limit') ? (int) $request->input('daily_limit') : null,
            'unlimited' => $request->boolean('unlimited'),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Pengguna {$user->name} berhasil diperbarui.",
                'user' => $user,
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', "Pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Quick toggle for unlimited entitlement.
     */
    public function toggleUnlimited(Request $request, User $user)
    {
        $user->update([
            'unlimited' => !$user->unlimited,
        ]);

        $status = $user->unlimited ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'unlimited' => $user->unlimited,
                'message' => "Akses Unlimited untuk {$user->name} telah {$status}.",
            ]);
        }

        return back()->with('success', "Akses Unlimited untuk {$user->name} telah {$status}.");
    }

    /**
     * Quick toggle for user active status.
     */
    public function toggleActive(Request $request, User $user)
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'disuspend';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'is_active' => $user->is_active,
                'message' => "Status akun {$user->name} telah {$status}.",
            ]);
        }

        return back()->with('success', "Status akun {$user->name} telah {$status}.");
    }

    /**
     * Escape LIKE wildcard characters in a search string.
     */
    private function escapeLikeWildcard(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
