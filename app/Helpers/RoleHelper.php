<?php

namespace App\Helpers;

use App\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleHelper
{
    private static RoleHelper $_instance;

    private Collection $_roles;
    private array $_staff_roles;

    public static function getInstance(): RoleHelper
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new RoleHelper();
        }

        return self::$_instance;
    }

    public function getRoles(string $order = 'DESC'): object
    {
        if (!isset($this->_roles)) {
            $this->_roles = Role::where('deleted', false)->orderBy('order', $order)->get();
        }

        return $this->_roles;
    }

    public function getStaffRoles(): array
    {
        if (!isset($this->_staff_roles)) {
            $this->_staff_roles = Role::select('id', 'name')->orderBy('order', 'ASC')->where([['staff', true], ['deleted', false]])->get()->toArray();
        }

        return $this->_staff_roles;
    }
}
