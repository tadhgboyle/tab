<?php 

if (! function_exists('hasPermission')) {
    /**
     * Check if the logged in user has specified permission
     *
     * @param  $permission
     * @return bool
     */
    function hasPermission($permission): bool
    {
        return Auth::user()->hasPermission($permission);
    }
}