<?php

namespace Tests\Unit;

use Tests\TestCase;
use Cknow\Money\Money;
use App\Models\Settings;
use App\Helpers\TaxHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxHelperTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
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
        $price = Money::parse(5_00);
        $quantity = 1;
        $pst = true;

        // $5 plus 12% = $5.60

        $this->assertEquals(Money::parse(5_60), TaxHelper::calculateFor($price, $quantity, $pst));

        $price = Money::parse(5_25);
        $quantity = 3;
        $pst = false;

        // $15.75 plus 5% = $16.54

        $this->assertEquals(Money::parse(16_54), TaxHelper::calculateFor($price, $quantity, $pst));
    }

    public function testCanCalculateTaxWithCustomRates(): void
    {
        $price = Money::parse(5_00);
        $quantity = 1;
        $pst = true;
        $rates = [
            'gst' => 10,
            'pst' => 15,
        ];

        // $5 plus 25% = $6.25

        $this->assertEquals(Money::parse(6_25), TaxHelper::calculateFor($price, $quantity, $pst, $rates));

        $price = Money::parse(5_25);
        $quantity = 3;
        $pst = false;
        $rates = [
            'gst' => 12,
            'pst' => 15,
        ];

        // $15.75 plus 12% = $17.64

        $this->assertEquals(Money::parse(17_64), TaxHelper::calculateFor($price, $quantity, $pst, $rates));

        $price = Money::parse(5_25);
        $quantity = 3;
        $pst = true;
        $rates = [
            'gst' => 12,
            'pst' => 11,
        ];

        // $15.75 plus 23% = $19.37

        $this->assertEquals(Money::parse(19_37), TaxHelper::calculateFor($price, $quantity, $pst, $rates));
    }

    public function testCanCalculateTaxForTransactionProduct(): void
    {
        // TODO
    }
}
