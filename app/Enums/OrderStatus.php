<?php

namespace App\Enums;

enum OrderStatus: int {
    case NotReturned = 0;
    case PartiallyReturned = 1;
    case FullyReturned = 2;
}
