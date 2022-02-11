<?php

namespace App\Services\Payouts;

use App\Models\Payout;

trait PayoutService
{
    protected Payout $_payout;

    final public function getPayout(): Payout
    {
        return $this->_payout;
    }
}
