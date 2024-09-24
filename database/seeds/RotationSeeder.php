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
            'start' => Carbon::now()->subWeek()->setHour(0)->setMinute(0)->setSecond(0),
            'end' => Carbon::now()->setHour(0)->setMinute(0)->setSecond(0),
        ]);

        // Present
        Rotation::factory()->create([
            'name' => 'Week #2',
            'start' => Carbon::now()->setHour(0)->setMinute(0)->setSecond(0),
            'end' => Carbon::now()->addWeek()->setHour(0)->setMinute(0)->setSecond(0),
        ]);

        // Future
        Rotation::factory()->create([
            'name' => 'Week #3',
            'start' => Carbon::now()->addWeek()->setHour(0)->setMinute(0)->setSecond(0),
            'end' => Carbon::now()->addWeeks(2)->setHour(0)->setMinute(0)->setSecond(0),
        ]);

        $users = User::all();

        foreach ($users as $user) {
            if (random_int(0, 3) == 3) {
                $rotation = Rotation::all()->random();
                if (!$user->rotations()->get()->contains($rotation)) {
                    $user->rotations()->attach($rotation);
                }
            }

            if (random_int(0, 6) == 6) {
                $rotation = Rotation::all()->random();
                if (!$user->rotations()->get()->contains($rotation)) {
                    $user->rotations()->attach($rotation);
                }
            }

            $rotation = Rotation::all()->random();
            if (!$user->rotations()->get()->contains($rotation)) {
                $user->rotations()->attach($rotation);
            }
        }
    }
}
