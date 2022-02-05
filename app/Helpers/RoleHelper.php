<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleHelper extends Helper
{
    private Collection $roles;
    private Collection $staffRoles;

    public function getRoles(string $order = 'DESC'): Collection
    {
        return $this->roles ??= Role::query()->orderBy('order', $order)->get();
    }

    public function getStaffRoles(): Collection
    {
        return $this->staffRoles ??= $this->getRoles()->filter(static function (Role $role): bool {
            return $role->staff;
        });
    }

    public function isStaffRole(int $roleId): bool
    {
        return $this->getStaffRoles()->pluck('id')->contains($roleId);
    }
}
