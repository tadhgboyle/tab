<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Services\Transactions\TransactionCreationService;
use App\Services\Transactions\TransactionReturnService;
use Arr;
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

                $service = new TransactionCreationService(new Request([
                    'purchaser_id' => $user->id,
                    'cashier_id' => $cashier->id,
                    'product' => $product_ids,
                    'quantity' => $quantity,
                    'created_at' => Carbon::now()->addMinutes(rand(-4000, 4000))
                ]));

                if ($service->getResult() != TransactionCreationService::RESULT_SUCCESS) {
                    continue;
                }

                $transaction = $service->getTransaction();

                if (rand(0, 3) == 3) {

                    if (rand(0, 1) == 1) {
                        
                        (new TransactionReturnService($transaction))->return();

                    } else {

                        $product_id = Arr::random($product_ids->all());
                        $returning = rand(0, $quantity[$product_id]);

                        for ($i = 0; $i <= $returning; $i++) { 
                            (new TransactionReturnService($transaction))->returnItem($product_id);
                        }
                    }
                }
            }
        }
    }
}
