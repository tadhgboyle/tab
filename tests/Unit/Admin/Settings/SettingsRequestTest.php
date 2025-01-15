<?php

namespace Tests\Unit\Admin\Settings;

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

    public function testOrderPrefixIsNullableAndIsString(): void
    {
        $this->assertNotHaveErrors('order_prefix', new SettingsRequest([
            'order_prefix' => null,
        ]));

        $this->assertNotHaveErrors('order_prefix', new SettingsRequest([
            'order_prefix' => 'string',
        ]));
    }

    public function testOrderSuffixIsNullableAndIsString(): void
    {
        $this->assertNotHaveErrors('order_suffix', new SettingsRequest([
            'order_suffix' => null,
        ]));

        $this->assertNotHaveErrors('order_suffix', new SettingsRequest([
            'order_suffix' => 'string',
        ]));
    }
}
