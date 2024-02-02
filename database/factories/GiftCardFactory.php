<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class GiftCardFactory extends Factory
{
    public function definition(): array
    {
        $original_balance = $this->faker->numberBetween(50, 500);
        // round to nearest $5
        $original_balance = round($original_balance / 5) * 5;
        $users = User::all();
        $issuer = $users->shuffle()->whereIn('role_id', [1, 2])->first();

        return [
            'name' => $this->faker->unique()->word,
            'code' => Str::upper(Str::random(10)),
            'original_balance' => $original_balance,
            'remaining_balance' => $original_balance,
            'issuer_id' => $issuer->id,
            'created_at' => $this->faker->dateTimeBetween($issuer->created_at),
        ];
    }
}
