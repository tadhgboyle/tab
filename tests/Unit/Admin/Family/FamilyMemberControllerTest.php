<?php

namespace Tests\Unit\Admin\Family;
use App\Enums\FamilyMemberRole;
use App\Helpers\Permission;
use App\Models\Family;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $_superuser;
    private User $_user;

    private Family $_family;

    public function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create();
        $this->_superuser = User::factory()->create([
            'full_name' => 'Superuser User',
            'role_id' => $role->id,
        ]);
        $this->_user = User::factory()->create([
            'full_name' => 'User',
            'role_id' => $role->id,
        ]);

        $this->_family = Family::factory()->create();
        $this->_family->members()->create([
            'user_id' => $this->_superuser->id,
            'role' => FamilyMemberRole::Admin,
        ]);

        $this->actingAs($this->_superuser);
    }

    public function testCannotAddUserToFamilyIfAlreadyInFamily(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_MANAGE]);

        $this
            ->get(route('families_user_add', [$this->_family->id, $this->_superuser->id]))
            ->assertSessionHas('error', "{$this->_superuser->full_name} is already in a family.");
    }

    public function testCanAddUserToFamily(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_MANAGE]);

        $this
            ->get(route('families_user_add', [$this->_family->id, $this->_user->id]))
            ->assertSessionHas('success', "{$this->_user->full_name} added to {$this->_family->name}.");

        $this->assertEquals($this->_user->family->id, $this->_family->id);
        $this->assertEquals($this->_user->familyRole(), FamilyMemberRole::Member);
        $this->assertFalse($this->_user->isFamilyAdmin());
    }

    public function testCanUpdateFamilyMemberRole(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_MANAGE]);

        $this->_family->members()->create([
            'user_id' => $this->_user->id,
            'role' => FamilyMemberRole::Member,
        ]);

        $this->assertEquals($this->_user->familyRole(), FamilyMemberRole::Member);
        $this->assertFalse($this->_user->isFamilyAdmin());

        $this
            ->patch(route('families_user_update', [$this->_family->id, $this->_user->id]), [
                'role' => FamilyMemberRole::Admin->value,
            ])
            ->assertSessionHas('success', "{$this->_user->full_name} role updated to Admin.");

        $this->assertEquals($this->_user->refresh()->familyRole(), FamilyMemberRole::Admin);
        $this->assertTrue($this->_user->isFamilyAdmin());
    }

    public function testCanDeleteFamilyMember(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_MANAGE]);

        $this->_family->members()->create([
            'user_id' => $this->_user->id,
            'role' => FamilyMemberRole::Member,
        ]);

        $this
            ->delete(route('families_user_remove', [$this->_family->id, $this->_user->id]))
            ->assertSessionHas('success', "{$this->_user->full_name} removed from {$this->_family->name}.");

        $this->assertNull($this->_user->refresh()->family);
    }
}