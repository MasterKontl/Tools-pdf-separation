<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversionUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'usage_date',
        'count',
    ];

    protected function casts(): array
    {
        return [
            'usage_date' => 'string',
            'count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
