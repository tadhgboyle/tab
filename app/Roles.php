<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Roles extends Model
{
    use QueryCacheable;

    protected $cacheFor = 180;

    public static function canViewPage($role, $page)
    {
        $pages_allowed = json_decode(Roles::where('role_id', $role)->pluck('pages_allowed')->first(), true);
        if (in_array($page, $pages_allowed)) return true;
        else if (self::endsWith($page, '_form') && in_array(str_replacE('_form', '', $page), $pages_allowed)) return true;
        else return false; 
    }

    private static function endsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }
}
