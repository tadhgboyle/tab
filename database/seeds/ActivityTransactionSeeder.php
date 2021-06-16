<?php

namespace Database\Seeders;

use Auth;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityTransactionSeeder extends Seeder
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

            $user_activites = $activities->shuffle()->random(rand(0, 2));

            foreach ($user_activites as $activity) {

                $cashier = $users->shuffle()->whereIn('role_id', [1, 2])->first();
                Auth::login($cashier);

                $activity->registerUser($user);

            }
        }
    }
}
