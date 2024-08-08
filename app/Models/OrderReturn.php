<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturn extends Model
{
    protected $casts = [
        'total_return_amount' => MoneyIntegerCast::class,
        'purchaser_amount' => MoneyIntegerCast::class,
        'gift_card_amount' => MoneyIntegerCast::class,
        'caused_by_product_return' => 'boolean',
    ];

    protected $fillable = [
        'order_id',
        'total_return_amount',
        'purchaser_amount',
        'gift_card_amount',
        'returner_id',
        'caused_by_product_return',
    ];

    public function returner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
