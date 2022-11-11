<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    private static array $activityNames = [
        'Fireside', 'Soccer Game', 'Football Game', 'Hockey Game', 'Scavanger Hunt',
        'Canoeing', 'Paintball', 'Surprise', 'Nail Painting', 'Widegame', 'Ski School',
        'Beach Games', 'Carnival'
    ];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start_date = Carbon::instance($this->faker->dateTimeThisMonth)->addDays($this->faker->numberBetween(14, 31));
        $end_date = $start_date->copy()->addMinutes($this->faker->numberBetween(0, 500));

        return [
            'name' => $this->faker->unique()->randomElement(self::$activityNames),
            'location' => $this->faker->address,
            'description' => $this->faker->text(75),
            'unlimited_slots' => random_int(0, 3) === 0,
            'slots' => $this->faker->numberBetween(3, 30),
            'price' => $this->faker->numberBetween(0, 50_00),
            'pst' => $this->faker->boolean,
            'start' => $start_date,
            'end' => $end_date
        ];
    }
}
