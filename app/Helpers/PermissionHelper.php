<?php

namespace App\Helpers;

use App\Role;
use Illuminate\Support\Str;

class PermissionHelper
{

    private static ?PermissionHelper $_instance = null;

    private array $_permissions = array();

    public static function getInstance(): PermissionHelper
    {
        if (self::$_instance == null) {
            self::$_instance = new PermissionHelper();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        $this->register('Cashier', 'cashier', [
            'cashier_create' => 'Create Orders',
        ]);

        $this->register('Users', 'users', [
            'users_list' => 'List all Users',
            'users_view' => 'View specific User information',
            'users_manage' => 'Edit/Create/Delete Users'
        ]);

        $this->register('Product Management', 'products', [
            'products_list' => 'List all Products',
            'products_manage' => 'Edit/Create/Delete Products',
            'products_adjust' => 'Adjust stock for Products'
        ]);

        $this->register('Activity Management', 'activities', [
            'activities_list' => 'List all Activities',
            'activities_manage' => 'Edit/Create/Delete Activities',
            'activities_register_user' => 'Register User for Activity'
        ]);

        $this->register('Order Management', 'orders', [
            'orders_list' => 'List all Orders',
            'orders_view' => 'View specific Order information',
            'orders_return' => 'Return whole Orders or individual items'
        ]);

        $this->register('Statistics', 'statistics', [
            'statistics_order_history' => 'View Order history chart',
            'statistics_item_info' => 'View Product info chart'
        ]);

        $this->register('Settings', 'settings', [
            'settings_general' => 'Edit tax rates',
            'settings_categories_manage' => 'Edit/Create/Delete Categories',
            'settings_roles_manage' => 'Edit/Create/Delete Roles'
        ]);
    }

    /**
     * Registers a new permission category
     */
    private function register(string $category_name, string $root_node, array $permissions)
    {
        $this->_permissions[$category_name]['root_node'] = $root_node;
        $this->_permissions[$category_name]['permissions'] = $permissions;
    }

    public function getCategories(): array
    {
        $return = array();

        foreach ($this->_permissions as $category_name => $meta) {
            $return[] = $category_name;
        }

        return $return;
    }

    /**
     * Returns comma seperated unique category root nodes / keys 
     */
    public function getCategoryKeys(): string
    {
        $return = '';

        foreach ($this->getCategories() as $category_name) {
            $return .= "'" . $this->_permissions[$category_name]['root_node'] . "',";
        }

        return rtrim($return, ',');
    }

    public function renderForm(?Role $role, ?array $role_permissions): string
    {
        $return = '';

        foreach ($this->getCategories() as $category) {
            $category_meta = $this->_permissions[$category];
            $category_root_node = $category_meta['root_node'];
            $category_permissions = $category_meta['permissions'];

            $category_permissions_html = '';
            foreach ($category_permissions as $node => $name) {
                $checked = (!is_null($role) && (in_array($node, $role_permissions) || $role->superuser)) ? 'checked' : '';
                $category_permissions_html .= "
                    <label class=\"checkbox\">
                        <input type=\"checkbox\" class=\"permission\" name=\"permissions[$node]\" value=\"1\" $checked>
                        $name
                    </label>
                    &nbsp;
                ";
            }

            $checked = (!is_null($role) && (in_array($category_root_node, $role_permissions) || $role->superuser)) ? 'checked' : '';
            $return .= "
                <h4 class=\"subtitle\"><strong>$category</strong>&nbsp;<input type=\"checkbox\" class=\"permission\" id=\"permission-$category_root_node-checkbox\" name=\"permissions[$category_root_node]\" onclick=\"updateSections();\" value=\"1\" $checked></h4>
                <div class=\"control\" id=\"permission-$category_root_node\" style=\"display: none;\">
                    $category_permissions_html
                </div>
                <hr>
            ";
        }

        return $return;
    }

    public static function parseNodes($permissions): array
    {
        $return = array();
        if (is_array($permissions)) {
            $selected_categories = array();
            foreach ($permissions as $permission => $value) {
                if (!$value) {
                    continue;
                }

                // if this node doesnt have a _, its probably a category root node
                if (!Str::contains($permission, '_')) {
                    $return[] = $permission;
                    $selected_categories[] = $permission;
                    continue;
                }

                // grab the root node from a normal node (first element if we split by _)
                // and ensure this node was selected. if not, dont add it
                $category = explode('_', $permission)[0];
                if (!in_array($category, $selected_categories)) {
                    continue;
                }

                $return[] = $permission;
            }

            foreach ($selected_categories as $category_root_node) {
                // remove any root categories which have no child nodes selected
                $found = false;

                foreach ($return as $permission) {
                    if (Str::startsWith($permission, $category_root_node . '_')) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    unset($return[array_search($category_root_node, $return)]);
                }
            }
        }

        return $return;
    }
}