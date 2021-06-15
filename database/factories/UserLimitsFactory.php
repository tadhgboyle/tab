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

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'limit_per' => -1,
            'duration' => UserLimits::LIMIT_DAILY,
            'editor_id' => 1
        ];
    }
}
