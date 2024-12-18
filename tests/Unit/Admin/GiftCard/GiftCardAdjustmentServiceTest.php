<?php

namespace Tests\Unit\Admin\GiftCard;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use Cknow\Money\Money;
use App\Models\GiftCard;
use App\Models\Rotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\GiftCards\GiftCardAdjustmentService;

class GiftCardAdjustmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateChargeAdjustment(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $giftCard = GiftCard::factory()->create();
        $order = Order::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => Rotation::factory()->create()->id,
            'total_price' => Money::parse(100_00),
            'total_tax' => Money::parse(0),
            'subtotal' => Money::parse(100_00),
            'purchaser_amount' => Money::parse(90_00),
            'gift_card_amount' => Money::parse(10_00),
        ]);

        $service = new GiftCardAdjustmentService($giftCard, $order);
        $service->charge(Money::parse(10_00));

        $this->assertDatabaseHas('gift_card_adjustments', [
            'gift_card_id' => $giftCard->id,
            'order_id' => $order->id,
            'amount' => 10_00,
            'type' => 'charge',
        ]);

        $this->assertEquals(GiftCardAdjustmentService::RESULT_SUCCESS, $service->getResult());
    }

    public function testCanCreateRefundAdjustment(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $giftCard = GiftCard::factory()->create();
        $order = Order::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => Rotation::factory()->create()->id,
            'total_price' => Money::parse(100_00),
            'total_tax' => Money::parse(0),
            'subtotal' => Money::parse(100_00),
            'purchaser_amount' => Money::parse(90_00),
            'gift_card_amount' => Money::parse(10_00),
        ]);

        $service = new GiftCardAdjustmentService($giftCard, $order);
        $service->refund(Money::parse(10_00));

        $this->assertDatabaseHas('gift_card_adjustments', [
            'gift_card_id' => $giftCard->id,
            'order_id' => $order->id,
            'amount' => 10_00,
            'type' => 'refund',
        ]);

        $this->assertEquals(GiftCardAdjustmentService::RESULT_SUCCESS, $service->getResult());
    }
}
