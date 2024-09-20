<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Family;
use App\Enums\FamilyMemberRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequiresFamilyAdminOrSelfTest extends TestCase
{
    use RefreshDatabase;

    private User $_user;
    private User $_user2;
    private Family $_family;

    public function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create();
        $this->_user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $this->_user2 = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $this->_family = Family::factory()->create([
            'name' => 'Smiths',
        ]);

        $this->_family->members()->createMany([[
            'user_id' => $this->_user->id,
            'role' => FamilyMemberRole::Member,
        ], [
            'user_id' => $this->_user2->id,
            'role' => FamilyMemberRole::Member,
        ]]);
    }

    public function test403PageIsRenderedIfNotFamilyAdminAndNotSelf(): void
    {
        $this->actingAs($this->_user)
            ->get(route('family_member_pdf', ['family' => $this->_family, 'familyMember' => $this->_user2]))
            ->assertForbidden();
    }

    public function testNextIsCalledIfFamilyAdmin(): void
    {
        $this->_user->familyMember()->update([
            'role' => FamilyMemberRole::Admin,
        ]);

        $this->actingAs($this->_user)
        ->get(route('family_member_pdf', ['family' => $this->_family, 'familyMember' => $this->_user2]))
        ->assertOk();
    }

    public function testNextIsCalledIfSelf(): void
    {
        $this->actingAs($this->_user)
        ->get(route('family_member_pdf', ['family' => $this->_family, 'familyMember' => $this->_user]))
        ->assertOk();
    }
}
