<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'plan_id',
        'daily_limit',
        'unlimited',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'daily_limit' => 'integer',
            'unlimited' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function conversionUsages(): HasMany
    {
        return $this->hasMany(ConversionUsage::class);
    }

    public function isAdmin(): bool
    {
        return strtoupper((string) $this->role) === 'ADMIN';
    }

    public function isUnlimited(): bool
    {
        return (bool) $this->unlimited;
    }

    /**
     * Check if user is entitled to high DPI (600 DPI) output.
     * Accessible for Admin, Unlimited, or users with Pro/Unlimited plans (slug: 'pro', 'unlimited') or daily_limit >= 100.
     */
    public function canAccessHighDpi(): bool
    {
        if ($this->isAdmin() || $this->unlimited) {
            return true;
        }

        if ($this->daily_limit !== null && $this->daily_limit >= 100) {
            return true;
        }

        $planSlug = strtolower((string) ($this->plan?->slug ?? ''));
        return in_array($planSlug, ['pro', 'unlimited'], true);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}

