<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Default subscription duration in days.
     */
    public const DEFAULT_DURATION_DAYS = 30;

    /**
     * Activate or extend a subscription for a user and plan.
     *
     * Rules:
     * - If an active subscription for the SAME plan exists:
     *     extend from existing expires_at (expires_at + durationDays)
     * - If no active subscription exists or previous expired:
     *     starts_at = now(), expires_at = now() + durationDays
     * - If upgrading/downgrading to a different plan:
     *     deactivate old subscription, starts_at = now(), expires_at = now() + durationDays
     *
     * @param  User  $user
     * @param  Plan  $plan
     * @param  string  $providerReference
     * @param  int  $durationDays
     * @return Subscription
     */
    public function activateOrExtend(
        User $user,
        Plan $plan,
        string $providerReference,
        int $durationDays = self::DEFAULT_DURATION_DAYS
    ): Subscription {
        return DB::transaction(function () use ($user, $plan, $providerReference, $durationDays) {
            $now = now();

            // Check if user has an active subscription for the exact same plan
            $existingSamePlanSub = Subscription::where('user_id', $user->id)
                ->where('plan_id', $plan->id)
                ->where('status', 'ACTIVE')
                ->where('expires_at', '>', $now)
                ->orderByDesc('expires_at')
                ->first();

            if ($existingSamePlanSub) {
                // Extend from existing expiration date so paid time is never lost
                $newExpiresAt = $existingSamePlanSub->expires_at->copy()->addDays($durationDays);
                $existingSamePlanSub->update([
                    'expires_at' => $newExpiresAt,
                    'provider_reference' => $providerReference,
                ]);

                // Ensure user's current plan is set
                $user->update(['plan_id' => $plan->id]);

                return $existingSamePlanSub;
            }

            // If user had an active subscription on a different plan, cancel/archive it
            Subscription::where('user_id', $user->id)
                ->where('status', 'ACTIVE')
                ->where('plan_id', '!=', $plan->id)
                ->update(['status' => 'CANCELLED']);

            // Create new active subscription
            $startsAt = $now;
            $expiresAt = $plan->unlimited ? null : $now->copy()->addDays($durationDays);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'ACTIVE',
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'provider' => 'pakasir',
                'provider_reference' => $providerReference,
            ]);

            // Update user plan entitlement
            $user->update([
                'plan_id' => $plan->id,
            ]);

            return $subscription;
        });
    }

    /**
     * Check and expire subscriptions that passed expires_at.
     */
    public function expireOutdatedSubscriptions(): int
    {
        $now = now();

        $expiredSubscriptions = Subscription::where('status', 'ACTIVE')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        $count = 0;
        foreach ($expiredSubscriptions as $sub) {
            DB::transaction(function () use ($sub) {
                $sub->update(['status' => 'EXPIRED']);

                // If user doesn't have other active subscriptions, rollback to free plan
                $hasOtherActive = Subscription::where('user_id', $sub->user_id)
                    ->where('status', 'ACTIVE')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->exists();

                if (!$hasOtherActive) {
                    $sub->user->update(['plan_id' => null]);
                }
            });

            $count++;
        }

        return $count;
    }

    /**
     * Determine if a user currently has an active, unexpired subscription.
     */
    public function hasActiveSubscription(User $user): bool
    {
        return Subscription::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}
