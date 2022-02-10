<?php

namespace Database\Seeders;

use App\Http\Requests\PayoutRequest;
use App\Models\User;
use App\Services\Payouts\PayoutCreationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Seeder;

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

            new PayoutCreationService(new PayoutRequest([
                'identifier' => random_int(0, 1) === 1 ? '#' . random_int(101010, 202020) : null,
                'amount' => random_int(1, ($user->findOwing() / random_int(2, 5))),
            ]), $user);
        }
    }
}
