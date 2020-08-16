<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Roles extends Model
{

    use QueryCacheable;

    protected $cacheFor = 180;

    public static function getRoles(string $order): object
    {
        return Roles::orderBy('order', $order)->get();
    }

    public static function getStaffRoles():array
    {
        return Roles::select('id', 'name')->orderBy('order', 'ASC')->where('staff', true)->get()->toArray();
    }

    public static function getRolesAvailable(int $caller):array
    {
        $roles = array();
        foreach (Roles::getRoles('DESC') as $role) {
            if (self::canInteract($caller, $role->id)) $roles[] = $role;
        }
        return $roles;
    }

    public static function getPermissions(int $role): array
    {
        return json_decode(Roles::where('id', $role)->pluck('permissions')->first(), true);
    }

    public static function idToName(int $id): string
    {
        return Roles::where('id', $id)->pluck('name')->first();
    }

    public static function canInteract(int $caller, int $subject): bool
    {
        if (Roles::where('id', $caller)->pluck('superuser')->first()) return true;
        $caller_order = Roles::where('id', $caller)->pluck('order')->first();
        $subject_order = Roles::where('id', $subject)->pluck('order')->first();
        return $caller_order < $subject_order;
    }

    public static function hasPermission(int $role, string $permission): bool
    {
        if (Roles::where('id', $role)->pluck('superuser')->first()) return true;
        return in_array($permission, self::getPermissions($role));
    }
}
