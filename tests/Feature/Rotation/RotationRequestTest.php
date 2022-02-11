<?php

namespace Tests\Feature\Rotation;

use App\Models\Rotation;
use Tests\FormRequestTestCase;
use App\Http\Requests\RotationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RotationRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testNameIsRequiredAndHasMinAndUnique(): void
    {
        $this->assertHasErrors('name', new RotationRequest([
            'name' => null,
        ]));

        $this->assertHasErrors('name', new RotationRequest([
            'name' => 'a',
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

    public function testStartIsRequiredAndIsDateAndIsNotInThePast(): void
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

        $this->assertHasErrors('start', new RotationRequest([
            'start' => now()->subDay(),
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
}
