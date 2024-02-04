<?php

namespace Database\Factories;

use App\Models\UserLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserLimitFactory extends Factory
{
    private static array $durations = [
        UserLimit::LIMIT_DAILY,
        UserLimit::LIMIT_WEEKLY
    ];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'limit' => $this->faker->boolean(25) ? -1_00 : $this->faker->numberBetween(5_00, 150_00),
            'duration' => $this->faker->randomElement(self::$durations),
        ];
    }
}
