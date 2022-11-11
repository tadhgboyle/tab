<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use App\Services\Activities\ActivityRegistrationCreationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class ActivityRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activities = Activity::all();
        $users = User::all();

        foreach ($users as $user) {
            if (random_int(0, 5) === 3) {
                continue;
            }

            $user_activities = $activities->shuffle()->random(random_int(0, 2));

            foreach ($user_activities as $activity) {
                $cashier = $users->shuffle()->whereIn('role_id', [1, 2])->first();
                Auth::login($cashier);

                (new ActivityRegistrationCreationService($activity, $user));
            }
        }
    }
}
