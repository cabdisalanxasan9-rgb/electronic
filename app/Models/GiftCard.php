<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class GiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'initial_balance',
        'balance',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function isValidFor(float $amount, ?Carbon $now = null): bool
    {
        $now = $now ?? now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        return (float) $this->balance >= $amount;
    }
}
