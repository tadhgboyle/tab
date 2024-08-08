<?php

namespace App\Services\GiftCards;

use App\Models\GiftCard;

trait GiftCardService
{
    protected GiftCard $_gift_card;

    final public function getGiftCard(): GiftCard
    {
        return $this->_gift_card;
    }
}
