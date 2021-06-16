<?php

namespace Database\Factories;

use App\Models\UserLimits;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserLimitsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserLimits::class;

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
            'limit_per' => $this->faker->boolean(25) ? -1 : $this->faker->numberBetween(5, 150),
            'duration' => $this->faker->randomElement(static::$durations),
        ];
    }
}
