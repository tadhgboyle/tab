<?php

namespace Tests\Feature\Payout;

use App\Http\Requests\PayoutRequest;
use Tests\FormRequestTestCase;

class PayoutRequestTest extends FormRequestTestCase
{
    public function testIdentifierIsNullable(): void
    {
        $this->assertNotHaveErrors('identifier', new PayoutRequest([
            'identifier' => null,
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
