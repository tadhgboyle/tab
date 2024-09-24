<?php

namespace Tests\Unit\Admin\Payout;

use Tests\FormRequestTestCase;
use App\Http\Requests\PayoutRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayoutRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

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
