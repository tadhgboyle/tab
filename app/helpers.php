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

if (!function_exists('page')) {
    /**
     * Check if current page matches this navbar item and display "is-active" class if so.
     *
     * @param string $current_page
     * @param string $page
     *
     * @return bool
     */
    function page(string $current_page, string $page): bool
    {
        return $page === $current_page;
    }
}
