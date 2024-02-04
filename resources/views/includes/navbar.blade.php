<nav class="navbar has-shadow">
    <div class="container">
        <div class="navbar-brand">
            <a class="navbar-item" href="{{ route('index') }}">
                <b class="navbar-item">tab</b>
            </a>
        </div>
        <div class="navbar-menu">
            <div class="navbar-start">
                @auth
                    @if(hasPermission(\App\Helpers\Permission::CASHIER) && hasPermission(\App\Helpers\Permission::CASHIER_CREATE))
                        <a class="navbar-item {{ page('cashier', @$page) }}" href="{{ route('index') }}">
                            🛒 Cashier
                        </a>
                    @endif

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

                    @permission(\App\Helpers\Permission::STATISTICS)
                        <a class="navbar-item {{ page('statistics', @$page) }}" href="{{ route('statistics') }}">
                            📊 Statistics
                        </a>
                    @endpermission
                @endauth
            </div>

            <div class="navbar-end">
                @auth
                    <div class="navbar-item">
                        <div class="field is-grouped">
                            @permission(\App\Helpers\Permission::SETTINGS)
                                <div class="control">
                                    <a class="button is-light" href="{{ route('settings') }}">
                                        <span class="icon">
                                            ⚙️
                                        </span>
                                    </a>
                                </div>
                            @endpermission
                            <div class="control">
                                <a class="button is-light" href="{{ route('logout') }}">
                                    <span class="icon">
                                        🚪
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>
