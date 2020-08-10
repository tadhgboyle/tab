<nav class="navbar has-shadow">
    <div class="navbar-brand">
        <b class="navbar-item">tabReborn</b>
        <div class="navbar-burger burger" data-target="navbarData">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div id="navbarExample" class="navbar-menu">
        <div class="navbar-start">
        <a class="navbar-item @if(isset($page) && $page == 'cashier') is-active @endif" href="{{ route('index') }}">
                <i class="fas fa-money-bill-wave-alt"></i>&nbsp;Cashier
            </a>
            <div class="navbar-item has-dropdown is-hoverable">
                <p class="navbar-link"><i class="fas fa-users"></i>&nbsp;Users</p>
                <div class="navbar-dropdown is-boxed">
                    <a class="navbar-item" href="{{ route('users') }}">
                        List
                    </a>
                    <a class="navbar-item" href="{{ route('users_new') }}">
                        Create
                    </a>
                </div>
            </div>
            <div class="navbar-item has-dropdown is-hoverable">
                <p class="navbar-link"><i class="fas fa-tag"></i>&nbsp;Products</p>
                <div class="navbar-dropdown is-boxed">
                    <a class="navbar-item" href="{{ route('products') }}">
                        List
                    </a>
                    <a class="navbar-item" href="{{ route('products_new') }}">
                        Create
                    </a>
                    <a class="navbar-item" href="{{ route('products_adjust') }}">
                        Adjust
                    </a>
                </div>
            </div>
            <a class="navbar-item @if(isset($page) && $page == 'orders') is-active @endif" href="{{ route('orders') }}">
                <i class="fas fa-shopping-basket"></i>&nbsp;Orders
            </a>
            <a class="navbar-item @if(isset($page) && $page == 'statistics') is-active @endif" href="{{ route('statistics') }}">
                <i class="fas fa-chart-pie"></i>&nbsp;Statistics
            </a>
        </div>

        <div class="navbar-end">
            <div class="navbar-item">
                <div class="field is-grouped">
                    <p class="control">
                    <a class="button is-warning" href="{{ route('settings') }}">
                            <span class="icon">
                                <i class="fas fa-cogs"></i>
                            </span>
                        </a>
                    </p>
                    <p class="control">
                    <a class="button is-primary" href="{{ route('logout') }}">
                            <span class="icon">
                                <i class="fas fa-sign-out-alt"></i>
                            </span>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</nav>