<?php

namespace App\Services;

use App\Exceptions\QuotaExceededException;
use App\Models\ConversionUsage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QuotaService
{
    public const GUEST_DAILY_LIMIT = 1;
    /**
     * Authoritative single resolver for user entitlement.
     *
     * Precedence:
     * 1. user.unlimited == true => unlimited
     * 2. user.daily_limit IS NOT NULL => user.daily_limit
     * 3. user.plan => user.plan.unlimited ? unlimited : user.plan.daily_limit
     * 4. default free => 3 conversions/day
     *
     * @return array{
     *     unlimited: bool,
     *     daily_limit: ?int,
     *     source: string
     * }
     */
    public function resolveEntitlement(User $user): array
    {
        // 1. User unlimited flag overrides everything
        if ($user->unlimited === true) {
            return [
                'unlimited' => true,
                'daily_limit' => null,
                'source' => 'user_unlimited',
            ];
        }

        // 2. Custom daily_limit on user overrides plan
        if ($user->daily_limit !== null) {
            return [
                'unlimited' => false,
                'daily_limit' => (int) $user->daily_limit,
                'source' => 'user_custom',
            ];
        }

        // 3. User plan limit
        $plan = $user->plan;
        if ($plan) {
            if ($plan->unlimited) {
                return [
                    'unlimited' => true,
                    'daily_limit' => null,
                    'source' => 'plan_unlimited',
                ];
            }

            return [
                'unlimited' => false,
                'daily_limit' => (int) $plan->daily_limit,
                'source' => 'plan',
            ];
        }

        // 4. Default free fallback (3 conversions/day)
        return [
            'unlimited' => false,
            'daily_limit' => 3,
            'source' => 'default_free',
        ];
    }

    /**
     * Get usage count for the given user on a specific date.
     */
    public function getUsageCount(User $user, ?string $date = null): int
    {
        $date = $date ?: now()->toDateString();
        $usage = ConversionUsage::where('user_id', $user->id)
            ->where(function ($q) use ($date) {
                $q->where('usage_date', $date)->orWhere('usage_date', 'like', $date . '%');
            })
            ->first();

        return $usage ? (int) $usage->count : 0;
    }

    /**
     * Get comprehensive usage summary for views and API.
     *
     * @return array{
     *     unlimited: bool,
     *     daily_limit: ?int,
     *     used_today: int,
     *     remaining: ?int,
     *     percentage: int,
     *     plan_name: string,
     *     source: string
     * }
     */
    public function getUsageInfo(User $user, ?string $date = null): array
    {
        $date = $date ?: now()->toDateString();
        $entitlement = $this->resolveEntitlement($user);
        $used = $this->getUsageCount($user, $date);

        $planName = $user->plan?->name ?: 'Free';
        if ($user->isAdmin()) {
            $planName = 'Admin / Unlimited';
        }

        if ($entitlement['unlimited']) {
            return [
                'unlimited' => true,
                'daily_limit' => null,
                'used_today' => $used,
                'remaining' => null,
                'percentage' => 0,
                'plan_name' => $planName,
                'source' => $entitlement['source'],
            ];
        }

        $limit = $entitlement['daily_limit'] ?? 3;
        $remaining = max(0, $limit - $used);
        $percentage = $limit > 0 ? min(100, (int) round(($used / $limit) * 100)) : 100;

        return [
            'unlimited' => false,
            'daily_limit' => $limit,
            'used_today' => $used,
            'remaining' => $remaining,
            'percentage' => $percentage,
            'plan_name' => $planName,
            'source' => $entitlement['source'],
        ];
    }

    /**
     * Check if user is eligible to convert without reserving a slot.
     */
    public function canConvert(User $user, ?string $date = null): bool
    {
        $entitlement = $this->resolveEntitlement($user);
        if ($entitlement['unlimited']) {
            return true;
        }

        $used = $this->getUsageCount($user, $date);
        $limit = $entitlement['daily_limit'] ?? 3;

        return $used < $limit;
    }

    /**
     * Atomically reserve a conversion quota slot using database transactions and row locks.
     *
     * @throws QuotaExceededException
     */
    public function reserveQuotaSlot(User $user, ?string $date = null): QuotaReservation
    {
        $date = $date ?: now()->toDateString();
        $entitlement = $this->resolveEntitlement($user);

        // Unlimited users track usage count for metrics but are never blocked
        if ($entitlement['unlimited']) {
            return DB::transaction(function () use ($user, $date) {
                $usage = ConversionUsage::where('user_id', $user->id)
                    ->where(function ($q) use ($date) {
                        $q->where('usage_date', $date)->orWhere('usage_date', 'like', $date . '%');
                    })
                    ->lockForUpdate()
                    ->first();

                if (!$usage) {
                    ConversionUsage::create([
                        'user_id' => $user->id,
                        'usage_date' => $date,
                        'count' => 1,
                    ]);
                } else {
                    $usage->count += 1;
                    $usage->save();
                }

                return new QuotaReservation(
                    userId: $user->id,
                    usageDate: $date,
                    isUnlimited: true,
                    reservedSlot: 1,
                    dailyLimit: null
                );
            });
        }

        $dailyLimit = $entitlement['daily_limit'] ?? 3;

        return DB::transaction(function () use ($user, $date, $dailyLimit) {
            $usage = ConversionUsage::where('user_id', $user->id)
                ->where(function ($q) use ($date) {
                    $q->where('usage_date', $date)->orWhere('usage_date', 'like', $date . '%');
                })
                ->lockForUpdate()
                ->first();

            if (!$usage) {
                // First conversion of the day
                $usage = ConversionUsage::create([
                    'user_id' => $user->id,
                    'usage_date' => $date,
                    'count' => 1,
                ]);

                return new QuotaReservation(
                    userId: $user->id,
                    usageDate: $date,
                    isUnlimited: false,
                    reservedSlot: 1,
                    dailyLimit: $dailyLimit
                );
            }

            if ($usage->count >= $dailyLimit) {
                throw new QuotaExceededException(
                    message: "Batas harian konversi tercapai ({$usage->count}/{$dailyLimit}). Silakan upgrade paket Anda untuk konversi tambahan.",
                    usedToday: (int) $usage->count,
                    dailyLimit: (int) $dailyLimit
                );
            }

            $usage->count += 1;
            $usage->save();

            return new QuotaReservation(
                userId: $user->id,
                usageDate: $date,
                isUnlimited: false,
                reservedSlot: (int) $usage->count,
                dailyLimit: $dailyLimit
            );
        });
    }

    /**
     * Get the cache key for guest tracking.
     */
    protected function getGuestCacheKey(Request $request, string $date): string
    {
        $sessionId = $request->hasSession() ? $request->session()->getId() : 'nosess';
        $fingerprint = md5($request->ip() . '_' . $sessionId);

        return 'guest_quota_' . $fingerprint . '_' . $date;
    }

    /**
     * Get usage count for the given guest on a specific date.
     */
    public function getGuestUsage(Request $request, ?string $date = null): int
    {
        $date = $date ?: now()->toDateString();
        $sessionCount = $request->hasSession() ? (int) $request->session()->get('guest_usage_' . $date, 0) : 0;
        $cacheKey = $this->getGuestCacheKey($request, $date);
        $cacheCount = (int) Cache::get($cacheKey, 0);

        return max($sessionCount, $cacheCount);
    }

    /**
     * Check if guest is eligible to convert without reserving a slot.
     */
    public function canGuestConvert(Request $request, ?string $date = null): bool
    {
        return $this->getGuestUsage($request, $date) < self::GUEST_DAILY_LIMIT;
    }

    /**
     * Get usage summary for guest users.
     *
     * @return array{
     *     unlimited: bool,
     *     daily_limit: int,
     *     used_today: int,
     *     remaining: int,
     *     percentage: int,
     *     plan_name: string,
     *     source: string
     * }
     */
    public function getGuestUsageInfo(Request $request): array
    {
        $used = $this->getGuestUsage($request);
        $remaining = max(0, self::GUEST_DAILY_LIMIT - $used);
        $percentage = $used >= self::GUEST_DAILY_LIMIT ? 100 : 0;

        return [
            'unlimited' => false,
            'daily_limit' => self::GUEST_DAILY_LIMIT,
            'used_today' => $used,
            'remaining' => $remaining,
            'percentage' => $percentage,
            'plan_name' => 'Tamu (Guest)',
            'source' => 'guest_limit',
        ];
    }

    /**
     * Reserve a conversion slot for a guest user.
     *
     * @throws QuotaExceededException
     */
    public function reserveGuestSlot(Request $request, ?string $date = null): GuestQuotaReservation
    {
        $date = $date ?: now()->toDateString();
        $used = $this->getGuestUsage($request, $date);

        if ($used >= self::GUEST_DAILY_LIMIT) {
            throw new QuotaExceededException(
                message: "Batas kuota harian tamu tercapai (1/1). Silakan buat akun gratis untuk mendapatkan 3 konversi per hari.",
                usedToday: $used,
                dailyLimit: self::GUEST_DAILY_LIMIT
            );
        }

        $newCount = $used + 1;
        if ($request->hasSession()) {
            $request->session()->put('guest_usage_' . $date, $newCount);
        }

        $cacheKey = $this->getGuestCacheKey($request, $date);
        Cache::put($cacheKey, $newCount, now()->addDay());

        return new GuestQuotaReservation($request, $date, $cacheKey);
    }
}
