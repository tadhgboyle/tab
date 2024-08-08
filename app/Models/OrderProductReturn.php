<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProductReturn extends Model
{
    protected $casts = [
        'total_return_amount' => MoneyIntegerCast::class,
        'purchaser_amount' => MoneyIntegerCast::class,
        'gift_card_amount' => MoneyIntegerCast::class,
    ];

    protected $fillable = [
        'order_id',
        'order_product_id',
        'total_return_amount',
        'purchaser_amount',
        'gift_card_amount',
        'returner_id',
    ];

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class);
    }

    public function returner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
