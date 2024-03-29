<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Cknow\Money\Money;
use App\Models\GiftCard;
use App\Models\Rotation;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $purchaser;
    private Transaction $transaction;
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

        $this->transaction = Transaction::factory()->create([
            'purchaser_id' => $this->purchaser->id,
            'cashier_id' => $this->purchaser->id,
            'rotation_id' => $this->rotation->id,
            'total_price' => 5_00,
            'purchaser_amount' => 5_00,
        ]);
    }

    public function testTotalPriceCastedToMoneyObject(): void
    {
        $this->assertInstanceOf(Money::class, $this->transaction->total_price);
        $this->assertEquals(5_00, $this->transaction->total_price->getAmount());
    }

    public function testHasPurchaser(): void
    {
        $this->assertInstanceOf(User::class, $this->transaction->purchaser);
        $this->assertEquals($this->purchaser->id, $this->transaction->purchaser->id);
    }

    public function testHasCashier(): void
    {
        $this->assertInstanceOf(User::class, $this->transaction->cashier);
        $this->assertEquals($this->purchaser->id, $this->transaction->cashier->id);
    }

    public function testHasRotation(): void
    {
        $this->assertInstanceOf(Rotation::class, $this->transaction->rotation);
        $this->assertEquals($this->rotation->id, $this->transaction->rotation->id);
    }

    public function testHasProducts(): void
    {
        $this->markTestIncomplete();
    }

    public function testBelongsToGiftCard(): void
    {
        $gift_card = GiftCard::factory()->create();
        $transaction = Transaction::factory()->create([
            'gift_card_id' => $gift_card->id,
            'purchaser_id' => $this->purchaser->id,
            'cashier_id' => $this->purchaser->id,
            'rotation_id' => $this->rotation->id,
            'total_price' => 5_00,
            'purchaser_amount' => 2_50,
            'gift_card_amount' => 2_50,
        ]);

        $this->assertInstanceOf(GiftCard::class, $transaction->giftCard);
        $this->assertEquals($gift_card->id, $transaction->giftCard->id);
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
