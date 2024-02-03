<?php

namespace Tests\Unit\Payout;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Payout;
use Cknow\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $_user;
    private User $_cashier_user;
    private Payout $_payout;

    public function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create();

        $this->_user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $this->_cashier_user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $this->_payout = Payout::factory()->create([
            'user_id' => $this->_user->id,
            'cashier_id' => $this->_cashier_user->id,
            'amount' => 10_00,
        ]);
    }

    public function testAmountCastedToMoneyObject(): void
    {
        $this->assertInstanceOf(Money::class, $this->_payout->amount);
        $this->assertEquals(Money::parse(10_00), $this->_payout->amount);
    }

    public function testBelongsToUser(): void
    {
        $this->assertInstanceOf(User::class, $this->_payout->user);
        $this->assertEquals($this->_user->id, $this->_payout->user->id);
    }

    public function testBelongsToCashier(): void
    {
        $this->assertInstanceOf(User::class, $this->_payout->cashier);
        $this->assertEquals($this->_cashier_user->id, $this->_payout->cashier->id);
    }
}
