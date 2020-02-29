<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor02" aria-controls="navbarColor02" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <?php
            if (Auth::check()) {
            ?>
                <li class="nav-item">
                    <a href="/" class="nav-link">Cashier</a>
                </li>
                <?php
                if (Auth::user()->role == "administrator") {
                ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Users
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="/users">List</a>
                            <a class="dropdown-item" href="/users/new">Create</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Products
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="/products">List</a>
                            <a class="dropdown-item" href="/products/new">Create</a>
                        </div>
                    </li>
                    <li class="nav-item">
                    <a href="/orders" class="nav-link">Orders</a>
                </li>
                <?php
                }
                ?>
                <li class="nav-item">
                    <a href="/logout" class="nav-link">Logout</a>
                </li>
            <?php
            } else {
            ?>
                <li class="nav-item">
                    <a href="/login" class="nav-link">Login</a>
                </li>
            <?php } ?>
        </ul>
    </div>
</nav>