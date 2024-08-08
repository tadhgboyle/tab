<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'gift_card_amount' => 0_00,
            'status' => Order::STATUS_NOT_RETURNED,
            'created_at' => now()
        ];
    }
}
