<?php

namespace Tests\Unit\Admin\Order;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\GiftCard;
use App\Models\Settings;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Database\Seeders\RotationSeeder;
use App\Enums\GiftCardAdjustmentType;
use App\Services\Orders\OrderCreateService;
use App\Services\Orders\OrderReturnService;
use App\Services\Orders\OrderReturnProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderReturnServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCanReturnOrder(): void
    {
        [, $order] = $this->createFakeRecords();

        $orderService = (new OrderReturnService($order));
        $this->assertSame(OrderReturnService::RESULT_SUCCESS, $orderService->getResult());

        $this->assertSame(OrderStatus::FullyReturned, $order->status);
        $this->assertTrue($order->isReturned());
    }

    public function testUserBalanceUpdated(): void
    {
        [$user, $order] = $this->createFakeRecords();
        $balance_before = $user->balance;

        $orderService = (new OrderReturnService($order));
        $this->assertSame(OrderReturnService::RESULT_SUCCESS, $orderService->getResult());

        $this->assertSame(OrderStatus::FullyReturned, $order->status);
        $this->assertTrue($order->isReturned());
        $this->assertEquals(
            $balance_before->add($order->purchaser_amount),
            $user->refresh()->balance
        );
        $this->assertEquals($order->purchaser_amount, $user->findReturned());
    }

    public function testGiftCardBalanceUpdated(): void
    {
        $role = Role::factory()->create([
            'name' => 'gift card issuer',
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $giftCard = GiftCard::factory()->create([
            'remaining_balance' => 100_00,
            'issuer_id' => $user->id,
        ]);

        $balance_before_order = $giftCard->remaining_balance;
        [,$order] = $this->createFakeRecords(gift_card_code: $giftCard->code);
        $orderService = new OrderReturnService($order);

        $this->assertSame(OrderReturnService::RESULT_SUCCESS, $orderService->getResult());
        $this->assertSame(OrderStatus::FullyReturned, $order->status);
        $this->assertTrue($order->isReturned());

        $this->assertEquals(
            $balance_before_order,
            $giftCard->refresh()->remaining_balance
        );

        $giftCardAdjustment = $giftCard->adjustments->last();
        $this->assertEquals($order->gift_card_amount, $giftCardAdjustment->amount);
        $this->assertEquals(GiftCardAdjustmentType::Refund, $giftCardAdjustment->type);
        $this->assertEquals($order->id, $giftCardAdjustment->order_id);
    }

    public function testGiftCardBalanceUpdatedAfterProductReturn(): void
    {
        $role = Role::factory()->create([
            'name' => 'gift card issuer',
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $giftCard = GiftCard::factory()->create([
            'remaining_balance' => 100_00,
            'original_balance' => 100_00,
            'issuer_id' => $user->id,
        ]);

        $balance_before_order = $giftCard->original_balance;
        [$cashier, $order] = $this->createFakeRecords(gift_card_code: $giftCard->code);
        $productReturning = $order->products->first();
        new OrderReturnProductService($productReturning);
        $amountAlreadyRefundedToGiftCard = $productReturning->product->getPriceAfterTax();
        $this->assertEquals($amountAlreadyRefundedToGiftCard, $order->getReturnedTotalToGiftCard());

        $orderService = new OrderReturnService($order->refresh());

        $this->assertSame(OrderReturnService::RESULT_SUCCESS, $orderService->getResult());
        $this->assertSame(OrderStatus::FullyReturned, $order->status);
        $this->assertTrue($order->isReturned());

        $this->assertEquals(
            $balance_before_order,
            $giftCard->refresh()->remaining_balance
        );
        $giftCardAmountRefunded = $order->gift_card_amount->subtract($amountAlreadyRefundedToGiftCard);

        $giftCardAdjustment = $giftCard->adjustments->last();
        $this->assertEquals($giftCardAmountRefunded, $giftCardAdjustment->amount);
        $this->assertEquals(GiftCardAdjustmentType::Refund, $giftCardAdjustment->type);
        $this->assertEquals($order->id, $giftCardAdjustment->order_id);

        $orderReturn = $order->return;
        $this->assertEquals($cashier->id, $orderReturn->returner_id);
        $this->assertEquals($giftCardAmountRefunded, $orderReturn->gift_card_amount);
        $this->assertEquals($order->purchaser_amount, $orderReturn->purchaser_amount);
        $this->assertEquals($order->purchaser_amount->add($giftCardAmountRefunded), $orderReturn->total_return_amount);
        $this->assertFalse($orderReturn->caused_by_product_return);
    }

    public function testProductReturnedValueUpdated(): void
    {
        [, $order, $hat] = $this->createFakeRecords();
        $hatOrderProduct = $order->products->firstWhere('product_id', $hat->id);

        $this->assertSame(0, $hatOrderProduct->refresh()->returned);

        $orderService = (new OrderReturnService($order));
        $this->assertSame(OrderReturnService::RESULT_SUCCESS, $orderService->getResult());

        $this->assertSame(2, $hatOrderProduct->refresh()->returned);
    }

    public function testCannotReturnFullyReturnedOrder(): void
    {
        [$user, $order] = $this->createFakeRecords();

        $orderService1 = (new OrderReturnService($order));
        $this->assertSame(OrderReturnService::RESULT_SUCCESS, $orderService1->getResult());

        $orderService2 = (new OrderReturnService($order));
        $this->assertSame(OrderReturnService::RESULT_ALREADY_RETURNED, $orderService2->getResult());

        $this->assertSame(OrderStatus::FullyReturned, $order->status);
        $this->assertEquals($order->total_price, $user->findReturned());
    }

    public function testProductStockIsNotRestoredIfSettingDisabled(): void
    {
        [, $order, $hat] = $this->createFakeRecords(5);

        $hat->update([
            'restore_stock_on_return' => false,
            'stock' => $start_stock = 12,
        ]);

        new OrderReturnService($order);

        $this->assertSame($start_stock, $hat->refresh()->stock);
    }

    public function testProductStockIsRestoredIfSettingEnabled(): void
    {
        [, $order, $hat] = $this->createFakeRecords($hat_count = 5);

        $hat->update([
            'restore_stock_on_return' => true,
            'stock' => $start_stock = 12,
        ]);

        new OrderReturnService($order);

        $this->assertSame($start_stock + $hat_count, $hat->refresh()->stock);
    }

    /**
     * @param int $hat_count
     * @param string|null $gift_card_code
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
