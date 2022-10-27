<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Http\Requests\PayoutRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\Payouts\PayoutCreationService;

class PayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->findOwing() <= 1 || random_int(0, 5) <= 3) {
                continue;
            }
            $cashier = $users->shuffle()->whereIn('role_id', [1, 2])->first();
            Auth::login($cashier);

            // We shouldn't have to do this
            $amount = $user->findOwing() / random_int(2, 5);
            if ($amount <= 1) {
                continue;
            }

            new PayoutCreationService(new PayoutRequest([
                'identifier' => random_int(0, 1) === 1 ? '#' . random_int(101010, 202020) : null,
                'amount' => random_int(1, $amount),
            ]), $user);
        }
    }
}
