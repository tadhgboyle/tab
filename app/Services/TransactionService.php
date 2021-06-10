<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;

abstract class TransactionService
{
    protected int $_result;
    protected string $_message;
    protected Transaction $_transaction;

    final public function getResult(): int
    {
        return $this->_result;
    }

    final public function getMessage(): string
    {
        return $this->_message;
    }

    final public function getTransaction(): Transaction
    {
        return $this->_transaction;
    }

    abstract public function redirect(): RedirectResponse;
}
