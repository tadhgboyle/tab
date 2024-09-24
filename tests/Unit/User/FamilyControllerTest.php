<?php

namespace Tests\Unit\User;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Family;
use App\Enums\FamilyMemberRole;
use App\Http\Middleware\RequiresOwnFamily;
use App\Http\Middleware\RequiresFamilyAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FamilyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $_superuser;

    private Family $_family;

    public function setUp(): void
    {
        parent::setUp();

        $this->_superuser = User::factory()->create([
            'full_name' => 'Superuser User',
            'role_id' => Role::factory()->create()->id,
        ]);

        $this->_family = Family::factory()->create([
            'name' => 'Test Family',
        ]);

        $this->_family->members()->create([
            'user_id' => $this->_superuser->id,
            'role' => FamilyMemberRole::Admin,
        ]);

        $this->actingAs($this->_superuser);
    }

    public function testCanViewShowPage()
    {
        $this->expectMiddleware([RequiresOwnFamily::class]);

        $this
            ->get(route('family_view', $this->_family))
            ->assertOk()
            ->assertViewIs('pages.user.family.view')
            ->assertViewHas('family', $this->_family);
    }

    public function testCanDownloadPdf()
    {
        $this->expectMiddleware([RequiresOwnFamily::class, RequiresFamilyAdmin::class]);

        $this
            ->get(route('family_pdf', $this->_family))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename=family-' . $this->_family->id . '-' . now()->timestamp . '.pdf');
    }
}
