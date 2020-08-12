<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Roles extends Model
{
    use QueryCacheable;

    protected $cacheFor = 180;

    public static function getRoles()
    {
        return Roles::orderBy('order', 'ASC')->get();
    }

    public static function idToName($id)
    {
        return Roles::where('role_id', $id)->pluck('name')->first();
    }

    public static function canViewPage($role, $page)
    {
        $pages_allowed = json_decode(Roles::where('role_id', $role)->pluck('pages_allowed')->first(), true);
        if (in_array($page, $pages_allowed)) return true;
        // For easier use of post routes, their names will always end in _form, so if they have "products_new" they will also have "products_new_form"
        else if (substr_compare($page, '_form', -strlen('_form')) === 0 && in_array(str_replacE('_form', '', $page), $pages_allowed)) return true;
        else return false; 
    }

    // TODO: Use for fine-tuning (ie: they can view settings page, but not create new roles)
    public static function hasPermission($role, $permission)
    {
        return in_array($permission, json_decode(Roles::where('role_id', $role)->pluck('permissions')->first(), true));
    }
}
