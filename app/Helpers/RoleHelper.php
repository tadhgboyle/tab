<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleHelper extends Helper
{
    private Collection $_roles;
    private Collection $_staff_roles;

    public function getRoles(string $order = 'DESC'): object
    {
        if (!isset($this->_roles)) {
            $this->_roles = Role::where('deleted', false)->orderBy('order', $order)->get();
        }

        return $this->_roles;
    }

    public function getStaffRoles(): Collection
    {
        if (!isset($this->_staff_roles)) {
            $this->_staff_roles = Role::select('id', 'name')->orderBy('order', 'ASC')->where([['staff', true], ['deleted', false]])->get();
        }

        return $this->_staff_roles;
    }
}
