<?php

namespace Database\Seeders;

use Str;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Http\Requests\PayoutRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\Payouts\PayoutCreateService;

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
            if ($user->findOwing()->isNegative() || random_int(0, 5) <= 3) {
                continue;
            }
            $cashier = $users->shuffle()->whereIn('role_id', [1, 3])->first();
            Auth::login($cashier);

            // We shouldn't have to do this
            $amount = $user->findOwing()->divide(random_int(2, 5));
            if ($amount->isNegative() || $amount->isZero()) {
                continue;
            }

            if (random_int(0, 1) === 0) {
                $identifier = '#' . random_int(101010, 202020);
            } else {
                $identifier = Str::random(10);
            }

            new PayoutCreateService(new PayoutRequest([
                'identifier' => $identifier,
                'amount' => random_int(1, $amount->getAmount()),
            ]), $user);
        }
    }
}
