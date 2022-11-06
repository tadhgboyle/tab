<?php

namespace App\Helpers;

class Permission
{
    public const CASHIER = 'cashier';
    public const CASHIER_CREATE = 'cashier_create';
    public const CASHIER_SELF_PURCHASES = 'cashier_self_purchases';
    public const CASHIER_USERS_OTHER_ROTATIONS = 'cashier_users_other_rotations';

    public const USERS = 'users';
    public const USERS_LIST = 'users_list';
    public const USERS_VIEW = 'users_view';
    public const USERS_VIEW_DELETED = 'users_view_deleted'; // TODO
    public const USERS_MANAGE = 'users_manage';
    public const USERS_PAYOUTS_CREATE = 'users_payouts_create';
    public const USERS_LIST_SELECT_ROTATION = 'users_list_select_rotation';

    public const PRODUCTS = 'products';
    public const PRODUCTS_LIST = 'products_list';
    public const PRODUCTS_VIEW_DELETED = 'products_view_deleted'; // TODO
    public const PRODUCTS_MANAGE = 'products_manage';
    public const PRODUCTS_ADJUST = 'products_adjust';

    public const ACTIVITIES = 'activities';
    public const ACTIVITIES_LIST = 'activities_list';
    public const ACTIVITIES_VIEW = 'activities_view';
    public const ACTIVITIES_MANAGE = 'activities_manage';
    public const ACTIVITIES_REGISTER_USER = 'activities_register_user';

    public const ORDERS = 'orders';
    public const ORDERS_LIST = 'orders_list';
    public const ORDERS_LIST_SELECT_ROTATION = 'orders_list_select_rotation'; // TODO
    public const ORDERS_VIEW = 'orders_view';
    public const ORDERS_RETURN = 'orders_return';

    public const STATISTICS = 'statistics';
    public const STATISTICS_ORDER_HISTORY = 'statistics_order_history';
    public const STATISTICS_PRODUCT_SALES = 'statistics_product_sales';
    public const STATISTICS_ACTIVITY_SALES = 'statistics_activity_sales';
    public const STATISTICS_INCOME_INFO = 'statistics_income_info';
    public const STATISTICS_SELECT_ROTATION = 'statistics_select_rotation';

    public const SETTINGS = 'settings';
    public const SETTINGS_GENERAL = 'settings_general';
    public const SETTINGS_CATEGORIES_MANAGE = 'settings_categories_manage';
    public const SETTINGS_ROLES_MANAGE = 'settings_roles_manage';
    public const SETTINGS_ROTATIONS_MANAGE = 'settings_rotations_manage';
}
