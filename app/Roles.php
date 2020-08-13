<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Roles extends Model
{

    protected $primaryKey = 'role_id';

    use QueryCacheable;

    protected $cacheFor = 180;

    public static function getRoles(): object
    {
        return Roles::orderBy('order', 'ASC')->get();
    }

    public static function idToName(int $id): string
    {
        return Roles::find($id)->pluck('name')->first();
    }

    public static function canInteract(int $caller, int $subject): bool
    {
        if (Roles::where('role_id', $caller)->pluck('superuser')->first()) return true;
        $caller_order = Roles::where('role_id', $caller)->pluck('order')->first();
        $subject_order = Roles::where('role_id', $subject)->pluck('order')->first();
        return $caller_order < $subject_order;
    }

    public static function hasPermission(int $role, string $permission): bool
    {
        if (Roles::where('role_id', $role)->pluck('superuser')->first()) return true;
        return in_array($permission, json_decode(Roles::where('role_id', $role)->pluck('permissions')->first(), true));
    }
}
