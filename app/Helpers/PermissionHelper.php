<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;

class PermissionHelper extends Helper
{
    private array $_permissions = [];

    public function __construct()
    {
        $this->register('Cashier', 'cashier', [
            'cashier_create' => 'Create Orders',
            'cashier_self_purchases' => 'Create orders for themselves',
            'cashier_users_other_rotations' => 'Create orders for users from other Rotations',
        ]);

        $this->register('Users', 'users', [
            'users_list' => 'List all Users',
            'users_list_select_rotation' => 'View Users from other Rotations',
            'users_view' => 'View specific User information',
            'users_manage' => 'Edit/Create/Delete Users',
            'users_payouts_create' => 'Create Payouts',
        ]);

        $this->register('Product Management', 'products', [
            'products_list' => 'List all Products',
            'products_manage' => 'Edit/Create/Delete Products',
            'products_adjust' => 'Adjust stock for Products',
        ]);

        $this->register('Activity Management', 'activities', [
            'activities_list' => 'List all Activities',
            'activities_view' => 'View specific Activity information',
            'activities_manage' => 'Edit/Create/Delete Activities',
            'activities_register_user' => 'Register Users for Activities',
        ]);

        $this->register('Order Management', 'orders', [
            'orders_list' => 'List all Orders',
            'orders_list_select_rotation' => 'View Orders from other Rotations',
            'orders_view' => 'View specific Order information',
            'orders_return' => 'Return whole Orders or individual items',
        ]);

        $this->register('Statistics', 'statistics', [
            'statistics_select_rotation' => 'View statistics charts from other Rotations',
            'statistics_order_history' => 'View Order history chart',
            'statistics_item_info' => 'View Product info chart',
            'statistics_activity_info' => 'View Activity info chart',
            'statistics_income_info' => 'View Income info chart',
        ]);

        $this->register('Settings', 'settings', [
            'settings_general' => 'Edit tax rates',
            'settings_categories_manage' => 'Edit/Create/Delete Categories',
            'settings_roles_manage' => 'Edit/Create/Delete Roles',
            'settings_rotations_manage' => 'Edit/Create/Delete Rotations',
        ]);

        $this->register('Misc', 'misc', [
            'misc_login_no_rotation' => 'Let Users login when no Rotation is currently active' // TODO implement, or find better UX solution
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

    #[Pure]
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
