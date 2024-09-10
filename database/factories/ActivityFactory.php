<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    private static array $activityNames = [
        'Fireside', 'Soccer Game', 'Football Game', 'Hockey Game', 'Scavenger Hunt',
        'Canoeing', 'Paintball', 'Surprise', 'Nail Painting', 'Widegame', 'Ski School',
        'Beach Games', 'Carnival', 'Trivia Night', 'Escape Room', 'Dance Party',
        'Movie Marathon', 'Karaoke Night', 'Cooking Class', 'Photography Contest',
        'Board Games Night', 'Rock Climbing', 'Gardening Workshop', 'Bowling',
        'Mystery Dinner', 'Art Exhibition', 'Tech Hackathon', 'Yoga Retreat',
        'Outdoor Movie Night', 'Mini Golf Tournament', 'Bike Tour', 'Ice Cream Social',
        'Book Club', 'Language Exchange', 'Science Fair', 'Potluck Dinner',
        'Volunteer Day', 'Fashion Show', 'Talent Show', 'Robotics Workshop',
        'Hot Air Balloon Ride', 'Fitness Bootcamp', 'DIY Craft Workshop',
        'Archery Session', 'Wine Tasting', 'Virtual Reality Gaming', 'Escape to Nature',
        'Zumba Party', 'Mindfulness Meditation', 'Puzzle Challenge', 'Kayaking Adventure'
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

        $price = $this->faker->boolean(5) ? 0_00 : $this->faker->numberBetween(0, 50_00);
        // round to nearest $0.50
        $price = round($price / 50) * 50;
        $price = $price / 100;

        return [
            'name' => $this->faker->unique()->randomElement(self::$activityNames),
            'location' => $this->faker->address,
            'description' => $this->faker->text(75),
            'unlimited_slots' => random_int(0, 3) === 0,
            'slots' => $this->faker->numberBetween(3, 30),
            'price' => $price,
            'pst' => $this->faker->boolean,
            'start' => $start_date,
            'end' => $end_date
        ];
    }
}
