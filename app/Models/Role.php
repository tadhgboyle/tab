<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'order',
        'staff',
        'superuser',
        'permissions',
    ];

    protected $casts = [
        'superuser' => 'boolean',
        'order' => 'integer',
        'staff' => 'boolean',
        'permissions' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get a set of all Roles which this Role has permission to interact with.
     * If $compare is provided, this will be limited to Roles which the currently logged in user's Role can also interact with as well.
     * This is so that when users delete old Roles, they cannot promote users in the old Role to a Role higher than their current role.
     *
     * @param ?Role $compare If provided, Roles will only be added if:
     * - They are not this Role
     * - This Role is staff OR (this Role is not staff AND the other Role is not staff)
     * - And finally that the `$compare` Role can interact with it
     *
     * @return Collection<int, Role> Roles available for this Role to manage.
     */
    public function getRolesAvailable(?Role $compare = null): Collection
    {
        $return = new Collection();
        $roles = Role::query()->orderByDesc('order')->get();
        foreach ($roles as $role) {
            if (!$compare) {
                if ($this->canInteract($role)) {
                    $return->add($role);
                }
                continue;
            }

            if ($this->id === $role->id) {
                continue;
            }

            if ($this->staff || !$role->staff) {
                if ($compare->canInteract($role)) {
                    $return->add($role);
                }
            }
        }

        return $return;
    }

    /**
     * Determine if this Role should be able to interact with $subject Role.
     * Comparison rules:
     * - All non-staff Roles cannot interact with any other Roles
     * - Superuser Roles can interact with all roles
     * - Non-superuser Roles cannot interact with Superuser roles
     * - If this is a staff role and the $subject is not, allow it
     * - If none of the above applies, it is then determined by if this Role has a lower order (lower = better) than the $subject Role.
     *
     * @param Role $subject Role to examine if this Role should interact with.
     *
     * @return bool Whether this Role can interact with $subject Role.
     */
    public function canInteract(Role $subject): bool
    {
        if (!$this->staff) {
            return false;
        }

        if ($this->superuser) {
            return true;
        }

        if ($subject->superuser) {
            return false;
        }

        if (!$subject->staff) {
            return true;
        }

        return $this->order < $subject->order;
    }

    /**
     * Determine if this Role has supplied permission(s).
     * If an array is supplied, this will only return true if this Role has *all* the permissions.
     * Will always return true if this Role is a superuser.
     *
     * @param string|array $permissions A string or array of permission nodes to check.
     *
     * @return bool Whether this Role has these permissions or not.
     */
    public function hasPermission(string|array $permissions): bool
    {
        if (!$this->staff) {
            return false;
        }

        if ($this->superuser) {
            return true;
        }

        foreach (Arr::wrap($permissions) as $permission) {
            if (!in_array($permission, $this->permissions, true)) {
                return false;
            }
        }

        return true;
    }
}
