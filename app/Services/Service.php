<?php

namespace App\Services;

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
}
