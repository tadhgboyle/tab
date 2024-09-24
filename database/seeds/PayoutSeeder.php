<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Payout;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

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

            Payout::factory()->create([
                'user_id' => $user->id,
                'creator_id' => $cashier->id,
                'amount' => $amount->getAmount(),
            ]);
        }
    }
}
