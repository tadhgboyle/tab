<?php

namespace Tests\Feature\Payout;

use Cknow\Money\Money;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\PayoutRequest;
use App\Services\Payouts\PayoutCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayoutCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreatePayout(): void
    {
        [$user, $admin] = $this->createData();

        $payoutService = new PayoutCreationService(new PayoutRequest([
            'identifier' => '#1',
            'amount' => 10_00,
        ]), $user);

        $this->assertSame(PayoutCreationService::RESULT_SUCCESS, $payoutService->getResult());

        $payout = $payoutService->getPayout();

        $this->assertEquals(Money::parse(10_00), $payout->amount);
        $this->assertEquals(Money::parse(-10_00), $user->findOwing());
        $this->assertSame('#1', $payout->identifier);
        $this->assertSame($admin->id, $payout->cashier->id);
        $this->assertSame($user->id, $payout->user->id);
    }

    public function testUserOwingCalculatedCorrectlyAfterPayoutCreation(): void
    {
        [$user] = $this->createData();

        $owing_before_payout = $user->findOwing();

        $payoutService = new PayoutCreationService(new PayoutRequest([
            'identifier' => '#1',
            'amount' => 10_00,
        ]), $user);

        $this->assertSame(PayoutCreationService::RESULT_SUCCESS, $payoutService->getResult());

        $payout = $payoutService->getPayout();
        $this->assertEquals($owing_before_payout->subtract($payout->amount), $user->findOwing());
    }

    /** @return User[] */
    private function createData(): array
    {
        $role = Role::factory()->create();
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $admin = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $this->actingAs($admin);

        return [$user, $admin];
    }
}
