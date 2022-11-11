<?php

namespace Database\Factories;

use App\Models\UserLimits;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserLimitsFactory extends Factory
{
    private static array $durations = [
        UserLimits::LIMIT_DAILY,
        UserLimits::LIMIT_WEEKLY
    ];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'limit_per' => $this->faker->boolean(25) ? -1_00 : $this->faker->numberBetween(5_00, 150_00),
            'duration' => $this->faker->randomElement(self::$durations),
        ];
    }
}
