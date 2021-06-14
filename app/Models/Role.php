<?php

namespace App\Models;

use App\Helpers\RoleHelper;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use QueryCacheable;
    use HasFactory;

    protected $cacheFor = 180;

    protected $fillable = [
        'order', // used to drag and drop roles in Settings page
        'deleted',
    ];

    protected $casts = [
        'name' => 'string',
        'superuser' => 'boolean', // if this is true, this group can do anything and edit any group
        'order' => 'integer', // heierarchy system. higher order = higher priority
        'staff' => 'boolean', // determine if they should ever have a password to login with
        'permissions' => 'array', // decode json to an array automatically
        'deleted' => 'boolean',
    ];

    /**
     * Get a set of all Roles which this Role has permission to interact with.
     * If $compare is provided, this will be limited to Roles which the currently logged in user's Role can also interact with as well.
     * This is so that when users delete old Roles, they cannot promote users in the old Role to a Role higher than their current role.
     * @see canInteract
     * 
     * @param Role $compare If provided, Roles will only be added if:
     * - They are not this Role
     * - This Role is staff OR (this Role is not staff AND the other Role is not staff)
     * - And finally that the `$compare` Role can interact with it
     * @return Collection Roles available for this Role to manage.
     */
    public function getRolesAvailable(?Role $compare = null): Collection
    {
        $return = new Collection();
        $roles = RoleHelper::getInstance()->getRoles();
        foreach ($roles as $role) {
            if ($compare) {
                if ($this->id == $role->id) {
                    continue;
                }
                if ($this->staff || (!$this->staff && !$role->staff)) {
                    if ($compare->canInteract($role)) {
                        $return->add($role);
                    }
                }
                continue;
            }

            if ($this->canInteract($role)) {
                $return->add($role);
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
     * - If none of the above applies, it is then determined by if this Role has a higher order (hierarchy) than the $subject Role.
     * 
     * @param Role $subject Role to examine if this Role should interact with.
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

        return $this->order > $subject->order;
    }

    /**
     * Determine if this Role has supplied permission(s).
     * If an array is supplied, this will only return true if this Role has *all* the permissions.
     * Will always return true if this Role is a superuser.
     * 
     * @param string|array $permissions A string or array of permission nodes to check.
     * @return bool Whether this Role has these permissions or not.
     */
    public function hasPermission(string|array $permissions): bool
    {
        if ($this->superuser) {
            return true;
        }

        foreach ((array) $permissions as $permission) {
            if (!in_array($permission, $this->permissions)) {
                return false;
            }
        }

        return true;
    }
}
