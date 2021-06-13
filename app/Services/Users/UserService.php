<?php

namespace App\Services\Users;

use App\Models\User;

trait UserService
{
    protected User $_user;

    final public function getUser(): User
    {
        return $this->_user;
    }
}