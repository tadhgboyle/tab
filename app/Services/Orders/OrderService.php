<?php

namespace App\Services\Orders;

use App\Models\Order;

trait OrderService
{
    protected Order $_order;

    final public function getOrder(): Order
    {
        return $this->_order;
    }
}
