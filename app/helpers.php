<?php

if (!function_exists('hasPermission')) {
    /**
     * Check if the logged in user has specified permission.
     *
     * @param array|string $permission
     *
     * @return bool
     */
    function hasPermission(array|string $permission): bool
    {
        return auth()->user()->hasPermission($permission);
    }
}
