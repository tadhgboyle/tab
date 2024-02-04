<?php

namespace Tests\Unit\User;

use App\Models\Role;
use App\Models\User;
use App\Models\Rotation;
use Tests\FormRequestTestCase;
use App\Http\Requests\UserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $role = Role::factory()->create();
        $admin = User::factory()->create([
            'role_id' => $role->id
        ]);
        $this->actingAs($admin);
    }

    public function testFullNameIsRequiredAndIsUnique(): void
    {
        $role = Role::factory()->create([
            'name' => 'camper',
            'order' => 2,
        ]);

        $this->assertHasErrors('full_name', new UserRequest([
            'full_name' => null,
        ]));

        $this->assertNotHaveErrors('full_name', new UserRequest([
            'full_name' => 'Valid Name',
        ]));

        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $this->assertHasErrors('full_name', new UserRequest([
            'full_name' => $user->full_name,
        ]));

        $this->assertNotHaveErrors('full_name', new UserRequest([
            'full_name' => $user->full_name,
            'user_id' => $user->id,
        ]));
    }

    public function testUsernameIsNullableAndIsUnique(): void
    {
        $role = Role::factory()->create([
            'name' => 'camper',
            'order' => 2,
        ]);

        $this->assertNotHaveErrors('username', new UserRequest([
            'username' => null,
        ]));

        $this->assertNotHaveErrors('username', new UserRequest([
            'username' => 'Valid Username',
        ]));

        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $this->assertHasErrors('username', new UserRequest([
            'username' => $user->username,
        ]));

        $this->assertNotHaveErrors('username', new UserRequest([
            'username' => $user->username,
            'user_id' => $user->id,
        ]));
    }

    public function testBalanceIsNullableAndIsNumericAndHasMin(): void
    {
        $this->assertNotHaveErrors('balance', new UserRequest([
            'balance' => null,
        ]));

        $this->assertHasErrors('balance', new UserRequest([
            'balance' => 'a',
        ]));

        $this->assertHasErrors('balance', new UserRequest([
            'balance' => -1,
        ]));

        $this->assertNotHaveErrors('balance', new UserRequest([
            'balance' => 1,
        ]));
    }

    public function testRotationsIsRequiredAndIsArrayAndHasMinAndIsInValidValues(): void
    {
        $this->assertHasErrors('rotations', new UserRequest([
            'rotations' => null,
        ]));

        $this->assertHasErrors('rotations', new UserRequest([
            'rotations' => 'string',
        ]));

        $this->assertHasErrors('rotations', new UserRequest([
            'rotations' => [],
        ]));

        // TODO: this should be invalid since no rotation exists with "-1" id
//        $this->assertHasErrors('rotations', new UserRequest([
//            'rotations' => [
//                -1
//            ],
//            'role_id' => $role->id,
//        ]));

        $rotation = Rotation::factory()->create([
            'name' => 'Rotation 1',
        ]);

        $this->assertNotHaveErrors('rotations', new UserRequest([
            'rotations' => [
                $rotation->id,
            ],
        ]));
    }

    public function testRoleIsRequiredAndInValidValues(): void
    {
        $this->assertHasErrors('role_id', new UserRequest([
            'role_id' => '',
        ]));

        $this->assertHasErrors('role_id', new UserRequest([
            'role_id' => 'string',
        ]));

        $this->assertHasErrors('role_id', new UserRequest([
            'role_id' => -1,
        ]));

        // TODO: this throws an error even though the role id is valid
//        $role = Role::factory()->create([
//            'name' => 'cashier',
//            'order' => 2,
//            'staff' => true,
//            'superuser' => false,
//        ]);

//        $this->assertNotHaveErrors('role_id', new UserRequest([
//            'role_id' => $role->id,
//        ]));
    }

    public function testPasswordIsRequiredUnderCertainConditionsAndHasMinAndIsConfirmed(): void
    {
        $staff_role = Role::factory()->create([
            'name' => 'cashier',
            'order' => 2,
            'staff' => true,
            'superuser' => false,
        ]);

        $normal_role = Role::factory()->create([
            'name' => 'camper',
            'order' => 3,
            'staff' => false,
            'superuser' => false,
        ]);

        $this->assertHasErrors('password', new UserRequest([
            'password' => '',
            'role_id' => $staff_role->id,
        ]));

        $this->assertNotHaveErrors('password', new UserRequest([
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $staff_role->id,
        ]));

        $this->assertNotHaveErrors('password', new UserRequest([
            'password' => '',
            'role_id' => $normal_role->id,
        ]));

        $this->assertHasErrors('password', new UserRequest([
            'password' => '123',
        ]));

        $this->assertHasErrors('password', new UserRequest([
            'password' => 'password',
        ]));

        $this->assertHasErrors('password', new UserRequest([
            'password' => 'password',
            'password_confirmation' => 'a',
        ]));

        $this->assertNotHaveErrors('password', new UserRequest([
            'password' => 'password',
            'password_confirmation' => 'password',
        ]));
    }
}
