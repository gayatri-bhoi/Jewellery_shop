<nav class="navbar navbar-expand navbar-light bg-navbar topbar mb-4 static-top">
  <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
    <i class="fa fa-bars"></i>
  </button>

  <!-- Topbar Search -->
  <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
    <div class="input-group">
      <input type="text" class="form-control bg-light border-0 small" placeholder="Search for products..." aria-label="Search">
      <div class="input-group-append">
        <button class="btn btn-primary" type="button">
          <i class="fas fa-search fa-sm"></i>
        </button>
      </div>
    </div>
  </form>

  <ul class="navbar-nav ml-auto">

    <!-- Cart Icon -->
    <li class="nav-item mx-1">
      <a class="nav-link" href="cart.php">
        <i class="fas fa-shopping-cart"></i>
        <span class="badge badge-danger badge-counter">3</span>
      </a>
    </li>

    <!-- Wishlist Icon -->
    <li class="nav-item mx-1">
      <a class="nav-link" href="wishlist.php">
        <i class="fas fa-heart"></i>
        <span class="badge badge-warning badge-counter">5</span>
      </a>
    </li>

    <div class="topbar-divider d-none d-sm-block"></div>

    <!-- User Info -->
    <li class="nav-item dropdown no-arrow">
      <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
        <img class="img-profile rounded-circle" src="../assets/img/user.png" style="max-width: 60px">
        <span class="ml-2 d-none d-lg-inline text-white small">User</span>
      </a>
      <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
        <a class="dropdown-item" href="orders.php">
          <i class="fas fa-box fa-sm fa-fw mr-2 text-gray-400"></i>
          My Orders
        </a>
        <a class="dropdown-item" href="account.php">
          <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
          Account Settings
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="../auth/logout.php">
          <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
          Logout
        </a>
      </div>
    </li>
  </ul>
</nav>
