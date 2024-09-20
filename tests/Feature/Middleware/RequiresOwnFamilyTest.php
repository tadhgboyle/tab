<?php

namespace Tests\Feature\Middleware;

use App\Enums\FamilyMemberRole;
use App\Models\Family;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequiresOwnFamilyTest extends TestCase
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
    }

    public function test403PageIsRenderedIfNotPartOfFamily(): void
    {
        $this->actingAs($this->_user)
            ->get(route('family_view', ['family' => $this->_family]))
            ->assertForbidden();
    }

    public function testNextIsCalledIfPartOfFamily(): void
    {
        $this->_family->members()->create([
            'user_id' => $this->_user->id,
            'role' => FamilyMemberRole::Member,
        ]);

        $this->actingAs($this->_user)
            ->get(route('family_view', ['family' => $this->_family]))
            ->assertOk();
    }
}
