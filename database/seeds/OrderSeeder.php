<?php

namespace Database\Seeders;

use Auth;
use App\Models\User;
use App\Models\Product;
use App\Models\GiftCard;
use App\Models\Rotation;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use App\Services\Orders\OrderCreateService;
use App\Services\Orders\OrderReturnService;
use App\Services\Orders\OrderReturnProductService;

class OrderSeeder extends Seeder
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
            $orders = random_int(1, 25);

            for ($i = 0; $i <= $orders; $i++) {
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

                $created_at = $rotation->start->addSeconds(random_int(0, $rotation->end->diffInSeconds($rotation->start, true)));
                if ($created_at->isFuture()) {
                    $created_at = $rotation->start->addSeconds(random_int(0, now()->diffInSeconds($rotation->start, true)));
                }
                Carbon::setTestNow($created_at);

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

                new OrderCreateService(new Request([
                    'purchaser_id' => $user->id,
                    'cashier_id' => $cashier->id,
                    'products' => json_encode($products),
                    'gift_card_code' => $giftCard->code ?? null,
                ]), $user);

                Carbon::setTestNow(null);
            }
        }

        foreach (Order::all() as $order) {
            if (random_int(0, 3) === 3) {
                if (random_int(0, 1) === 1) {
                    new OrderReturnService($order);
                } else {
                    $orderProduct = $order->products->random();
                    $max_to_return = $orderProduct->quantity;
                    $returning = random_int(1, $max_to_return);

                    for ($j = 0; $j <= $returning; $j++) {
                        new OrderReturnProductService($orderProduct);
                    }
                    if (random_int(0, 1) === 1) {
                        new OrderReturnService($order);
                    }
                }
            }
        }
    }
}
