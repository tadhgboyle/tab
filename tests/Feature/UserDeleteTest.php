<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\Users\UserDeleteService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function testCanDeleteUser()
    {
        $user = $this->createUser();

        $userService = new UserDeleteService($user->id);

        $this->assertSame(UserDeleteService::RESULT_SUCCESS, $userService->getResult());
        $this->assertTrue($user->refresh()->deleted);
    }

    private function createUser(): User
    {
        $user = User::factory()->create([
            'role_id' => Role::factory()->create()
        ]);

        return $user;
    }
}