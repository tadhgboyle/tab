<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Rotation;
use Illuminate\Database\Seeder;

class RotationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Past
        Rotation::factory()->create([
            'name' => 'Week #1',
            'start' => Carbon::now()->subWeek(),
            'end' => Carbon::now()
        ]);

        // Present
        Rotation::factory()->create([
            'name' => 'Week #2',
            'start' => Carbon::now(),
            'end' => Carbon::now()->addWeek()
        ]);

        // Future
        Rotation::factory()->create([
            'name' => 'Week #3',
            'start' => Carbon::now()->addWeek(),
            'end' => Carbon::now()->addWeeks(2)
        ]);

        $users = User::all();

        foreach ($users as $user) {
            if (random_int(0, 3) == 3) {
                $user->rotations()->attach(Rotation::all()->random(1));
            }

            if (random_int(0, 6) == 6) {
                $user->rotations()->attach(Rotation::all()->random(1));
            }

            $user->rotations()->attach(Rotation::all()->random(1));
        }
    }
}
