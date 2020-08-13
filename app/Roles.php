<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Roles extends Model
{

    use QueryCacheable;

    protected $cacheFor = 180;

    public static function getRoles(): object
    {
        return Roles::orderBy('order', 'ASC')->get();
    }

    public static function getStaffRoles(): object
    {
        return Roles::where('staff', true)->pluck(['id', 'name'])->get();
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
        return in_array($permission, json_decode(Roles::where('id', $role)->pluck('permissions')->first(), true));
    }
}
