<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
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
            'credit_amount' => 0_00,
            'returned' => false,
            'created_at' => now()
        ];
    }
}
