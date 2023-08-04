<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;

abstract class Service
{
    protected string $_result;
    protected string $_message;

    final public function getResult(): string
    {
        return $this->_result;
    }

    final public function getMessage(): string
    {
        return $this->_message;
    }

    abstract public function redirect(): RedirectResponse;
}
