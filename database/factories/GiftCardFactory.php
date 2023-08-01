<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GiftCardFactory extends Factory {

    public function definition(): array {
        $original_balance = $this->faker->numberBetween(10_00, 1000_00);
        $users = User::all();
        $issuer = $users->shuffle()->whereIn('role_id', [1, 2])->first();

        return [
            'name' => $this->faker->unique()->word,
            'code' => Str::upper(Str::random(10)),
            'original_balance' => $original_balance,
            'remaining_balance' => $this->faker->boolean
                ? $this->faker->numberBetween(0, $original_balance)
                : $original_balance,
            'issuer_id' => $issuer->id,
            'created_at' => $this->faker->dateTimeBetween($issuer->created_at),
        ];
    }
}
