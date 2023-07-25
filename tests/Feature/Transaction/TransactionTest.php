<?php

namespace Tests\Feature\Transaction;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function testTotalPriceCastedToMoneyObject(): void
    {
    }

    public function testHasPurchaser(): void
    {
    }

    public function testHasCashier(): void
    {
    }

    public function testHasRotation(): void
    {
    }

    public function testHasProducts(): void
    {
    }

    public function testGetReturnedTotalIsFullPriceIfFullyReturned(): void
    {
    }

    public function testGetReturnedTotalIsZeroIfNotReturned(): void
    {
    }

    public function testGetReturnedTotalIsPartialPriceIfPartiallyReturned(): void
    {
    }

    public function testIsReturnedIsTrueIfFullyReturned(): void
    {
    }

    public function testGetReturnStatusIsFullyReturnedIfReturned(): void
    {
    }

    public function testGetReturnStatusIsNotReturnedIfNotReturned(): void
    {
    }

    public function testGetReturnStatusIsPartiallyReturnedIfPartiallyReturned(): void
    {
    }
}
