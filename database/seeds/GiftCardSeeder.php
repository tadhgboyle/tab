<?php

namespace Database\Seeders;

use Auth;
use App\Models\User;
use App\Models\GiftCard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GiftCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $giftCards = GiftCard::factory()->count(100)->create();
        $users = User::all();

        foreach ($giftCards as $giftCard) {
            if (random_int(0, 3)) {
                continue;
            }

            $users_count = random_int(1, 10);
            for ($i = 0; $i < $users_count; $i++) {
                $cashier = $users->shuffle()->whereIn('role_id', [1, 3])->first();
                Auth::login($cashier);
                $user = $users->shuffle()->first();

                $giftCard->assignments()->create([
                    'user_id' => $user->id,
                    'assigner_id' => $cashier->id,
                ]);
            }
        }
    }
}
