<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GuestQuotaReservation
{
    public function __construct(
        public readonly Request $request,
        public readonly string $date,
        public readonly string $cacheKey,
        public readonly int $reservedSlot = 1
    ) {}

    /**
     * Release the reserved quota slot if conversion failed.
     */
    public function release(): void
    {
        // Decrement session count if session is available
        if ($this->request->hasSession()) {
            $sessionCount = (int) $this->request->session()->get('guest_usage_' . $this->date, 0);
            if ($sessionCount > 0) {
                $this->request->session()->put('guest_usage_' . $this->date, $sessionCount - 1);
            }
        }

        // Decrement cache count
        $cacheCount = (int) Cache::get($this->cacheKey, 0);
        if ($cacheCount > 0) {
            Cache::put($this->cacheKey, $cacheCount - 1, now()->addDay());
        }
    }

    /**
     * Confirms the reservation.
     */
    public function confirm(): void
    {
        // Already committed during reservation
    }

    /**
     * Alias for confirm().
     */
    public function finalize(): void
    {
        $this->confirm();
    }
}
