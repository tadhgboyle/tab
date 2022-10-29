<?php

namespace Tests\Feature\Payout;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\PayoutRequest;
use App\Services\Payouts\PayoutCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayoutCreationTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreatePayout(): void
    {
        [$user, $admin] = $this->createData();

        $payoutService = new PayoutCreationService(new PayoutRequest([
            'identifier' => '#1',
            'amount' => 10,
        ]), $user);

        $this->assertSame(PayoutCreationService::RESULT_SUCCESS, $payoutService->getResult());

        $payout = $payoutService->getPayout();

        $this->assertSame(10.00, $payout->amount);
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
            'amount' => 10,
        ]), $user);

        $this->assertSame(PayoutCreationService::RESULT_SUCCESS, $payoutService->getResult());

        $payout = $payoutService->getPayout();
        $this->assertSame($owing_before_payout - $payout->amount, $user->findOwing());
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
