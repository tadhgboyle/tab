<?php

namespace Tests\Unit\Order;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use Cknow\Money\Money;
use App\Models\GiftCard;
use App\Models\Rotation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $purchaser;
    private Order $order;
    private Rotation $rotation;

    public function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create();
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $this->purchaser = $user;

        $this->rotation = Rotation::factory()->create();

        $this->order = Order::factory()->create([
            'purchaser_id' => $this->purchaser->id,
            'cashier_id' => $this->purchaser->id,
            'rotation_id' => $this->rotation->id,
            'total_price' => 5_00,
            'purchaser_amount' => 5_00,
        ]);
    }

    public function testTotalPriceCastedToMoneyObject(): void
    {
        $this->assertInstanceOf(Money::class, $this->order->total_price);
        $this->assertEquals(5_00, $this->order->total_price->getAmount());
    }

    public function testHasPurchaser(): void
    {
        $this->assertInstanceOf(User::class, $this->order->purchaser);
        $this->assertEquals($this->purchaser->id, $this->order->purchaser->id);
    }

    public function testHasCashier(): void
    {
        $this->assertInstanceOf(User::class, $this->order->cashier);
        $this->assertEquals($this->purchaser->id, $this->order->cashier->id);
    }

    public function testHasRotation(): void
    {
        $this->assertInstanceOf(Rotation::class, $this->order->rotation);
        $this->assertEquals($this->rotation->id, $this->order->rotation->id);
    }

    public function testHasProducts(): void
    {
        $this->markTestIncomplete();
    }

    public function testBelongsToGiftCard(): void
    {
        $gift_card = GiftCard::factory()->create();
        $order = Order::factory()->create([
            'gift_card_id' => $gift_card->id,
            'purchaser_id' => $this->purchaser->id,
            'cashier_id' => $this->purchaser->id,
            'rotation_id' => $this->rotation->id,
            'total_price' => 5_00,
            'purchaser_amount' => 2_50,
            'gift_card_amount' => 2_50,
        ]);

        $this->assertInstanceOf(GiftCard::class, $order->giftCard);
        $this->assertEquals($gift_card->id, $order->giftCard->id);
    }

    public function testGetReturnedTotalIsFullPriceIfFullyReturned(): void
    {
        $this->markTestIncomplete();
    }

    public function testGetReturnedTotalIsZeroIfNotReturned(): void
    {
        $this->markTestIncomplete();
    }

    public function testGetReturnedTotalIsPartialPriceIfPartiallyReturned(): void
    {
        $this->markTestIncomplete();
    }

    public function testIsReturnedIsTrueIfFullyReturned(): void
    {
        $this->markTestIncomplete();
    }

    public function testGetReturnStatusIsFullyReturnedIfReturned(): void
    {
        $this->markTestIncomplete();
    }

    public function testGetReturnStatusIsNotReturnedIfNotReturned(): void
    {
        $this->markTestIncomplete();
    }

    public function testGetReturnStatusIsPartiallyReturnedIfPartiallyReturned(): void
    {
        $this->markTestIncomplete();
    }
}
