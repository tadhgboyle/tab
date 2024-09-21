<?php

namespace Tests\Unit\Admin\Family;
use App\Helpers\Permission;
use App\Models\Family;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $_superuser;

    public function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create();
        $this->_superuser = User::factory()->create([
            'full_name' => 'Superuser User',
            'role_id' => $role->id,
        ]);

        $this->actingAs($this->_superuser);
    }

    public function testCanViewListPage(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_LIST]);

        $this
            ->get(route('families_list'))
            ->assertOk()
            ->assertViewIs('pages.admin.families.list');
    }

    public function testCanViewShowPage(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_VIEW]);

        $family = Family::factory()->create();

        $this
            ->get(route('families_view', $family))
            ->assertOk()
            ->assertViewIs('pages.admin.families.view')
            ->assertViewHas('family', $family);
    }

    public function testCanViewCreatePage(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_MANAGE]);

        $this
            ->get(route('families_create'))
            ->assertOk()
            ->assertViewIs('pages.admin.families.form');
    }

    public function testCanCreateFamily(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_MANAGE]);

        $this
            ->post(route('families_store'), [
                'name' => 'My Family',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'My Family family created.');

        $family = Family::latest()->first();

        $this->assertEquals('My Family', $family->name);
    }

    public function testCanViewEditPage(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_MANAGE]);

        $family = Family::factory()->create();

        $this
            ->get(route('families_edit', $family))
            ->assertOk()
            ->assertViewIs('pages.admin.families.form')
            ->assertViewHas('family', $family);
    }

    public function testCanUpdateFamily(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_MANAGE]);

        $family = Family::factory()->create();

        $this
            ->put(route('families_update', $family), [
                'name' => 'My Updated Family',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Family updated.');

        $family->refresh();

        $this->assertEquals('My Updated Family', $family->name);
    }

    public function testCanDownloadPdf(): void
    {
        $this->expectPermissionChecks([Permission::FAMILIES, Permission::FAMILIES_VIEW]);

        $family = Family::factory()->create();

        $this
            ->get(route('families_pdf', $family))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', "inline; filename=family-{$family->id}-" . now()->timestamp . '.pdf');
    }
}