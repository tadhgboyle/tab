<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Support\Str;

class PermissionHelper
{
    private array $_permissions = [];

    public function __construct()
    {
        $this->register('Dashboard', Permission::DASHBOARD, [
            Permission::DASHBOARD_USERS => 'View user statistics',
            Permission::DASHBOARD_FINANCIAL => 'View financial statistics',
            Permission::DASHBOARD_ACTIVITIES => 'View activity statistics',
            Permission::DASHBOARD_PRODUCTS => 'View product statistics',
            Permission::DASHBOARD_GIFT_CARDS => 'View gift card statistics',
            Permission::DASHBOARD_ALERTS => 'View alerts',
        ]);

        $this->register('Cashier', Permission::CASHIER, [
            Permission::CASHIER_CREATE => 'Create orders',
            Permission::CASHIER_SELF_PURCHASES => 'Create orders for themselves',
            Permission::CASHIER_USERS_OTHER_ROTATIONS => 'Create orders for users from other rotations',
        ]);

        $this->register('Users', Permission::USERS, [
            Permission::USERS_LIST => 'List all users',
            Permission::USERS_VIEW => 'View specific user information',
            Permission::USERS_MANAGE => 'Edit/Create/Delete users',
            Permission::USERS_LIST_SELECT_ROTATION => 'View users from other rotations',
        ]);

        $this->register('Product Management', Permission::PRODUCTS, [
            Permission::PRODUCTS_VIEW => 'View specific product information',
            Permission::PRODUCTS_VIEW_DRAFT => 'View draft products',
            Permission::PRODUCTS_VIEW_COST => 'View product cost',
            Permission::PRODUCTS_LIST => 'List all products',
            Permission::PRODUCTS_MANAGE => 'Edit/Create/Delete products',
            Permission::PRODUCTS_LEDGER => 'Adjust stock for products',
        ]);

        $this->register('Activity Management', Permission::ACTIVITIES, [
            Permission::ACTIVITIES_LIST => 'List all activities',
            Permission::ACTIVITIES_VIEW => 'View specific activity information',
            Permission::ACTIVITIES_MANAGE => 'Edit/Create/Delete activities',
            Permission::ACTIVITIES_MANAGE_REGISTRATIONS => 'Register/Remove activity users',
        ]);

        $this->register('Order Management', Permission::ORDERS, [
            Permission::ORDERS_LIST => 'List all orders',
            Permission::ORDERS_LIST_SELECT_ROTATION => 'View orders from other rotations',
            Permission::ORDERS_VIEW => 'View specific order information',
            Permission::ORDERS_RETURN => 'Return whole orders or individual items',
        ]);

        $this->register('Settings', Permission::SETTINGS, [
            Permission::SETTINGS_GENERAL => 'Edit tax rates',
            Permission::SETTINGS_CATEGORIES_MANAGE => 'Edit/Create/Delete categories',
            Permission::SETTINGS_ROLES_MANAGE => 'Edit/Create/Delete roles',
            Permission::SETTINGS_ROTATIONS_MANAGE => 'Edit/Create/Delete rotations',
            Permission::SETTINGS_GIFT_CARDS_MANAGE => 'View/Edit/Create/Delete gift Cards',
        ]);
    }

    /**
     * Registers a new permission category.
     */
    private function register(string $category_name, string $root_node, array $permissions): void
    {
        $this->_permissions[$category_name] = [
            'root_node' => $root_node,
            'permissions' => $permissions,
        ];
    }

    public function getCategories(): array
    {
        return array_keys($this->_permissions);
    }

    /**
     * @return string Comma seperated unique category root nodes / keys.
     */
    public function getCategoryKeys(): string
    {
        $return = '';

        foreach ($this->getCategories() as $category_name) {
            $return .= "'" . $this->_permissions[$category_name]['root_node'] . "',";
        }

        return rtrim($return, ',');
    }

    public function renderForm(?Role $role): string
    {
        $return = '';

        foreach ($this->getCategories() as $category) {
            $category_meta = $this->_permissions[$category];
            $category_root_node = $category_meta['root_node'];
            $category_permissions = $category_meta['permissions'];

            $category_permissions_html = '';
            foreach ($category_permissions as $node => $name) {
                $checked = (!is_null($role) && ($role->superuser || in_array($node, $role->permissions, true))) ? 'checked' : '';

                $category_permissions_html .= <<<HTML
                    <label class="checkbox">
                        <input type="checkbox" class="permission" name="permissions[$node]" value="1" $checked>
                        $name
                    </label>
                    &nbsp;
                HTML;
            }

            $checked = (!is_null($role) && ($role->superuser || in_array($category_root_node, $role->permissions, true))) ? 'checked' : '';

            // TODO: click on name of category to select/deselect checkbox
            $return .= <<<HTML
                <h4 class="subtitle"><strong>$category</strong>&nbsp;<input type="checkbox" class="permission" id="permission-$category_root_node-checkbox" name="permissions[$category_root_node]" onclick="updateSections();" value="1" $checked></h4>
                <div class="control" id="permission-$category_root_node" style="display: none;">
                    $category_permissions_html
                </div>
                <hr>
            HTML;
        }

        return $return;
    }

    public static function parseNodes($permissions): array
    {
        $nodes = [];

        if (!is_array($permissions)) {
            return $nodes;
        }

        $selected_categories = [];
        foreach ($permissions as $permission => $value) {
            if (!$value) {
                continue;
            }

            // if this node doesn't have a _, it's probably a category root node
            if (!Str::contains($permission, '_')) {
                $nodes[] = $permission;
                $selected_categories[] = $permission;
                continue;
            }

            // grab the root node from a normal node (first element if we split by _)
            // and ensure this node was selected. if not, don't add it
            $category = explode('_', $permission)[0];
            if (!in_array($category, $selected_categories, true)) {
                continue;
            }

            $nodes[] = $permission;
        }

        foreach ($selected_categories as $category_root_node) {
            // remove any root categories which have no child nodes selected
            $found = false;

            foreach ($nodes as $permission) {
                if (Str::startsWith($permission, $category_root_node . '_')) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                unset($nodes[array_search($category_root_node, $nodes, true)]);
            }
        }

        return array_values($nodes);
    }
}
