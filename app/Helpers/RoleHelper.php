<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleHelper extends Helper
{
    private Collection $_roles;
    private Collection $_staff_roles;

    public function getRoles(string $order = 'DESC'): Collection
    {
        if (!isset($this->_roles)) {
            $this->_roles = Role::orderBy('order', $order)->get();
        }

        return $this->_roles;
    }

    public function isStaffRole(int $role_id): bool
    {
        foreach ($this->getStaffRoles() as $role) {
            if ($role->id == $role_id) {
                return true;
            }
        }

        return false;
    }

    public function getStaffRoles(): Collection
    {
        if (!isset($this->_staff_roles)) {
            $this->_staff_roles = new Collection();

            foreach ($this->getRoles() as $role) {
                if ($role->staff) {
                    $this->_staff_roles->add($role);
                }
            }
        }

        return $this->_staff_roles;
    }
}
