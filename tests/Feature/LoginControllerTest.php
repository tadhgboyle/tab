<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $_user;

    public function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create();
        $this->_user = User::factory()->create([
            'role_id' => $role->id,
            'password' => bcrypt('password'),
        ]);
    }

    public function testCanViewLoginPageWhenLoggedOut(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('pages.login');
    }

    public function testCanLoginWithValidCredentials(): void
    {
        $this
            ->post(route('login_auth'), [
                'username' => $this->_user->username,
                'password' => 'password',
            ])
            ->assertRedirect('/');
    }

    public function testCannotLoginWithInvalidCredentials(): void
    {
        $this
            ->post(route('login_auth'), [
                'username' => $this->_user->username,
                'password' => 'invalid_password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Invalid credentials. Please try again.');
    }

    public function testCanLogoutIfLoggedIn(): void
    {
        $this
            ->actingAs($this->_user)
            ->get(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
