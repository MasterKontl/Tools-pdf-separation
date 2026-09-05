<?php

namespace App\Services;

use App\Models\ConversionUsage;
use Illuminate\Support\Facades\DB;

class QuotaReservation
{
    public function __construct(
        public readonly int $userId,
        public readonly string $usageDate,
        public readonly bool $isUnlimited,
        public readonly int $reservedSlot = 1,
        public readonly ?int $dailyLimit = null
    ) {}

    /**
     * Release the reserved quota slot if conversion failed.
     */
    public function release(): void
    {
        if ($this->isUnlimited) {
            return;
        }

        DB::transaction(function () {
            ConversionUsage::where('user_id', $this->userId)
                ->where(function ($q) {
                    $q->where('usage_date', $this->usageDate)
                      ->orWhere('usage_date', 'like', $this->usageDate . '%');
                })
                ->lockForUpdate()
                ->first();

            DB::table('conversion_usages')
                ->where('user_id', $this->userId)
                ->where(function ($q) {
                    $q->where('usage_date', $this->usageDate)
                      ->orWhere('usage_date', 'like', $this->usageDate . '%');
                })
                ->update([
                    'count' => DB::raw('CASE WHEN count > 0 THEN count - 1 ELSE 0 END'),
                    'updated_at' => now(),
                ]);
        });
    }

    /**
     * Confirms the reservation (already committed atomically).
     */
    public function confirm(): void
    {
        // No-op: atomic slot was already reserved during reserveQuotaSlot.
    }

    /**
     * Alias for confirm().
     */
    public function finalize(): void
    {
        $this->confirm();
    }
}
