<?php

namespace Tests\Unit\Rotation;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Rotation;
use App\Helpers\RotationHelper;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RotationHelperTest extends TestCase
{
    use RefreshDatabase;

    private Role $_cashier_role;
    private User $_cashier_user;
    private Role $_superuser_role;
    private User $_superuser_user;

    public function setUp(): void
    {
        parent::setUp();

        $this->_cashier_role = Role::factory()->create([
            'name' => 'Cashier',
            'superuser' => false,
        ]);

        $this->_cashier_user = User::factory()->create([
            'role_id' => $this->_cashier_role->id,
        ]);

        $this->_superuser_role = Role::factory()->create();

        $this->_superuser_user = User::factory()->create([
            'role_id' => $this->_superuser_role->id,
        ]);
    }

    public function testGetCurrentRotationWorksAsExpected(): void
    {
        $rotationHelper = new RotationHelper();

        Rotation::factory()->create([
            'name' => 'Rotation 2',
            'start' => Carbon::now()->addDays(3),
            'end' => Carbon::now()->addDays(6),
        ]);

        $this->assertNull($rotationHelper->getCurrentRotation());

        $rotationHelper = new RotationHelper();

        $rotation = Rotation::factory()->create([
            'name' => 'Rotation 1',
            'start' => Carbon::now()->subDays(2),
            'end' => Carbon::now()->addDays(2),
        ]);

        $this->assertSame($rotation->id, $rotationHelper->getCurrentRotation()->id);
    }

    public function testDoesRotationOverlapWorksAsExpected(): void
    {
        $rotationHelper = resolve(RotationHelper::class);

        Rotation::factory()->create([
            'name' => 'Rotation 1',
            'start' => Carbon::now()->subDays(2),
            'end' => Carbon::now()->addDays(2),
        ]);

        $this->assertTrue($rotationHelper->doesRotationOverlap(Carbon::now(), Carbon::now()->addDay()));
        $this->assertFalse($rotationHelper->doesRotationOverlap(Carbon::now()->addWeek(), Carbon::now()->addWeeks(2)));
    }

    public function testDoesRotationOverlapReturnsFalseIfEndAndStartAreSameTime(): void
    {
        $rotationHelper = resolve(RotationHelper::class);

        $start = Carbon::now()->subDays(2);
        $end = Carbon::now()->addDays(2);
        Rotation::factory()->create([
            'name' => 'Rotation 1',
            'start' => $start,
            'end' => $end,
        ]);

        $new_start = $end->clone();
        $new_end = $new_start->clone()->addDays(2);

        $this->assertFalse($rotationHelper->doesRotationOverlap($new_start, $new_end));
    }

    public function testDoesRotationOverlapIgnoresRotationWhenIgnoreIdPassed(): void
    {
        $rotationHelper = resolve(RotationHelper::class);

        $rotation = Rotation::factory()->create([
            'name' => 'Rotation 1',
            'start' => Carbon::now()->subDays(2),
            'end' => Carbon::now()->addDays(2),
        ]);

        $this->assertFalse($rotationHelper->doesRotationOverlap($rotation->start, $rotation->end, $rotation->id));
    }

    public function testGetStatisticsRotationIdReturnsNullWhenNoCurrentRotationOrExtraPermission(): void
    {
        $this->actingAs($this->_cashier_user);

        $this->assertNull(resolve(RotationHelper::class)->getStatisticsRotationId());
    }

    public function testGetStatisticsRotationIdReturnsCurrentRotationIdWhenNoExtraPermission(): void
    {
        $this->actingAs($this->_cashier_user);

        $rotation = Rotation::factory()->create([
            'name' => 'Rotation 1',
            'start' => Carbon::now()->subDays(2),
            'end' => Carbon::now()->addDays(2),
        ]);

        $this->assertEquals($rotation->id, resolve(RotationHelper::class)->getStatisticsRotationId());
    }

    public function testGetStatisticsRotationIdReturnsCookiedIdWhenExtraPermission(): void
    {
        $this->markTestSkipped('Skipped because we cannot mock the Cookie facade.');

        $cookie_value = random_int(1, 100);

        Cookie::queue('stats_rotation_id', $cookie_value);

        $this->actingAs($this->_superuser_user);

        $this->assertEquals($cookie_value, resolve(RotationHelper::class)->getStatisticsRotationId());
    }

    public function testGetStatisticsRotationIdReturnsAsteriskWhenNoCookieStoredAndHasExtraPermission(): void
    {
        $this->markTestSkipped('Skipped because we cannot mock the Cookie facade.');

        Cookie::queue('stats_rotation_id', null);

        $this->actingAs($this->_superuser_user);

        $this->assertEquals('*', resolve(RotationHelper::class)->getStatisticsRotationId());
    }
}
