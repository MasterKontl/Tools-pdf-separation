<?php

namespace App\Services;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

class PlanService
{
    /**
     * Get all active plans sorted by price.
     */
    public function getActivePlans(): Collection
    {
        return Plan::where('is_active', true)
            ->orderBy('price')
            ->get();
    }

    /**
     * Find plan by slug.
     */
    public function findBySlug(string $slug): ?Plan
    {
        return Plan::where('slug', $slug)->first();
    }

    /**
     * Create a new plan (Admin).
     */
    public function createPlan(array $data): Plan
    {
        return Plan::create($data);
    }

    /**
     * Update an existing plan (Admin).
     */
    public function updatePlan(Plan $plan, array $data): Plan
    {
        $plan->update($data);
        return $plan;
    }
}
