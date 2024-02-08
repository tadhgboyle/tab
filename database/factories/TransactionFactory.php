<?php

namespace Database\Factories;

use App\Models\Transaction;
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
            'status' => Transaction::STATUS_NOT_RETURNED,
            'created_at' => now()
        ];
    }
}
