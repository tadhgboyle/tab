<?php

namespace Tests\Unit\Order;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\GiftCard;
use App\Models\Settings;
use Illuminate\Http\Request;
use App\Models\GiftCardAdjustment;
use Database\Seeders\RotationSeeder;
use App\Services\Orders\OrderCreateService;
use App\Services\Orders\OrderReturnProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderReturnProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testUserBalanceAndOrderTotalsUpdated(): void
    {
        [$user, $order, $hat] = $this->createFakeRecords();
        $balance_before = $user->balance;

        $hatOrderProduct = $order->products->firstWhere('product_id', $hat->id);
        $orderService = (new OrderReturnProductService($hatOrderProduct));
        $this->assertSame(OrderReturnProductService::RESULT_SUCCESS, $orderService->getResult());

        $order = $order->refresh();
        $this->assertSame(Order::STATUS_PARTIAL_RETURNED, $order->status);
        $this->assertEquals(
            $balance_before->add($hat->getPriceAfterTax()),
            $user->refresh()->balance
        );
        $this->assertEquals($hat->getPriceAfterTax(), $user->findReturned());
        $this->assertEquals($hat->getPriceAfterTax(), $order->getReturnedTotal());
    }

    public function testGiftCardBalanceUpdated(): void
    {
        $role = Role::factory()->create([
            'name' => 'Gift Card'
        ]);
        $gc_issuer = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $gift_card = GiftCard::factory()->create([
            'original_balance' => 100_00,
            'remaining_balance' => 100_00,
            'issuer_id' => $gc_issuer->id
        ]);
        $balance_before = $gift_card->original_balance;

        [, $order, $hat] = $this->createFakeRecords(gift_card_code: $gift_card->code);

        $hatOrderProduct = $order->products->firstWhere('product_id', $hat->id);
        $orderService = (new OrderReturnProductService($hatOrderProduct));

        $this->assertSame(OrderReturnProductService::RESULT_SUCCESS, $orderService->getResult());
        $this->assertSame(Order::STATUS_PARTIAL_RETURNED, $order->refresh()->status);
        $this->assertEquals(
            $balance_before->subtract($hat->getPriceAfterTax()), // since there are 2 hats, only 1 has been returned
            $gift_card->refresh()->remaining_balance
        );
        $this->assertDatabaseHas('gift_card_adjustments', [
            'gift_card_id' => $gift_card->id,
            'amount' => $hat->getPriceAfterTax()->getAmount(),
            'type' => GiftCardAdjustment::TYPE_REFUND
        ]);
    }

    public function testGiftCardBalanceUpdatedOnSecondReturn(): void
    {
        $role = Role::factory()->create([
            'name' => 'Gift Card'
        ]);
        $gc_issuer = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $gift_card = GiftCard::factory()->create([
            'original_balance' => 15_00,
            'remaining_balance' => 15_00,
            'issuer_id' => $gc_issuer->id
        ]);
        $balance_before = $gift_card->original_balance;

        [$user, $order, $hat] = $this->createFakeRecords(gift_card_code: $gift_card->code);

        $hatOrderProduct = $order->products->firstWhere('product_id', $hat->id);
        $expected_gift_card_refund = $order->gift_card_amount->subtract($hat->getPriceAfterTax());

        new OrderReturnProductService($hatOrderProduct);

        $orderService = new OrderReturnProductService($hatOrderProduct);

        $this->assertSame(OrderReturnProductService::RESULT_SUCCESS, $orderService->getResult());
        $this->assertSame(Order::STATUS_FULLY_RETURNED, $order->refresh()->status);
        $this->assertEquals(
            $balance_before,
            $gift_card->refresh()->remaining_balance
        );

        $giftCardAdjustment = $gift_card->adjustments->last();
        $this->assertEquals($order->id, $giftCardAdjustment->order_id);
        $this->assertEquals($expected_gift_card_refund, $giftCardAdjustment->amount);
        $this->assertEquals(GiftCardAdjustment::TYPE_REFUND, $giftCardAdjustment->type);
        $this->assertEquals($gift_card->id, $giftCardAdjustment->gift_card_id);

        $orderReturn = $order->return;
        $this->assertEquals($user->id, $orderReturn->returner_id);
        $this->assertEquals($expected_gift_card_refund, $orderReturn->gift_card_amount);
        $this->assertEquals($order->purchaser_amount, $orderReturn->purchaser_amount);
        $this->assertEquals($order->purchaser_amount->add($expected_gift_card_refund), $orderReturn->total_return_amount);
        $this->assertTrue($orderReturn->caused_by_product_return);
    }

    public function testProductReturnedValueUpdated(): void
    {
        [, $order, $hat] = $this->createFakeRecords();

        $hatOrderProduct = $order->products->firstWhere('product_id', $hat->id);
        $orderService = (new OrderReturnProductService($hatOrderProduct));
        $this->assertSame(OrderReturnProductService::RESULT_SUCCESS, $orderService->getResult());

        $hatOrderProduct = $order->products->firstWhere('product_id', $hat->id);
        $this->assertSame(1, $hatOrderProduct->refresh()->returned);
    }

    public function testCanReturnPartiallyReturnedItemInOrder(): void
    {
        [$user, $order, $hat] = $this->createFakeRecords();

        $hatOrderProduct = $order->products->firstWhere('product_id', $hat->id);
        new OrderReturnProductService($hatOrderProduct);
        $orderService = (new OrderReturnProductService($hatOrderProduct));
        $this->assertSame(OrderReturnProductService::RESULT_SUCCESS, $orderService->getResult());

        $order = $order->refresh();
        $this->assertSame(Order::STATUS_FULLY_RETURNED, $order->status);
        $this->assertTrue($order->isReturned());
        $this->assertEquals($order->total_price, $user->findReturned());
    }

    public function testCannotReturnFullyReturnedItemInOrder(): void
    {
        [$user, , $hat] = $this->createFakeRecords();

        $order_2_items = $this->createTwoItemOrder($user, $hat);
        $hatOrderProduct = $order_2_items->products->firstWhere('product_id', $hat->id);
        $orderService1 = (new OrderReturnProductService($hatOrderProduct));
        $this->assertSame(OrderReturnProductService::RESULT_SUCCESS, $orderService1->getResult());

        $orderService2 = (new OrderReturnProductService($hatOrderProduct));
        $this->assertSame(OrderReturnProductService::RESULT_SUCCESS, $orderService2->getResult());

        $orderService3 = (new OrderReturnProductService($hatOrderProduct));
        $this->assertSame(OrderReturnProductService::RESULT_ITEM_RETURNED_MAX_TIMES, $orderService3->getResult());

        $order_2_items = $order_2_items->refresh();
        $this->assertSame(Order::STATUS_PARTIAL_RETURNED, $order_2_items->status);
        $this->assertEquals($hat->getPriceAfterTax()->multiply(2), $user->findReturned());
    }

    public function testProductStockIsNotRestoredIfSettingDisabled(): void
    {
        [, $order, $hat] = $this->createFakeRecords(5);

        $hat->update([
            'restore_stock_on_return' => false,
            'stock' => $start_stock = 12,
        ]);

        $hatOrderProduct = $order->products->firstWhere('product_id', $hat->id);
        new OrderReturnProductService($hatOrderProduct);

        $this->assertSame($start_stock, $hat->refresh()->stock);
    }

    public function testProductStockIsRestoredIfSettingEnabled(): void
    {
        [, $order, $hat] = $this->createFakeRecords(5);

        $hat->update([
            'restore_stock_on_return' => true,
            'stock' => $start_stock = 12,
        ]);

        $hatOrderProduct = $order->products->firstWhere('product_id', $hat->id);
        new OrderReturnProductService($hatOrderProduct);

        $this->assertEquals($start_stock + 1, $hat->refresh()->stock);
    }

    /**
     * @param int $hat_count
     *
     * @return array<User, Order, Product>
     */
    private function createFakeRecords(int $hat_count = 2, ?string $gift_card_code = null): array
    {
        app(RotationSeeder::class)->run();

        $role = Role::factory()->create();

        /** @var User */
        $user = User::factory()->create([
            'role_id' => $role->id,
            'balance' => 300_00
        ]);

        $this->actingAs($user);

        $merch_category = Category::factory()->create([
            'name' => 'Merch'
        ]);

        $hat = Product::factory()->create([
            'name' => 'Hat',
            'price' => 11_99, // $13.43
            'category_id' => $merch_category->id,
            'pst' => true
        ]);

        Settings::factory()->createMany([
            [
                'setting' => 'gst',
                'value' => '5.00',
            ],
            [
                'setting' => 'pst',
                'value' => '7.00',
            ]
        ]);

        $order = (new OrderCreateService(new Request([
            'products' => json_encode([
                [
                    'id' => $hat->id,
                    'quantity' => $hat_count,
                ],
            ]),
            'gift_card_code' => $gift_card_code,
            'purchaser_id' => $user->id
        ]), $user))->getOrder(); // $26.8576 -> $3.1424

        return [$user, $order, $hat];
    }

    private function createTwoItemOrder(User $user, Product $hat): Order
    {
        $sweater = Product::factory()->create([
            'name' => 'Sweater',
            'category_id' => $hat->category_id,
            'price' => 39_99
        ]);

        return (new OrderCreateService(new Request([
            'products' => json_encode([
                [
                    'id' => $hat->id,
                    'quantity' => 2,
                ],
                [
                    'id' => $sweater->id,
                    'quantity' => 1
                ]
            ]),
            'purchaser_id' => $user->id
        ]), $user))->getOrder();
    }
}
