<?php

namespace Tests\Feature\Payout;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Cknow\Money\Money;
use App\Http\Requests\PayoutRequest;
use App\Services\Payouts\PayoutCreateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayoutCreateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreatePayout(): void
    {
        [$user, $admin] = $this->createData();

        $payoutService = new PayoutCreateService(new PayoutRequest([
            'identifier' => '#1',
            'amount' => 10_00,
        ]), $user);

        $this->assertSame(PayoutCreateService::RESULT_SUCCESS, $payoutService->getResult());

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

        $payoutService = new PayoutCreateService(new PayoutRequest([
            'identifier' => '#1',
            'amount' => 10_00,
        ]), $user);

        $this->assertSame(PayoutCreateService::RESULT_SUCCESS, $payoutService->getResult());
        $this->assertStringContainsString("Successfully created payout of $10.00 for {$user->full_name}", $payoutService->getMessage());

        $payout = $payoutService->getPayout();
        $this->assertEquals($owing_before_payout->subtract($payout->amount), $user->refresh()->findOwing());
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
