<?php

namespace App\Services\GiftCards;

use App\Models\Order;
use Cknow\Money\Money;
use App\Models\GiftCard;
use App\Services\Service;
use App\Models\GiftCardAdjustment;

class GiftCardAdjustmentService extends Service
{
    use GiftCardService;

    public const RESULT_SUCCESS = 'SUCCESS';

    private Order $_order;

    public function __construct(GiftCard $giftCard, Order $order)
    {
        $this->_gift_card = $giftCard;
        $this->_order = $order;
    }

    public function charge(Money $amount): void
    {
        $this->createAdjustment($amount, GiftCardAdjustment::TYPE_CHARGE);
    }

    public function refund(Money $amount): void
    {
        $this->createAdjustment($amount, GiftCardAdjustment::TYPE_REFUND);
    }

    private function createAdjustment(Money $amount, string $type): void
    {
        $this->_gift_card->adjustments()->create([
            'order_id' => $this->_order->id,
            'amount' => $amount,
            'type' => $type,
        ]);

        $this->_result = self::RESULT_SUCCESS;
    }
}
