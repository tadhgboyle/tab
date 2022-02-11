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
                    @if(hasPermission('cashier') && hasPermission('cashier_create'))
                        <a class="navbar-item {{ page('cashier', @$page) }}" href="{{ route('index') }}">
                            <i class="fas fa-money-bill-wave-alt"></i>&nbsp;Cashier
                        </a>
                    @endif

                    @permission('users')
                        <div class="navbar-item has-dropdown is-hoverable">
                            <p class="navbar-link {{ page('users', @$page) }}"><i class="fas fa-users"></i>&nbsp;Users</p>
                            <div class="navbar-dropdown is-boxed">
                                @permission('users_list')
                                    <a class="navbar-item" href="{{ route('users_list') }}">
                                        List
                                    </a>
                                @endpermission
                                @permission('users_manage')
                                    <a class="navbar-item" href="{{ route('users_new') }}">
                                        Create
                                    </a>
                                @endpermission
                            </div>
                        </div>
                    @endpermission

                    @permission('products')
                        <div class="navbar-item has-dropdown is-hoverable">
                            <p class="navbar-link {{ page('products', @$page) }}"><i class="fas fa-tag"></i>&nbsp;Products</p>
                            <div class="navbar-dropdown is-boxed">
                                @permission('products_list')
                                    <a class="navbar-item" href="{{ route('products_list') }}">
                                        List
                                    </a>
                                @endpermission
                                @permission('products_manage')
                                    <a class="navbar-item" href="{{ route('products_new') }}">
                                        Create
                                    </a>
                                @endpermission
                                @permission('products_adjust')
                                    <a class="navbar-item" href="{{ route('products_adjust') }}">
                                        Adjust
                                    </a>
                                @endpermission
                            </div>
                        </div>
                    @endpermission

                    @permission('activities')
                        <div class="navbar-item has-dropdown is-hoverable">
                            <p class="navbar-link {{ page('activities', @$page) }}"><i class="fas fa-calendar-alt"></i>&nbsp;Activities</p>
                            <div class="navbar-dropdown is-boxed">
                                @permission('activities_list')
                                    <a class="navbar-item" href="{{ route('activities_list') }}">
                                        List
                                    </a>
                                @endpermission
                                @permission('activities_manage')
                                    <a class="navbar-item" href="{{ route('activities_new') }}">
                                        Create
                                    </a>
                                @endpermission
                            </div>
                        </div>
                    @endpermission

                    @permission('orders_list')
                        <a class="navbar-item {{ page('orders', @$page) }}" href="{{ route('orders_list') }}">
                            <i class="fas fa-shopping-basket"></i>&nbsp;Orders
                        </a>
                    @endpermission

                    @permission('statistics')
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
                            @permission('settings')
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
