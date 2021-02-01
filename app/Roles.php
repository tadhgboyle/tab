<?php

namespace App;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Roles extends Model implements CastsAttributes
{

    use QueryCacheable;

    protected $cacheFor = 180;

    protected $fillable = ['order'];

    public function get($model, string $key, $value, array $attributes)
    {
        return Roles::find($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $this->id;
    }

    public static function getRoles(string $order = 'DESC'): object
    {
        return Roles::orderBy('order', $order)->get();
    }

    public static function getStaffRoles(): array
    {
        return Roles::select('id', 'name')->orderBy('order', 'ASC')->where('staff', true)->get()->toArray();
    }

    public function getRolesAvailable(): array
    {
        $roles = array();
        foreach (self::getRoles() as $role) {
            if ($this->canInteract($role)) {
                $roles[] = $role;
            }
        }

        return $roles;
    }

    public function canInteract(Roles $subject): bool
    {
        if ($this->superuser) {
            return true;
        } else if ($subject->superuser) {
            return false;
        }
        
        return $this->order < $subject->order;
    }

    private function getPermissions(): array
    {
        return json_decode($this->permissions, true);
    }

    public function hasPermission($permissions): bool
    {
        if ($this->superuser) {
            return true;
        }

        if (!is_array($permissions)) {
            return in_array($permissions, $this->getPermissions());
        } else {
            foreach ($permissions as $permission) {
                if (!in_array($permission, $this->getPermissions())) {
                    return false;
                }
            }
            
            return true;
        }
    }
}
