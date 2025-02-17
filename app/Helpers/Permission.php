<?php

namespace App\Helpers;

class Permission
{
    public const DASHBOARD = 'dashboard';
    public const DASHBOARD_USERS = 'dashboard_users';
    public const DASHBOARD_FINANCIAL = 'dashboard_financial';
    public const DASHBOARD_ACTIVITIES = 'dashboard_activities';
    public const DASHBOARD_PRODUCTS = 'dashboard_products';
    public const DASHBOARD_GIFT_CARDS = 'dashboard_gift_cards';
    public const DASHBOARD_ALERTS = 'dashboard_alerts';

    public const CASHIER = 'cashier';
    public const CASHIER_CREATE = 'cashier_create';
    public const CASHIER_SELF_PURCHASES = 'cashier_self_purchases';
    public const CASHIER_USERS_OTHER_ROTATIONS = 'cashier_users_other_rotations';

    public const USERS = 'users';
    public const USERS_LIST = 'users_list';
    public const USERS_VIEW = 'users_view';
    public const USERS_VIEW_DELETED = 'users_view_deleted'; // TODO
    public const USERS_MANAGE = 'users_manage';
    public const USERS_LIST_SELECT_ROTATION = 'users_list_select_rotation';

    public const FAMILIES = 'families';
    public const FAMILIES_LIST = 'families_list';
    public const FAMILIES_VIEW = 'families_view';
    public const FAMILIES_MANAGE = 'families_manage';

    public const PRODUCTS = 'products';
    public const PRODUCTS_LIST = 'products_list';
    public const PRODUCTS_VIEW = 'products_view';
    public const PRODUCTS_VIEW_DRAFT = 'products_view_draft';
    public const PRODUCTS_VIEW_COST = 'products_view_cost';
    public const PRODUCTS_VIEW_DELETED = 'products_view_deleted'; // TODO
    public const PRODUCTS_MANAGE = 'products_manage';
    public const PRODUCTS_LEDGER = 'products_ledger';

    public const ACTIVITIES = 'activities';
    public const ACTIVITIES_LIST = 'activities_list';
    public const ACTIVITIES_VIEW = 'activities_view';
    public const ACTIVITIES_MANAGE = 'activities_manage';
    public const ACTIVITIES_MANAGE_REGISTRATIONS = 'activities_manage_registrations';

    public const ORDERS = 'orders';
    public const ORDERS_LIST = 'orders_list';
    public const ORDERS_LIST_SELECT_ROTATION = 'orders_list_select_rotation'; // TODO
    public const ORDERS_VIEW = 'orders_view';
    public const ORDERS_RETURN = 'orders_return';

    public const SETTINGS = 'settings';
    public const SETTINGS_GENERAL = 'settings_general';
    public const SETTINGS_CATEGORIES_MANAGE = 'settings_categories_manage';
    public const SETTINGS_ROLES_MANAGE = 'settings_roles_manage';
    public const SETTINGS_ROTATIONS_MANAGE = 'settings_rotations_manage';
    public const SETTINGS_GIFT_CARDS_MANAGE = 'settings_gift_cards_manage';
}
