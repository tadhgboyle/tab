<?php

namespace Tests\Unit\User;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Family;
use Cknow\Money\Money;
use App\Models\Category;
use App\Models\FamilyMember;
use App\Enums\FamilyMemberRole;
use App\Enums\UserLimitDuration;
use App\Http\Middleware\RequiresOwnFamily;
use App\Http\Middleware\RequiresFamilyAdmin;
use App\Http\Middleware\RequiresFamilyAdminOrSelf;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FamilyMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $_superuser;

    private Family $_family;

    private FamilyMember $_familyMember;
    private FamilyMember $_familyMember2;

    public function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create();

        $this->_superuser = User::factory()->create([
            'full_name' => 'Superuser User',
            'role_id' => $role->id,
        ]);

        $this->_family = Family::factory()->create([
            'name' => 'Test Family',
        ]);

        $this->_familyMember = $this->_family->members()->create([
            'user_id' => $this->_superuser->id,
            'role' => FamilyMemberRole::Admin,
        ]);

        $user2 = User::factory()->create([
            'full_name' => 'User 2',
            'role_id' => $role->id,
        ]);

        $this->_familyMember2 = $this->_family->members()->create([
            'user_id' => $user2->id,
            'role' => FamilyMemberRole::Member,
        ]);

        $this->actingAs($this->_superuser);

        $this->expectMiddleware([RequiresOwnFamily::class]);
    }

    public function testCanViewShowPage()
    {
        $this->expectMiddleware([RequiresFamilyAdminOrSelf::class]);

        $this
            ->get(route('families_member_view', [$this->_family, $this->_familyMember]))
            ->assertOk()
            ->assertViewIs('pages.user.family.members.view')
            ->assertViewHas('user', $this->_superuser)
            ->assertViewHas('familyMember', $this->_familyMember);
    }

    public function testCanViewEditPage()
    {
        $this->expectMiddleware([RequiresFamilyAdmin::class]);

        $this
            ->get(route('families_member_edit', [$this->_family, $this->_familyMember]))
            ->assertOk()
            ->assertViewIs('pages.user.family.members.edit')
            ->assertViewHas('user', $this->_superuser)
            ->assertViewHas('familyMember', $this->_familyMember)
            ->assertViewHas('categories');
    }

    public function testCannotEditOwnRole()
    {
        $this->expectMiddleware([RequiresFamilyAdmin::class]);

        $this
            ->put(route('families_member_update', [$this->_family, $this->_familyMember]), [
                'role' => FamilyMemberRole::Member->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'You cannot update your own family role.');
    }

    public function testCannotUpdateNegativeLimit()
    {
        $category = Category::factory()->create();

        $this->expectMiddleware([RequiresFamilyAdmin::class]);

        $this
            ->put(route('families_member_update', [$this->_family, $this->_familyMember]), [
                'role' => FamilyMemberRole::Admin->value,
                'limits' => [
                    $category->id => '-2.00',
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Limit cannot be negative.');
    }

    public function testCanUpdateRole()
    {
        $this->expectMiddleware([RequiresFamilyAdmin::class]);

        $this->assertEquals(FamilyMemberRole::Member, $this->_familyMember2->role);

        $this
            ->put(route('families_member_update', [$this->_family, $this->_familyMember2]), [
                'role' => FamilyMemberRole::Admin->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Family member updated.');

        $this->assertEquals(FamilyMemberRole::Admin, $this->_familyMember2->refresh()->role);
    }

    public function testCanUpdateUserLimits()
    {
        $category = Category::factory()->create();

        $this->expectMiddleware([RequiresFamilyAdmin::class]);

        $this
            ->put(route('families_member_update', [$this->_family, $this->_familyMember]), [
                'role' => FamilyMemberRole::Admin->value,
                'limits' => [
                    $category->id => '10.00',
                ],
                'durations' => [
                    $category->id => UserLimitDuration::Weekly->value,
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Family member updated.');

        $this->assertEquals(Money::parse(10_00), $this->_superuser->limitFor($category)->limit);
        $this->assertEquals(UserLimitDuration::Weekly, $this->_superuser->limitFor($category)->duration);
    }

    public function testCanDownloadPdf()
    {
        $this->expectMiddleware([RequiresFamilyAdminOrSelf::class]);

        $this
            ->get(route('family_member_pdf', [$this->_family, $this->_familyMember]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename=user-' . $this->_superuser->id . '-' . now()->timestamp . '.pdf');
    }
}
