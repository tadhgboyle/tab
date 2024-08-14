<?php

namespace Tests\Unit\GiftCard;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\GiftCard;
use App\Helpers\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GiftCardAssignmentControllerTest extends TestCase
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

    public function testCanAssignGiftCardToUser(): void
    {
        $this->expectPermissionChecks([
            Permission::SETTINGS,
            Permission::SETTINGS_GIFT_CARDS_MANAGE,
        ]);

        $giftCard = GiftCard::factory()->create();

        $this->actingAs($this->_superuser)
            ->get(route('settings_gift-cards_assign', [$giftCard->id, $this->_superuser->id]))
            ->assertRedirect(route('settings_gift-cards_view', $giftCard->id))
            ->assertSessionHas('success', "Assigned gift card to {$this->_superuser->full_name}.");
        
        $giftCardAssignment = $giftCard->assignments()->first();
        $this->assertEquals($this->_superuser->id, $giftCardAssignment->user_id);
        $this->assertEquals($this->_superuser->id, $giftCardAssignment->assigner_id);

        $this->assertTrue($giftCard->canBeUsedBy($this->_superuser));
        $this->assertContains($giftCard->id, $this->_superuser->giftCards->pluck('id'));
    }

    public function testCannotAssignExpiredGiftCardToUser(): void
    {
        $this->expectPermissionChecks([
            Permission::SETTINGS,
            Permission::SETTINGS_GIFT_CARDS_MANAGE,
        ]);

        $giftCard = GiftCard::factory()->create([
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($this->_superuser)
            ->get(route('settings_gift-cards_assign', [$giftCard->id, $this->_superuser->id]))
            ->assertRedirect(route('settings_gift-cards_view', $giftCard->id))
            ->assertSessionHas('error', 'Cannot assign an expired gift card.');
        
        $this->assertEmpty($giftCard->assignments);
        $this->assertNotContains($giftCard, $this->_superuser->giftCards);
    }
}
