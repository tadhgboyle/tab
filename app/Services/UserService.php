<?php

namespace App\Services;

use App\Models\User;

trait UserService
{
    protected User $_user;

    final public function getUser(): User
    {
        return $this->_user;
    }
}