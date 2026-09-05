<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminPlanController extends Controller
{
    /**
     * Display a listing of plans.
     */
    public function index(Request $request)
    {
        $plans = Plan::withCount('users')->orderBy('price')->get();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'plans' => $plans,
            ]);
        }

        return view('admin.plans.index', ['plans' => $plans]);
    }

    /**
     * Show form for creating a new plan.
     */
    public function create(): View
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:plans,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'daily_limit' => ['nullable', 'integer', 'min:1'],
            'unlimited' => ['nullable', 'boolean'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $validated['slug'] ?: Str::slug($validated['name']);

        $plan = Plan::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'daily_limit' => $request->boolean('unlimited') ? null : ($validated['daily_limit'] ?? 3),
            'unlimited' => $request->boolean('unlimited'),
            'price' => $validated['price'],
            'currency' => strtoupper($validated['currency']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil ditambahkan.',
                'plan' => $plan,
            ], 201);
        }

        return redirect()->route('admin.plans.index')->with('success', 'Paket berhasil ditambahkan.');
    }

    /**
     * Show form for editing the specified plan.
     */
    public function edit(Plan $plan)
    {
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'plan' => $plan,
            ]);
        }

        return view('admin.plans.edit', ['plan' => $plan]);
    }

    /**
     * Update the specified plan in database.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'daily_limit' => ['nullable', 'integer', 'min:1'],
            'unlimited' => ['nullable', 'boolean'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'daily_limit' => $request->boolean('unlimited') ? null : ($validated['daily_limit'] ?? 3),
            'unlimited' => $request->boolean('unlimited'),
            'price' => $validated['price'],
            'currency' => strtoupper($validated['currency']),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Paket {$plan->name} berhasil diperbarui.",
                'plan' => $plan,
            ]);
        }

        return redirect()->route('admin.plans.index')->with('success', "Paket {$plan->name} berhasil diperbarui.");
    }

    /**
     * Toggle plan active state.
     */
    public function toggleActive(Request $request, Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $status = $plan->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'is_active' => $plan->is_active,
                'message' => "Status paket {$plan->name} telah {$status}.",
            ]);
        }

        return back()->with('success', "Status paket {$plan->name} telah {$status}.");
    }
}
