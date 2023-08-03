<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credit extends Model
{
    protected $fillable = [
        'transaction_id',
        'amount',
    ];

    protected $casts = [
        'amount' => MoneyIntegerCast::class,
        'amount_used' => MoneyIntegerCast::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function amountAvailable(): Money
    {
        return $this->amount->subtract($this->amount_used);
    }

    public function fullyUsed(): bool
    {
        return $this->amount_used->equals($this->amount);
    }
}
