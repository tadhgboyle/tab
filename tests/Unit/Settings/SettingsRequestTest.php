<?php

namespace Tests\Unit\Settings;

use Tests\FormRequestTestCase;
use App\Http\Requests\SettingsRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingsRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testGstIsRequiredAndNumeric(): void
    {
        $this->assertHasErrors('gst', new SettingsRequest([
            'gst' => null,
        ]));

        $this->assertHasErrors('gst', new SettingsRequest([
            'gst' => 'string',
        ]));

        $this->assertNotHaveErrors('gst', new SettingsRequest([
            'gst' => 1,
        ]));
    }

    public function testPstIsRequiredAndNumeric(): void
    {
        $this->assertHasErrors('pst', new SettingsRequest([
            'pst' => null,
        ]));

        $this->assertHasErrors('pst', new SettingsRequest([
            'pst' => 'string',
        ]));

        $this->assertNotHaveErrors('pst', new SettingsRequest([
            'pst' => 1,
        ]));
    }
}
