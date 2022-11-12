<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testBalanceIsCastedToMoneyObject(): void
    {

    }

    public function testBelongsToARole(): void
    {

    }

    public function testHasTransactions(): void
    {

    }

    public function testHasActivityRegistrations(): void
    {

    }

    public function testBelongsToManyRotations(): void
    {

    }

    public function testHasPayouts(): void
    {

    }

    public function testHasPermission(): void
    {

    }

    public function testFindSpentCalculatesFromTransactionsAndActivityRegistrations(): void
    {

    }

    public function testFindReturnedCalculatesFromTransactionTotalPriceIfFullyReturned(): void
    {

    }

    public function testFindReturnedCalculatesFromProductPriceAndHistoricalTaxesIfPartiallyReturned(): void
    {

    }

    public function testFindReturnedCalculatesFromReturnedActivityRegistrations(): void
    {

    }

    public function testFindOwingUsesTotalSpentMinusReturnedMinusPayouts(): void
    {

    }
}
