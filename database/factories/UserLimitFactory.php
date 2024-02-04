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
        $limit = $this->faker->boolean(15) ? -1_00 : $this->faker->numberBetween(0, 200_00);
        // round to nearest $5.00
        $limit = round($limit / 500) * 500;
        $limit = $limit / 100;

        return [
            'limit' => $limit,
            'duration' => $this->faker->randomElement(self::$durations),
        ];
    }
}
