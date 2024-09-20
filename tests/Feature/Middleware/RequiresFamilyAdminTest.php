<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Family;
use App\Enums\FamilyMemberRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequiresFamilyAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $_user;
    private Family $_family;

    public function setUp(): void
    {
        parent::setUp();

        $this->_user = User::factory()->create([
            'role_id' => Role::factory()->create()->id,
        ]);

        $this->_family = Family::factory()->create([
            'name' => 'Smiths',
        ]);

        $this->_family->members()->create([
            'user_id' => $this->_user->id,
            'role' => FamilyMemberRole::Member,
        ]);
    }

    public function test403PageIsRenderedIfNotFamilyAdmin(): void
    {
        $this->actingAs($this->_user)
            ->get(route('family_pdf', ['family' => $this->_family]))
            ->assertForbidden();
    }

    public function testNextIsCalledIfFamilyAdmin(): void
    {
        $this->_user->familyMember()->update([
            'role' => FamilyMemberRole::Admin,
        ]);

        $this->actingAs($this->_user)
            ->get(route('family_pdf', ['family' => $this->_family]))
            ->assertOk();
    }
}
