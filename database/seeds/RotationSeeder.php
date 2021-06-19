<?php

namespace Database\Seeders;

use App\Models\Rotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RotationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 5; $i++) {
            Rotation::factory()->create([
                'name' => "Week #{$i}",
                'start' => Carbon::now()->addWeeks($i - 1)->subDay(),
                'end' => Carbon::now()->addWeeks($i + 1)
            ]);
        }

        $users = User::all();

        foreach ($users as $user) {
            if (rand(0, 3) == 3) {
                $user->rotations()->attach(Rotation::all()->random(1));
            }

            if (rand(0, 6) == 6) {
                $user->rotations()->attach(Rotation::all()->random(1));
            }

            $user->rotations()->attach(Rotation::all()->random(1));
        }
    }
}
