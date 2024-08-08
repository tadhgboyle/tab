<?php

namespace App\Services\GiftCards;

use App\Models\GiftCard;
use App\Models\GiftCardAdjustment;
use App\Models\Transaction;
use App\Services\GiftCards\GiftCardService;
use App\Services\Service;
use Cknow\Money\Money;

class GiftCardAdjustmentService extends Service
{
    use GiftCardService;

    public const RESULT_SUCCESS = 'SUCCESS';

    private Transaction $_transaction;

    public function __construct(GiftCard $giftCard, Transaction $transaction)
    {
        $this->_gift_card = $giftCard;
        $this->_transaction = $transaction;
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
            'transaction_id' => $this->_transaction->id,
            'amount' => $amount,
            'type' => $type,
        ]);

        $this->_result = self::RESULT_SUCCESS;
    }
}