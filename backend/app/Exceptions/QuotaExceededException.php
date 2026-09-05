<?php

namespace App\Exceptions;

use Exception;

class QuotaExceededException extends Exception
{
    protected int $usedToday;
    protected ?int $dailyLimit;

    public function __construct(string $message = 'Batas harian konversi tercapai. Silakan tingkatkan paket Anda.', int $usedToday = 3, ?int $dailyLimit = 3, int $code = 429)
    {
        parent::__construct($message, $code);
        $this->usedToday = $usedToday;
        $this->dailyLimit = $dailyLimit;
    }

    public function getUsedToday(): int
    {
        return $this->usedToday;
    }

    public function getDailyLimit(): ?int
    {
        return $this->dailyLimit;
    }
}
