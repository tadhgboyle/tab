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

    public function testHasTransactions(): void
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

    public function testHasPermission(): void
    {
        $this->markTestIncomplete();
    }

    public function testFindSpentCalculatesFromTransactionsAndActivityRegistrations(): void
    {
        $this->markTestIncomplete();
    }

    public function testFindReturnedCalculatesFromTransactionTotalPriceIfFullyReturned(): void
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
}
