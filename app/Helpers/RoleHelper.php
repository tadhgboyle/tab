<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleHelper
{
    private Collection $roles;
    private Collection $staffRoles;

    /**
     * @param string $order Direction of sorting
     *
     * @return Collection<int, Role>
     */
    public function getRoles(string $order = 'DESC', bool $with_user_count = false): Collection
    {
        return $this->roles ??= Role::query()
            ->orderBy('order', $order)
            ->when($with_user_count, static function ($query) {
                $query->withCount('users');
            })
            ->get();
    }

    /** @return Collection<int, Role> */
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
