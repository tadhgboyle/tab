<?php

namespace Tests\Unit;

use App\Helpers\TaxHelper;
use App\Models\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxHelperTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();

        Settings::factory()->createMany([
            [
                'setting' => 'gst',
                'value' => '5.00',
            ],
            [
                'setting' => 'pst',
                'value' => '7.00',
            ],
        ]);
    }

    public function testCanCalculateTaxWithDefaultRates(): void
    {
        $price = 5.00;
        $quantity = 1;
        $pst = true;

        // $5 plus 12% = $5.60

        $this->assertEquals(5.60, TaxHelper::calculateFor($price, $quantity, $pst));

        $price = 5.25;
        $quantity = 3;
        $pst = false;

        // $15.75 plus 5% = $16.54

        $this->assertEquals(16.54, TaxHelper::calculateFor($price, $quantity, $pst));
    }

    public function testCanCalculateTaxWithCustomRates(): void
    {
        $price = 5.00;
        $quantity = 1;
        $pst = true;
        $rates = [
            'gst' => 10,
            'pst' => 15,
        ];

        // $5 plus 25% = $6.25

        $this->assertEquals(6.25, TaxHelper::calculateFor($price, $quantity, $pst, $rates));

        $price = 5.25;
        $quantity = 3;
        $pst = false;
        $rates = [
            'gst' => 12,
            'pst' => 15,
        ];

        // $15.75 plus 12% = $17.64

        $this->assertEquals(17.64, TaxHelper::calculateFor($price, $quantity, $pst, $rates));

        $price = 5.25;
        $quantity = 3;
        $pst = true;
        $rates = [
            'gst' => 12,
            'pst' => 11,
        ];

        // $15.75 plus 23% = $19.37

        $this->assertEquals(19.37, TaxHelper::calculateFor($price, $quantity, $pst, $rates));
    }
}
