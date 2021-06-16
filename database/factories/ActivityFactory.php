<?php

namespace Database\Factories;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Activity::class;

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
        $start_date = $this->faker->dateTimeThisMonth;
        $end_date = Carbon::instance($start_date)->addMinutes($this->faker->numberBetween(15, 1000));

        return [
            'name' => $this->faker->unique()->randomElement(static::$activityNames),
            'location' => $this->faker->address,
            'description' => $this->faker->text(75),
            'unlimited_slots' => $this->faker->boolean,
            'slots' => $this->faker->numberBetween(1, 15),
            'price' => $this->faker->randomFloat(2, 5, 50),
            'pst' => $this->faker->boolean,
            'start' => $start_date,
            'end' => $end_date
        ];
    }
}
