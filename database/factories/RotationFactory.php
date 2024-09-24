<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start = now()->setMinute(0)->setSecond(0);
        $end = $start->copy()->addDays(7);

        return [
            'name' => $this->faker->word,
            'start' => $start,
            'end' => $end,
        ];
    }
}
