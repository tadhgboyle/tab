<?php

namespace App\Helpers;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class Permission
{
    public const CASHIER = 'cashier';
    public const CASHIER_CREATE = 'cashier_create';
    public const CASHIER_SELF_PURCHASES = 'cashier_self_purchases';
    public const CASHIER_USERS_OTHER_ROTATIONS = 'cashier_users_other_rotations';

    public const CASHIER_PERMISSIONS = [
        //self::CASHIER,
        self::CASHIER_CREATE => 'Create Orders',
        self::CASHIER_SELF_PURCHASES => 'Create orders for themselves',
        self::CASHIER_USERS_OTHER_ROTATIONS => 'Create orders for users from other Rotations',
    ];

    public const USERS = 'users';
    public const USERS_LIST = 'users_list';
    public const USERS_VIEW = 'users_view';
    public const USERS_VIEW_DELETED = 'users_view_deleted'; // TODO
    public const USERS_MANAGE = 'users_manage';
    public const USERS_PAYOUTS_CREATE = 'users_payouts_create';
    public const USERS_LIST_SELECT_ROTATION = 'users_list_select_rotation';

    public const USER_PERMISSIONS = [
        //self::USERS,
        self::USERS_LIST => 'List all Users',
        self::USERS_VIEW => 'View specific User information',
        self::USERS_VIEW_DELETED => 'View deleted Users', // TODO
        self::USERS_MANAGE => 'Edit/Create/Delete Users',
        self::USERS_PAYOUTS_CREATE => 'Create Payouts',
        self::USERS_LIST_SELECT_ROTATION => 'View Users from other Rotations',
    ];

    public const PRODUCTS = 'products';
    public const PRODUCTS_LIST = 'products_list';
    public const PRODUCTS_VIEW_DELETED = 'products_view_deleted'; // TODO
    public const PRODUCTS_MANAGE = 'products_manage';
    public const PRODUCTS_LEDGER = 'products_ledger';

    public const PRODUCT_PERMISSIONS = [
        // self::PRODUCTS,
        self::PRODUCTS_LIST => 'List all Products',
        self::PRODUCTS_VIEW_DELETED => 'View deleted Products', // TODO
        self::PRODUCTS_MANAGE => 'Edit/Create/Delete Products',
        self::PRODUCTS_LEDGER => 'Adjust stock for Products',
    ];

    public const ACTIVITIES = 'activities';
    public const ACTIVITIES_LIST = 'activities_list';
    public const ACTIVITIES_VIEW = 'activities_view';
    public const ACTIVITIES_MANAGE = 'activities_manage';
    public const ACTIVITIES_REGISTER_USER = 'activities_register_user';

    public const ACTIVITY_PERMISSIONS = [
        //self::ACTIVITIES,
        self::ACTIVITIES_LIST => 'List all Activities',
        self::ACTIVITIES_VIEW => 'View specific Activity information',
        self::ACTIVITIES_MANAGE => 'Edit/Create/Delete Activities',
        self::ACTIVITIES_REGISTER_USER => 'Register Users for Activities',
    ];

    public const ORDERS = 'orders';
    public const ORDERS_LIST = 'orders_list';
    public const ORDERS_LIST_SELECT_ROTATION = 'orders_list_select_rotation'; // TODO
    public const ORDERS_VIEW = 'orders_view';
    public const ORDERS_RETURN = 'orders_return';

    public const ORDER_PERMISSIONS = [
        //self::ORDERS,
        self::ORDERS_LIST => 'List all Orders',
        self::ORDERS_LIST_SELECT_ROTATION => 'View Orders from other Rotations',
        self::ORDERS_VIEW => 'View specific Order information',
        self::ORDERS_RETURN => 'Return whole Orders or individual items',
    ];

    public const STATISTICS = 'statistics';
    public const STATISTICS_ORDER_HISTORY = 'statistics_order_history';
    public const STATISTICS_PRODUCT_SALES = 'statistics_product_sales';
    public const STATISTICS_ACTIVITY_SALES = 'statistics_activity_sales';
    public const STATISTICS_INCOME_INFO = 'statistics_income_info';
    public const STATISTICS_SELECT_ROTATION = 'statistics_select_rotation';

    public const STATISTICS_PERMISSIONS = [
        //self::STATISTICS,
        self::STATISTICS_ORDER_HISTORY => 'View Order history chart',
        self::STATISTICS_PRODUCT_SALES => 'View Product sales chart',
        self::STATISTICS_ACTIVITY_SALES => 'View Activity sales chart',
        self::STATISTICS_INCOME_INFO => 'View Income info chart',
        self::STATISTICS_SELECT_ROTATION => 'View statistics charts from other Rotations',
    ];

    public const SETTINGS = 'settings';
    public const SETTINGS_GENERAL = 'settings_general';
    public const SETTINGS_CATEGORIES_MANAGE = 'settings_categories_manage';
    public const SETTINGS_ROLES_MANAGE = 'settings_roles_manage';
    public const SETTINGS_ROTATIONS_MANAGE = 'settings_rotations_manage';
    public const SETTINGS_GIFT_CARDS_MANAGE = 'settings_gift_cards_manage';

    public const SETTINGS_PERMISSIONS = [
        //self::SETTINGS,
        self::SETTINGS_GENERAL => 'Edit tax rates',
        self::SETTINGS_CATEGORIES_MANAGE => 'Edit/Create/Delete Categories',
        self::SETTINGS_ROLES_MANAGE => 'Edit/Create/Delete Roles',
        self::SETTINGS_ROTATIONS_MANAGE => 'Edit/Create/Delete Rotations',
        self::SETTINGS_GIFT_CARDS_MANAGE => 'Edit/Create/Delete Gift Cards',
    ];

    public static function createFilamentSchema(): Grid
    {
        $all_permissions = [
            'Cashier Permissions' => self::CASHIER_PERMISSIONS,
            'User Permissions' => self::USER_PERMISSIONS,
            'Product Permissions' => self::PRODUCT_PERMISSIONS,
            'Activity Permissions' => self::ACTIVITY_PERMISSIONS,
            'Order Permissions' => self::ORDER_PERMISSIONS,
            'Statistics Permissions' => self::STATISTICS_PERMISSIONS,
            'Settings Permissions' => self::SETTINGS_PERMISSIONS,
        ];

        $sections = [];

        foreach ($all_permissions as $name => $permissions) {
            $columns = match (count($permissions)) {
                3 => 3,
                4 => 4,
                6 => 3,
                default => 4,
            };
            $sections[] = Section::make($name)
                ->schema([
                    CheckboxList::make($name)
                        ->label('')
                        ->options(array_keys($permissions))
                        ->descriptions(array_values($permissions))
                        ->bulkToggleable()
                        ->columns($columns)
                ]);
        }

        return Grid::make()->schema($sections);
    }
}
