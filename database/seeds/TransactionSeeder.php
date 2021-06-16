<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Transactions\TransactionCreationService;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $products_all = Product::all();

        foreach ($users as $user) {

            $transactions = rand(0, 6);

            for ($i = 0; $i <= $transactions; $i++) {

                $cashier = $users->shuffle()->whereIn('role_id', [1, 2])->first();
                Auth::login($cashier);

                $product_ids = $products_all->random(rand(4, 7))->pluck('id');
    
                $quantity = [];
                foreach ($product_ids as $product_id) {
                    $quantity[$product_id] = rand(1, 3);
                }

                new TransactionCreationService(new Request([
                    'purchaser_id' => $user->id,
                    'cashier_id' => $cashier->id,
                    'product' => $product_ids,
                    'quantity' => $quantity,
                    'created_at' => Carbon::now()->addMinutes(rand(-4000, 4000))
                ]));
            }
        }

        $this->generateReturns();
    }

    private function generateReturns()
    {
        $transactions = Transaction::all();

        foreach ($transactions as $transaction) {



        }
    }
}
