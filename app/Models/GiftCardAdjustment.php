<?php

namespace App\Models;

use App\Enums\GiftCardAdjustmentType;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;

class GiftCardAdjustment extends Model
{
    protected $casts = [
        'type' => GiftCardAdjustmentType::class,
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
        return $this->type === GiftCardAdjustmentType::Charge;
    }

    public function isRefund(): bool
    {
        return $this->type === GiftCardAdjustmentType::Refund;
    }
}
