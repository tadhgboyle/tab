<?php

namespace Tests\Feature\Role;

use App\Models\Role;
use Tests\FormRequestTestCase;
use App\Http\Requests\RoleRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testNameIsRequiredAndHasMinAndIsUnique(): void
    {
        $this->assertHasErrors('name', new RoleRequest([
            'name' => null,
        ]));

        $this->assertHasErrors('name', new RoleRequest([
            'name' => '1',
        ]));

        $this->assertNotHaveErrors('name', new RoleRequest([
            'name' => 'Valid',
        ]));

        $role = Role::factory()->create([
            'name' => 'Superadmin'
        ]);

        $this->assertHasErrors('name', new RoleRequest([
            'name' => 'Superadmin',
        ]));

        $this->assertNotHaveErrors('name', new RoleRequest([
            'name' => 'Superadmin',
            'role_id' => $role->id,
        ]));
    }

    public function testOrderIsRequiredAndNumericAndGreaterThanZeroAndUnique(): void
    {
        $this->assertHasErrors('order', new RoleRequest([
            'order' => null,
        ]));

        $this->assertHasErrors('order', new RoleRequest([
            'order' => 'string',
        ]));

        $this->assertHasErrors('order', new RoleRequest([
            'order' => 0,
        ]));

        $role = Role::factory()->create([
            'order' => 1
        ]);

        $this->assertHasErrors('order', new RoleRequest([
            'order' => 1,
        ]));

        $this->assertNotHaveErrors('order', new RoleRequest([
            'order' => 1,
            'role_id' => $role->id,
        ]));
    }
}
