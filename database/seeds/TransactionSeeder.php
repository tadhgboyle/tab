<?php

namespace Database\Seeders;

use Auth;
use App\Models\User;
use App\Models\Product;
use App\Models\Rotation;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use App\Services\Transactions\TransactionReturnService;
use App\Services\Transactions\TransactionCreationService;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $users = User::all();
        $products_all = Product::all();

        foreach ($users as $user) {
            $transactions = random_int(0, 6);

            for ($i = 0; $i <= $transactions; $i++) {
                $cashier = $users->shuffle()->whereIn('role_id', [1, 2])->first();
                Auth::login($cashier);

                if ($user->id === $cashier->id) {
                    continue;
                }

                $product_ids = $products_all->random(random_int(1, 5))->pluck('id');

                $quantity = [];
                foreach ($product_ids as $product_id) {
                    $quantity[$product_id] = random_int(1, 4);
                }

                /** @var Rotation $rotation */
                $rotation = $user->rotations->random();

                $service = new TransactionCreationService(new Request([
                    'purchaser_id' => $user->id,
                    'cashier_id' => $cashier->id,
                    'rotation_id' => $rotation->id,
                    'product' => $product_ids,
                    'quantity' => $quantity,
                    'created_at' => $rotation->start->addDays(random_int(1, 6))->addMillis(random_int(-99999, 99999)),
                ]));

                if ($service->getResult() !== TransactionCreationService::RESULT_SUCCESS) {
                    continue;
                }

                $transaction = $service->getTransaction();

                if (random_int(0, 3) === 3) {
                    if (random_int(0, 1) === 1) {
                        (new TransactionReturnService($transaction))->return();
                    } else {
                        $product_id = $product_ids->random();
                        $returning = random_int(0, $quantity[$product_id]);

                        for ($j = 0; $j <= $returning; $j++) {
                            (new TransactionReturnService($transaction))->returnItem($product_id);
                        }
                    }
                }
            }
        }
    }
}
