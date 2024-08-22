<?php

namespace App\Enums;

enum GiftCardAdjustmentType: string {
    case Charge = 'charge';
    case Refund = 'refund';
}