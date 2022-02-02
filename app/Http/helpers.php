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
        return Auth::user()->hasPermission($permission);
    }
}

if (!function_exists('page')) {
    /**
     * Check if current page matches this navbar item and display "is-active" class if so.
     *
     * @param string $current_page
     * @param string|null $page
     *
     * @return string
     */
    function page(string $current_page, ?string $page): string
    {
        return !is_null($page) && $page === $current_page ? ' is-active ' : '';
    }
}
