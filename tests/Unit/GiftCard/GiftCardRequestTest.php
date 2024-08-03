<?php

namespace Tests\Unit\GiftCard;

use App\Models\Role;
use App\Models\User;
use App\Models\GiftCard;
use Tests\FormRequestTestCase;
use App\Http\Requests\GiftCardRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GiftCardRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testCodeIsRequiredAndIsUnique(): void
    {
        $this->assertHasErrors('code', new GiftCardRequest([
            'code' => null,
        ]));

        $this->assertNotHaveErrors('code', new GiftCardRequest([
            'code' => 'VAL1D_C0D3',
        ]));

        $role = Role::factory()->create();
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $giftCard = GiftCard::factory()->create([
            'code' => 'VAL1D_C0D3',
            'issuer_id' => $user->id,
        ]);

        $this->assertHasErrors('code', new GiftCardRequest([
            'code' => 'VAL1D_C0D3',
        ]));

        $this->assertNotHaveErrors('code', new GiftCardRequest([
            'code' => 'VAL1D_C0D3',
            'gift_card_id' => $giftCard->id,
        ]));
    }

    public function testBalanceIsRequiredAndNumericAndMinZero(): void
    {
        $this->assertHasErrors('balance', new GiftCardRequest([
            'balance' => null,
        ]));

        $this->assertHasErrors('balance', new GiftCardRequest([
            'balance' => 'not-numeric',
        ]));

        $this->assertHasErrors('balance', new GiftCardRequest([
            'balance' => -1,
        ]));

        $this->assertNotHaveErrors('balance', new GiftCardRequest([
            'balance' => 0,
        ]));

        $this->assertNotHaveErrors('balance', new GiftCardRequest([
            'balance' => 1,
        ]));
    }

    public function testExpiresAtIsNullableAndIsDateAndIsAfterToday(): void
    {
        $this->assertNotHaveErrors('expires_at', new GiftCardRequest([
            'expires_at' => null,
        ]));

        $this->assertNotHaveErrors('expires_at', new GiftCardRequest([
            'expires_at' => now()->addDay(),
        ]));

        $this->assertHasErrors('expires_at', new GiftCardRequest([
            'expires_at' => now()->subDay(),
        ]));
    }
}
