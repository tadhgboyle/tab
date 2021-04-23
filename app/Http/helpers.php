<?php

if (!function_exists('hasPermission')) {
    /**
     * Check if the logged in user has specified permission.
     *
     * @param $permission
     *
     * @return bool
     */
    function hasPermission($permission): bool
    {
        return Auth::user()->hasPermission($permission);
    }
}

if (!function_exists('page')) {
    /**
     * Check if current page matches this navbar item and display "is-active" class if so.
     *
     * @param $navbar_item
     * @param $page
     *
     * @return bool
     */
    function page(string $current_page, ?string $page): string
    {
        return !is_null($page) && $page == $current_page ? ' is-active ' : '';
    }
}
