<nav class="navbar has-shadow">
    <div class="container">
        <div class="navbar-menu">
            <div class="navbar-start">
                @permission(\App\Helpers\Permission::DASHBOARD)
                    <a class="navbar-item {{ page('dashboard', @$page) }}" href="{{ route('dashboard') }}">
                        🏠 Dashboard
                    </a>
                @endpermission

                @permission(\App\Helpers\Permission::CASHIER_CREATE)
                    <a class="navbar-item {{ page('cashier', @$page) }}" href="{{ route('cashier') }}">
                        🛒 Cashier
                    </a>
                @endpermission

                @permission(\App\Helpers\Permission::USERS)
                    <div class="navbar-item has-dropdown is-hoverable">
                        <p class="navbar-link is-arrowless {{ page('users', @$page) }}">👥 Users</p>
                        <div class="navbar-dropdown is-boxed">
                            @permission(\App\Helpers\Permission::USERS_LIST)
                                <a class="navbar-item" href="{{ route('users_list') }}">
                                    List
                                </a>
                            @endpermission
                            @permission(\App\Helpers\Permission::USERS_MANAGE)
                                <a class="navbar-item" href="{{ route('users_create') }}">
                                    Create
                                </a>
                            @endpermission
                        </div>
                    </div>
                @endpermission

                @permission(\App\Helpers\Permission::PRODUCTS)
                    <div class="navbar-item has-dropdown is-hoverable">
                        <p class="navbar-link is-arrowless {{ page('products', @$page) }}">🏷 Products</p>
                        <div class="navbar-dropdown is-boxed">
                            @permission(\App\Helpers\Permission::PRODUCTS_LIST)
                                <a class="navbar-item" href="{{ route('products_list') }}">
                                    List
                                </a>
                            @endpermission
                            @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)
                                <a class="navbar-item" href="{{ route('products_create') }}">
                                    Create
                                </a>
                            @endpermission
                            @permission(\App\Helpers\Permission::PRODUCTS_LEDGER)
                                <a class="navbar-item" href="{{ route('products_ledger') }}">
                                    Ledger
                                </a>
                            @endpermission
                        </div>
                    </div>
                @endpermission

                @permission(\App\Helpers\Permission::ACTIVITIES)
                    <div class="navbar-item has-dropdown is-hoverable">
                        <p class="navbar-link is-arrowless {{ page('activities', @$page) }}">🗓 Activities</p>
                        <div class="navbar-dropdown is-boxed">
                            @permission(\App\Helpers\Permission::ACTIVITIES_LIST)
                                <a class="navbar-item" href="{{ route('activities_list') }}">
                                    List
                                </a>
                            @endpermission
                            @permission(\App\Helpers\Permission::ACTIVITIES_MANAGE)
                                <a class="navbar-item" href="{{ route('activities_create') }}">
                                    Create
                                </a>
                            @endpermission
                        </div>
                    </div>
                @endpermission

                @permission(\App\Helpers\Permission::ORDERS_LIST)
                    <a class="navbar-item {{ page('orders', @$page) }}" href="{{ route('orders_list') }}">
                        🛍 Orders
                    </a>
                @endpermission
            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="field is-grouped">
                        @permission(\App\Helpers\Permission::SETTINGS)
                        <a class="navbar-item {{ page('settings', @$page) }}" href="{{ route('settings') }}">
                        ⚙️ Settings
                    </a>
                        @endpermission
                        <a class="navbar-item" href="{{ route('logout') }}">
                        🚪 Logout
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
