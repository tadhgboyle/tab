<?php

namespace Tests\Unit\GiftCard;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\GiftCard;
use App\Helpers\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GiftCardControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $_superadmin_role;
    private User $_superuser;

    public function setUp(): void
    {
        parent::setUp();

        $this->_superadmin_role = Role::factory()->create([
            'name' => 'Superadmin',
            'order' => 1,
            'superuser' => true,
        ]);

        $this->_superuser = User::factory()->create([
            'full_name' => 'Superuser User',
            'role_id' => $this->_superadmin_role->id,
        ]);
    }

    public function testCanViewGiftCardShowPage(): void
    {
        $this->expectPermissionChecks([
            Permission::SETTINGS,
            Permission::SETTINGS_GIFT_CARDS_MANAGE,
        ]);

        $giftCard = GiftCard::factory()->create();

        $this->actingAs($this->_superuser)
            ->get(route('settings_gift-cards_view', $giftCard->id))
            ->assertOk()
            ->assertViewIs('pages.settings.gift-cards.view');
    }

    public function testCanViewGiftCardCreatePage(): void
    {
        $this->expectPermissionChecks([
            Permission::SETTINGS,
            Permission::SETTINGS_GIFT_CARDS_MANAGE,
        ]);

        $this->actingAs($this->_superuser)
            ->get(route('settings_gift-cards_create'))
            ->assertOk()
            ->assertViewIs('pages.settings.gift-cards.form');
    }

    public function testCanCreateGiftCard(): void
    {
        $this->expectPermissionChecks([
            Permission::SETTINGS,
            Permission::SETTINGS_GIFT_CARDS_MANAGE,
        ]);

        $expires_at = now()->addDays(30);
        $this->actingAs($this->_superuser)
            ->post(route('settings_gift-cards_store'), [
                'code' => 'VALID_CODE',
                'balance' => 100,
                'expires_at' => $expires_at,
            ])
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Created new gift card VALID_CODE.');

        $this->assertDatabaseHas('gift_cards', [
            'code' => 'VALID_CODE',
            'original_balance' => 100,
            'remaining_balance' => 100,
            'expires_at' => $expires_at,
            'issuer_id' => $this->_superuser->id,
        ]);
    }

    public function testAjaxHasValidityWhenInvalidCodePassed(): void
    {
        $this->expectPermissionChecks([Permission::CASHIER]);

        $this->actingAs($this->_superuser)
            ->get(route('gift_cards_check_validity', ['code' => 'INVALID_CODE', 'purchaser_id' => $this->_superuser->id]))
            ->assertJson([
                'valid' => false,
                'message' => 'Invalid gift card code',
            ]);
    }

    public function testAjaxHasValidityWhenExpiredGiftCardPassed(): void
    {
        $this->expectPermissionChecks([Permission::CASHIER]);

        $giftCard = GiftCard::factory()->create([
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($this->_superuser)
            ->get(route('gift_cards_check_validity', ['code' => $giftCard->code, 'purchaser_id' => $this->_superuser->id]))
            ->assertJson([
                'valid' => false,
                'message' => 'Gift card has expired',
            ]);
    }

    public function testAjaxHasValidityWhenGiftCardCannotBeUsedByPurchaser(): void
    {
        $this->expectPermissionChecks([Permission::CASHIER]);

        $giftCard = GiftCard::factory()->create([
            'expires_at' => null,
        ]);

        $another_user = User::factory()->create([
            'role_id' => $this->_superadmin_role->id,
        ]);

        $giftCard->users()->attach($another_user);

        $this->actingAs($this->_superuser)
            ->get(route('gift_cards_check_validity', ['code' => $giftCard->code, 'purchaser_id' => $this->_superuser->id]))
            ->assertJson([
                'valid' => false,
                'message' => 'Gift card cannot be used by you',
            ]);
    }

    public function testAjaxHasValidityWhenGiftCardFullyUsed(): void
    {
        $this->expectPermissionChecks([Permission::CASHIER]);

        $giftCard = GiftCard::factory()->create([
            'remaining_balance' => 0,
            'expires_at' => null,
        ]);

        $this->actingAs($this->_superuser)
            ->get(route('gift_cards_check_validity', ['code' => $giftCard->code, 'purchaser_id' => $this->_superuser->id]))
            ->assertJson([
                'valid' => false,
                'message' => 'Gift card has no remaining balance',
            ]);
    }

    public function testAjaxHasValidityWhenGiftCardIsValid(): void
    {
        $this->expectPermissionChecks([Permission::CASHIER]);

        $giftCard = GiftCard::factory()->create([
            'remaining_balance' => 100,
            'expires_at' => null,
        ]);

        $this->actingAs($this->_superuser)
            ->get(route('gift_cards_check_validity', ['code' => $giftCard->code, 'purchaser_id' => $this->_superuser->id]))
            ->assertJson([
                'valid' => true,
                'remaining_balance' => 1,
            ]);
    }
}
