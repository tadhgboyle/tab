<?php

namespace App\Services\Transactions;

use App\Models\Transaction;

trait TransactionService
{
    protected Transaction $_transaction;

    final public function getTransaction(): Transaction
    {
        return $this->_transaction;
    }
}
