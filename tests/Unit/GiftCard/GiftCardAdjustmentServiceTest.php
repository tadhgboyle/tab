<?php

namespace Tests\Unit\GiftCard;
use App\Models\GiftCard;
use App\Models\Role;
use App\Models\Rotation;
use App\Models\Transaction;
use App\Models\User;
use App\Services\GiftCards\GiftCardAdjustmentService;
use Cknow\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
        $transaction = Transaction::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => Rotation::factory()->create()->id,
            'total_price' => Money::parse(100_00),
            'purchaser_amount' => Money::parse(90_00),
            'gift_card_amount' => Money::parse(10_00),
        ]);

        $service = new GiftCardAdjustmentService($giftCard, $transaction);
        $service->charge(Money::parse(10_00));

        $this->assertDatabaseHas('gift_card_adjustments', [
            'gift_card_id' => $giftCard->id,
            'transaction_id' => $transaction->id,
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
        $transaction = Transaction::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => Rotation::factory()->create()->id,
            'total_price' => Money::parse(100_00),
            'purchaser_amount' => Money::parse(90_00),
            'gift_card_amount' => Money::parse(10_00),
        ]);

        $service = new GiftCardAdjustmentService($giftCard, $transaction);
        $service->refund(Money::parse(10_00));

        $this->assertDatabaseHas('gift_card_adjustments', [
            'gift_card_id' => $giftCard->id,
            'transaction_id' => $transaction->id,
            'amount' => 10_00,
            'type' => 'refund',
        ]);

        $this->assertEquals(GiftCardAdjustmentService::RESULT_SUCCESS, $service->getResult());
    }
}