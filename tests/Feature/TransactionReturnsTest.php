<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// TODO after rewriting transaction handling
class TransactionReturnsTest extends TestCase
{
    use RefreshDatabase;

    public function testUserBalanceUpdatedAfterItemReturn()
    {
        $this->assertTrue(true);
    }

    public function testUserBalanceUpdatedAfterTransactionReturn()
    {
        $this->assertTrue(true);
    }

    public function testCanReturnPartiallyReturnedItemInTransaction()
    {
        $this->assertTrue(true);
    }

    public function testCannotReturnFullyReturnedTransaction()
    {
        $this->assertTrue(true);
    }

    public function testCannotReturnFullyReturnedItemInTransaction()
    {
        $this->assertTrue(true);
    }
}
