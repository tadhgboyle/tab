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
                            <i class="fas fa-money-bill-wave-alt"></i>&nbsp;Cashier
                        </a>
                    @endif

                    @permission(\App\Helpers\Permission::USERS)
                        <div class="navbar-item has-dropdown is-hoverable">
                            <p class="navbar-link {{ page('users', @$page) }}"><i class="fas fa-users"></i>&nbsp;Users</p>
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
                            <p class="navbar-link {{ page('products', @$page) }}"><i class="fas fa-tag"></i>&nbsp;Products</p>
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
                                @permission(\App\Helpers\Permission::PRODUCTS_ADJUST)
                                    <a class="navbar-item" href="{{ route('products_adjust') }}">
                                        Adjust
                                    </a>
                                @endpermission
                            </div>
                        </div>
                    @endpermission

                    @permission(\App\Helpers\Permission::ACTIVITIES)
                        <div class="navbar-item has-dropdown is-hoverable">
                            <p class="navbar-link {{ page('activities', @$page) }}"><i class="fas fa-calendar-alt"></i>&nbsp;Activities</p>
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
                            <i class="fas fa-shopping-basket"></i>&nbsp;Orders
                        </a>
                    @endpermission

                    @permission(\App\Helpers\Permission::STATISTICS)
                        <a class="navbar-item {{ page('statistics', @$page) }}" href="{{ route('statistics') }}">
                            <i class="fas fa-chart-pie"></i>&nbsp;Statistics
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
                                    <a class="button is-warning" href="{{ route('settings') }}">
                                        <span class="icon">
                                            <i class="fas fa-cogs"></i>
                                        </span>
                                    </a>
                                </div>
                            @endpermission
                            <div class="control">
                                <a class="button is-primary" href="{{ route('logout') }}">
                                    <span class="icon">
                                        <i class="fas fa-sign-out-alt"></i>
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
