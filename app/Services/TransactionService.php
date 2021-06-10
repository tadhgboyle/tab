<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;

abstract class TransactionService
{
    protected int $_result;
    protected string $_message;
    protected Transaction $_transaction;

    public final function getResult(): int
    {
        return $this->_result;
    }

    public final function getMessage(): string
    {
        return $this->_message;
    }

    public final function getTransaction(): Transaction
    {
        return $this->_transaction;
    }

    abstract function redirect(): RedirectResponse;
}