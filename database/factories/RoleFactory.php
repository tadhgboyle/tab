<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'Superadmin',
            'superuser' => true,
            'order' => 1,
            'staff' => true,
            'permissions' => []
        ];
    }
}
