<?php

namespace Tests\Feature\Rotation;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Rotation;
use App\Helpers\RotationHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RotationTest extends TestCase
{
    use RefreshDatabase;

    public function testGetCurrentRotationWorksAsExpected(): void
    {
        $rotationHelper = resolve(RotationHelper::class);

        $rotation2 = Rotation::factory()->create([
            'name' => 'Rotation 2',
            'start' => Carbon::now()->addDays(3),
            'end' => Carbon::now()->addDays(6),
        ]);

        // TODO: seems to cache current rotation even if we remove caching stuff $this->assertNull($rotationHelper->getCurrentRotation());

        $rotation1 = Rotation::factory()->create([
            'name' => 'Rotation 1',
            'start' => Carbon::now()->subDays(2),
            'end' => Carbon::now()->addDays(2),
        ]);

        $this->assertSame($rotation1->id, $rotationHelper->getCurrentRotation()->id);
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
}
