<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;

class PermissionHelper
{
    private array $_permissions = [];

    public function __construct()
    {
        $this->register('Cashier', Permission::CASHIER, [
            Permission::CASHIER_CREATE => 'Create Orders',
            Permission::CASHIER_SELF_PURCHASES => 'Create orders for themselves',
            Permission::CASHIER_USERS_OTHER_ROTATIONS => 'Create orders for users from other Rotations',
        ]);

        $this->register('Users', Permission::USERS, [
            Permission::USERS_LIST => 'List all Users',
            Permission::USERS_VIEW => 'View specific User information',
            Permission::USERS_MANAGE => 'Edit/Create/Delete Users',
            Permission::USERS_PAYOUTS_CREATE => 'Create Payouts',
            Permission::USERS_LIST_SELECT_ROTATION => 'View Users from other Rotations',
        ]);

        $this->register('Product Management', Permission::PRODUCTS, [
            Permission::PRODUCTS_LIST => 'List all Products',
            Permission::PRODUCTS_MANAGE => 'Edit/Create/Delete Products',
            Permission::PRODUCTS_LEDGER => 'Adjust stock for Products',
        ]);

        $this->register('Activity Management', Permission::ACTIVITIES, [
            Permission::ACTIVITIES_LIST => 'List all Activities',
            Permission::ACTIVITIES_VIEW => 'View specific Activity information',
            Permission::ACTIVITIES_MANAGE => 'Edit/Create/Delete Activities',
            Permission::ACTIVITIES_REGISTER_USER => 'Register Users for Activities',
        ]);

        $this->register('Order Management', Permission::ORDERS, [
            Permission::ORDERS_LIST => 'List all Orders',
            Permission::ORDERS_LIST_SELECT_ROTATION => 'View Orders from other Rotations',
            Permission::ORDERS_VIEW => 'View specific Order information',
            Permission::ORDERS_RETURN => 'Return whole Orders or individual items',
        ]);

        $this->register('Statistics', Permission::STATISTICS, [
            Permission::STATISTICS_ORDER_HISTORY => 'View Order history chart',
            Permission::STATISTICS_PRODUCT_SALES => 'View Product sales chart',
            Permission::STATISTICS_ACTIVITY_SALES => 'View Activity sales chart',
            Permission::STATISTICS_INCOME_INFO => 'View Income info chart',
            Permission::STATISTICS_SELECT_ROTATION => 'View statistics charts from other Rotations',
        ]);

        $this->register('Settings', Permission::SETTINGS, [
            Permission::SETTINGS_GENERAL => 'Edit tax rates',
            Permission::SETTINGS_CATEGORIES_MANAGE => 'Edit/Create/Delete Categories',
            Permission::SETTINGS_ROLES_MANAGE => 'Edit/Create/Delete Roles',
            Permission::SETTINGS_ROTATIONS_MANAGE => 'Edit/Create/Delete Rotations',
            Permission::SETTINGS_GIFT_CARDS_MANAGE => 'Edit/Create/Delete Gift Cards',
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
    #[Pure]
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

        return $nodes;
    }
}
