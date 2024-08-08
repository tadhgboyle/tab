<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Auth;
use App\Models\User;
use App\Models\Product;
use App\Models\GiftCard;
use App\Models\Rotation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use App\Services\Transactions\TransactionReturnService;
use App\Services\Transactions\TransactionCreateService;
use App\Services\Transactions\TransactionReturnProductService;

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
            $transactions = random_int(1, 25);

            for ($i = 0; $i <= $transactions; $i++) {
                $cashier = $users->shuffle()->whereIn('role_id', [1, 2, 4])->first();
                Auth::login($cashier);

                $product_ids = $products_all->random(random_int(1, 7))->pluck('id');

                $products = [];
                foreach ($product_ids as $product_id) {
                    $products[] = [
                        'id' => $product_id,
                        'quantity' => random_int(1, 4),
                    ];
                }

                /** @var Rotation $rotation */
                $rotation = $user->rotations->random();
                if ($rotation->isFuture()) {
                    continue;
                }
                $created_at = $rotation->start->addSeconds(random_int(0, $rotation->end->diffInSeconds($rotation->start)));
                if ($created_at->isFuture()) {
                    $created_at = $rotation->start->addSeconds(random_int(0, now()->diffInSeconds($rotation->start)));
                }

                if (random_int(0, 5) === 0) {
                    $user->load('giftCards');

                    $giftCards = random_int(0, 1) === 1 ? $user->giftCards->where('remaining_balance', '>', 0) : GiftCard::where('remaining_balance', '>', 0)->get();
                    if ($giftCards->count() === 0) {
                        $giftCard = null;
                    } else {
                        $giftCard = $giftCards->random();
                        if ($giftCard->expired() || !$giftCard->canBeUsedBy($user)) {
                            $giftCard = null;
                        }
                    }
                }

                new TransactionCreateService(new Request([
                    'purchaser_id' => $user->id,
                    'cashier_id' => $cashier->id,
                    'rotation_id' => $rotation->id,
                    'products' => json_encode($products),
                    'created_at' => $created_at,
                    'gift_card_code' => $giftCard->code ?? null,
                ]), $user);
            }
        }

        foreach (Transaction::all() as $transaction) {
            if (random_int(0, 3) === 3) {
                if (random_int(0, 1) === 1) {
                    (new TransactionReturnService($transaction));
                } else {
                    $transactionProduct = $transaction->products->random();
                    $max_to_return = $transactionProduct->quantity;
                    $returning = random_int(1, $max_to_return);

                    for ($j = 0; $j <= $returning; $j++) {
                        (new TransactionReturnProductService($transactionProduct));
                    }
                    if (random_int(0, 1) === 1) {
                        (new TransactionReturnService($transaction));
                    }
                }
            }
        }
    }
}
