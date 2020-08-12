@php

use \App\Roles;
@endphp
<nav class="navbar has-shadow">
    <div class="container">
        <div class="navbar-brand">
            <b class="navbar-item">tabReborn</b>
        </div>
        <div class="navbar-menu">
            <div class="navbar-start">
                @auth
                    @if (Roles::canViewPage(Auth::user()->role, 'cashier'))
                        <a class="navbar-item @if(isset($page) && $page == 'cashier') is-active @endif" href="{{ route('index') }}">
                            <i class="fas fa-money-bill-wave-alt"></i>&nbsp;Cashier
                        </a>
                    @endif
                    @if (Roles::canViewPage(Auth::user()->role, 'users_list'))
                        <div class="navbar-item has-dropdown is-hoverable">
                            <p class="navbar-link"><i class="fas fa-users"></i>&nbsp;Users</p>
                            <div class="navbar-dropdown is-boxed">
                                <a class="navbar-item" href="{{ route('users_list') }}">
                                    List
                                </a>
                                @if (Roles::canViewPage(Auth::user()->role, 'users_new'))
                                    <a class="navbar-item" href="{{ route('users_new') }}">
                                        Create
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if (Roles::canViewPage(Auth::user()->role, 'products_list'))
                        <div class="navbar-item has-dropdown is-hoverable">
                            <p class="navbar-link"><i class="fas fa-tag"></i>&nbsp;Products</p>
                            <div class="navbar-dropdown is-boxed">
                                <a class="navbar-item" href="{{ route('products_list') }}">
                                    List
                                </a>
                                @if (Roles::canViewPage(Auth::user()->role, 'products_new'))
                                    <a class="navbar-item" href="{{ route('products_new') }}">
                                        Create
                                    </a>
                                @endif
                                @if (Roles::canViewPage(Auth::user()->role, 'products_adjust'))
                                    <a class="navbar-item" href="{{ route('products_adjust') }}">
                                        Adjust
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if (Roles::canViewPage(Auth::user()->role, 'orders_list'))
                        <a class="navbar-item @if(isset($page) && $page == 'orders') is-active @endif" href="{{ route('orders_list') }}">
                            <i class="fas fa-shopping-basket"></i>&nbsp;Orders
                        </a>
                    @endif
                    @if (Roles::canViewPage(Auth::user()->role, 'statistics'))
                        <a class="navbar-item @if(isset($page) && $page == 'statistics') is-active @endif" href="{{ route('statistics') }}">
                            <i class="fas fa-chart-pie"></i>&nbsp;Statistics
                        </a>     
                    @endif
                @endauth
            </div>

            <div class="navbar-end">
                @auth
                    <div class="navbar-item">
                        <div class="field is-grouped">
                            @if (Roles::canViewPage(Auth::user()->role, 'settings'))
                                <p class="control">
                                    <a class="button is-warning" href="{{ route('settings') }}">
                                        <span class="icon">
                                            <i class="fas fa-cogs"></i>
                                        </span>
                                    </a>
                                </p>
                            @endif
                            <p class="control">
                                <a class="button is-primary" href="{{ route('logout') }}">
                                    <span class="icon">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </span>
                                </a>
                            </p>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>