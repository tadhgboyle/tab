<?php

namespace Tests\Unit\Admin\Rotation;

use App\Models\Rotation;
use Tests\FormRequestTestCase;
use App\Http\Requests\RotationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RotationRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testNameIsRequiredAndUnique(): void
    {
        $this->assertHasErrors('name', new RotationRequest([
            'name' => null,
        ]));

        $this->assertNotHaveErrors('name', new RotationRequest([
            'name' => 'valid',
        ]));

        $rotation = Rotation::factory()->create([
            'name' => 'Rotation',
        ]);

        $this->assertHasErrors('name', new RotationRequest([
            'name' => $rotation->name,
        ]));

        $this->assertNotHaveErrors('name', new RotationRequest([
            'name' => $rotation->name,
            'rotation_id' => $rotation->id,
        ]));
    }

    public function testStartIsRequiredAndIsDate(): void
    {
        $this->assertHasErrors('start', new RotationRequest([
            'start' => null,
        ]));

        $this->assertHasErrors('start', new RotationRequest([
            'start' => 'a',
        ]));

        $this->assertNotHaveErrors('start', new RotationRequest([
            'start' => now(),
        ]));
    }

    public function testEndIsRequiredAndIsDateAndIsAfterTheStart(): void
    {
        $this->assertHasErrors('end', new RotationRequest([
            'end' => null,
        ]));

        $this->assertHasErrors('end', new RotationRequest([
            'end' => 'a',
        ]));

        $this->assertNotHaveErrors('end', new RotationRequest([
            'end' => now(),
        ]));

        $this->assertHasErrors('end', new RotationRequest([
            'start' => now(),
            'end' => now()->subDay(),
        ]));

        $this->assertNotHaveErrors('end', new RotationRequest([
            'start' => now(),
            'end' => now()->addDay(),
        ]));
    }

    public function testStartAndEndCannotOverlapExistingRotation(): void
    {
        Rotation::factory()->create([
            'name' => 'Rotation',
            'start' => now()->subDay(),
            'end' => now()->addDay(),
        ]);

        $this->assertHasErrors('start_or_end', new RotationRequest([
            'start' => now()->subDays(2),
            'end' => now()->addDays(2),
        ]));
    }

    public function testOverlapErrorNotAddedIfEditingRotation(): void
    {
        $rotation = Rotation::factory()->create([
            'name' => 'Rotation',
            'start' => now()->subDay(),
            'end' => now()->addDay(),
        ]);

        $this->assertNotHaveErrors('start_or_end', new RotationRequest([
            'start' => now()->subDays(2),
            'end' => now()->addDays(2),
            'rotation_id' => $rotation->id,
        ]));
    }
}
