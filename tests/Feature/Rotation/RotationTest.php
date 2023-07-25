<?php

namespace Tests\Feature\Rotation;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Rotation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RotationTest extends TestCase
{
    use RefreshDatabase;

    private Rotation $_rotation;
    private User $_user;

    public function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create();
        $this->_user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $this->_rotation = Rotation::factory()->create([
            'name' => 'Test Rotation',
        ]);
    }

    public function testRotationBelongsToManyUsers(): void
    {
        $this->_rotation->users()->attach($this->_user);

        $this->assertDatabaseHas('rotation_user', [
            'rotation_id' => $this->_rotation->id,
            'user_id' => $this->_user->id,
        ]);

        $this->assertCount(1, $this->_rotation->users);
        $this->assertEquals($this->_rotation->users->first()->id, $this->_user->id);
    }

    public function testIsPresentIsTrueIfStatusIsPresent(): void
    {
        $this->_rotation->update([
            'start' => now()->subDay(),
            'end' => now()->addDays(2),
        ]);

        $this->assertTrue($this->_rotation->isPresent());
    }

    public function testGetStatusReturnsPresentIfPresent(): void
    {
        $this->_rotation->update([
            'start' => now()->subDay(),
            'end' => now()->addDays(2),
        ]);

        $this->assertEquals(Rotation::STATUS_PRESENT, $this->_rotation->getStatus());
    }

    public function testGetStatusReturnsFutureIfStartIsInFuture(): void
    {
        $this->_rotation->update([
            'start' => now()->addWeek(),
            'end' => now()->addWeeks(2),
        ]);

        $this->assertEquals(Rotation::STATUS_FUTURE, $this->_rotation->getStatus());
    }

    public function testGetStatusReturnsPastIfEndIsInPast(): void
    {
        $this->_rotation->update([
            'start' => now()->subWeeks(2),
            'end' => now()->subWeek(),
        ]);

        $this->assertEquals(Rotation::STATUS_PAST, $this->_rotation->getStatus());
    }
}
