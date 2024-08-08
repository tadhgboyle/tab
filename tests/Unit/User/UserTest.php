<?php

namespace Tests\Unit\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testBalanceIsCastedToMoneyObject(): void
    {
        $this->markTestIncomplete();
    }

    public function testBelongsToARole(): void
    {
        $this->markTestIncomplete();
    }

    public function testHasOrders(): void
    {
        $this->markTestIncomplete();
    }

    public function testHasActivityRegistrations(): void
    {
        $this->markTestIncomplete();
    }

    public function testBelongsToManyRotations(): void
    {
        $this->markTestIncomplete();
    }

    public function testHasPayouts(): void
    {
        $this->markTestIncomplete();
    }

    public function testHasUserLimits(): void
    {
        $this->markTestIncomplete();
    }

    public function testLimitFor(): void
    {
        $this->markTestIncomplete();
    }

    public function testHasPermission(): void
    {
        $this->markTestIncomplete();
    }

    public function testFindSpentCalculatesFromOrdersAndActivityRegistrations(): void
    {
        $this->markTestIncomplete();
    }

    public function testFindReturnedCalculatesFromOrderTotalPriceIfFullyReturned(): void
    {
        $this->markTestIncomplete();
    }

    public function testFindReturnedCalculatesFromProductPriceAndHistoricalTaxesIfPartiallyReturned(): void
    {
        $this->markTestIncomplete();
    }

    public function testFindReturnedCalculatesFromReturnedActivityRegistrations(): void
    {
        $this->markTestIncomplete();
    }

    public function testFindOwingUsesTotalSpentMinusReturnedMinusPayouts(): void
    {
        $this->markTestIncomplete();
    }

    public function testFindPaidOutCalculatesFromPayouts(): void
    {
        $this->markTestIncomplete();
    }
}
