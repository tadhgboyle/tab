<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasPermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $_user;
    private Role $_superuser_role;
    private Role $_camper_role;

    public function setUp(): void
    {
        parent::setUp();

        $this->_superuser_role = Role::factory()->create();

        $this->_camper_role = Role::factory()->create([
            'name' => 'Camper',
            'superuser' => false,
        ]);

        $this->_user = User::factory()->create([
            'role_id' => $this->_camper_role->id,
        ]);
    }

    public function test403PageIsRenderedIfNoPermission(): void
    {
        $this->actingAs($this->_user)
            ->get(route('users_list'))
            ->assertForbidden();
    }

    public function testNextIsCalledIfPermissionExists(): void
    {
        $this->_user->role_id = $this->_superuser_role->id;

        $this->actingAs($this->_user)
            ->get(route('users_list'))
            ->assertViewIs('pages.admin.users.list');
    }
}
