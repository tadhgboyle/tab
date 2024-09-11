<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Activity;
use App\Services\Activities\ActivityRegistrationDeleteService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use App\Services\Activities\ActivityRegistrationCreateService;

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
            if (random_int(0, 5) === 0) {
                continue;
            }

            $user_activities = $activities->shuffle()->random(random_int(0, 4));

            foreach ($user_activities as $activity) {
                $cashier = $users->shuffle()->whereIn('role_id', [1, 2])->first();
                Auth::login($cashier);

                $service = new ActivityRegistrationCreateService($activity, $user);

                if (random_int(0, 4) === 0 && $service->getResult() === ActivityRegistrationCreateService::RESULT_SUCCESS) {
                    new ActivityRegistrationDeleteService($service->getActivityRegistration());
                }
            }
        }
    }
}
