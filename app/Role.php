<?php

namespace App;

use App\Helpers\RoleHelper;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Role extends Model
{
    
    use QueryCacheable;

    protected $cacheFor = 180;

    protected $fillable = [
        'order', // used to drag and drop roles in Settings page
        'deleted'
    ];

    protected $casts = [
        'name' => 'string',
        'superuser' => 'boolean', // if this is true, this group can do anything and edit any group
        'order' => 'integer', // heierarchy system. higher order = higher priority
        'staff' => 'boolean', // determine if they should ever have a password to login with
        'permissions' => 'array', // decode json to an array automatically
        'deleted' => 'boolean'
    ];

    public function getRolesAvailable(Role $compare = null): array
    {
        // TODO: Refractor
        $return = array();
        $roles = RoleHelper::getInstance()->getRoles();
        foreach ($roles as $role) {
            if ($compare) {
                if ($this->id == $role->id) {
                    continue;
                }
                if ($this->staff || (!$this->staff && !$role->staff)) {
                    if ($compare->canInteract($role)) {
                        $return[] = $role;
                    }
                }
            } else {
                if ($this->canInteract($role)) {
                    $return[] = $role;
                }
            }
        }

        return $return;
    }

    public function canInteract(Role $subject): bool
    {
        if ($this->superuser) {
            return true;
        }

        if ($subject->superuser) {
            return false;
        }

        return $this->order < $subject->order;
    }

    public function hasPermission($permissions): bool
    {
        if ($this->superuser) {
            return true;
        }

        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (in_array($permission, $this->permissions)) {
                    return true;
                }
            }
            return false;
        } else {
            return in_array($permissions, $this->permissions);
        }
    }
}
