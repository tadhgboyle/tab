<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;

class GiftCardAdjustment extends Model
{
    public const TYPE_CHARGE = 'charge';
    public const TYPE_REFUND = 'refund';

    protected $casts = [
        'amount' => MoneyIntegerCast::class,
    ];

    protected $fillable = [
        'gift_card_id',
        'order_id',
        'amount',
        'type',
    ];

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isCharge(): bool
    {
        return $this->type === self::TYPE_CHARGE;
    }

    public function isRefund(): bool
    {
        return $this->type === self::TYPE_REFUND;
    }
}
