<?php

namespace Tests\Unit\Admin\Family;

use App\Models\Family;
use Tests\FormRequestTestCase;
use App\Http\Requests\FamilyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FamilyRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testNameIsRequiredAndIsUnique(): void
    {
        $this->assertHasErrors('name', new FamilyRequest([
            'name' => '',
        ]));

        $this->assertNotHaveErrors('name', new FamilyRequest([
            'name' => 'My Family',
        ]));

        $family = Family::factory()->create([
            'name' => 'My Family',
        ]);

        $this->assertHasErrors('name', new FamilyRequest([
            'name' => $family->name,
        ]));

        $this->assertNotHaveErrors('name', new FamilyRequest([
            'name' => $family->name,
            'family_id' => $family->id,
        ]));
    }
}
