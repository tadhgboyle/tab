<?php

namespace Tests\Unit\Payout;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FormRequestTestCase;
use App\Http\Requests\PayoutRequest;

class PayoutRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testIdentifierIsUnique(): void
    {
        $role = Role::factory()->create([
            'name' => 'camper',
            'order' => 2,
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $payout = $user->payouts()->create([
            'identifier' => 'woooyeah',
            'cashier_id' => $user->id,
            'amount' => 10_00,
        ]);

        $this->assertHasErrors('identifier', new PayoutRequest([
            'identifier' => $payout->identifier,
        ]));
        $this->assertNotHaveErrors('identifier', new PayoutRequest([
            'identifier' => 'w000yeah',
        ]));
    }

    public function testPayoutAmountIsRequiredAndIsNumericAndNotNegative(): void
    {
        $this->assertHasErrors('amount', new PayoutRequest([
            'amount' => null,
        ]));

        $this->assertHasErrors('amount', new PayoutRequest([
            'amount' => 'string',
        ]));

        $this->assertHasErrors('amount', new PayoutRequest([
            'amount' => -1.00,
        ]));

        $this->assertNotHaveErrors('amount', new PayoutRequest([
            'amount' => 1.00,
        ]));
    }
}
