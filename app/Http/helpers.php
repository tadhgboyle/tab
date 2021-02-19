<?php 

if (! function_exists('hasPermission')) {
    /**
     * Check if the logged in user has specified permission
     *
     * @param  string  $permission
     * @return bool
     */
    function hasPermission(string $permission): bool
    {
        return Auth::user()->hasPermission($permission);
    }
}