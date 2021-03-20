<?php

namespace App\Helpers;

use App\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleHelper
{

    private static ?RoleHelper $_instance = null;

    private ?Collection $_roles = null;
    private ?array $_staff_roles = null;

    public static function getInstance(): RoleHelper
    {
        if (self::$_instance == null) {
            self::$_instance = new RoleHelper();
        }

        return self::$_instance;
    }

    public function getRoles(string $order = 'DESC'): object
    {
        if ($this->_roles == null) {
            $this->_roles = Role::where('deleted', false)->orderBy('order', $order)->get();
        }

        return $this->_roles;
    }

    public function getStaffRoles(): array
    {
        if ($this->_staff_roles == null) {
            $this->_staff_roles = Role::select('id', 'name')->orderBy('order', 'ASC')->where([['staff', true], ['deleted', false]])->get()->toArray();
        }

        return $this->_staff_roles;
    }
}
