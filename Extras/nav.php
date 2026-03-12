<?php
require_once __DIR__ . '/Security.php';

function navIsActive($page, $currentPage) {
    return basename($page) === $currentPage ? ' class="active"' : '';
}
?>
<nav>
  <ul>
    <li><a href="index.php"<?php echo navIsActive('index.php', $currentPage); ?>>Home</a></li>
    <?php if ($login): ?>
      <li><a href="items.php"<?php echo navIsActive('items.php', $currentPage); ?>>Shop</a></li>
      <li><a href="search.php"<?php echo navIsActive('search.php', $currentPage); ?>>Search</a></li>
      <li><a href="cart.php"<?php echo navIsActive('cart.php', $currentPage); ?>>Cart <span id="cart-count">0</span></a></li>
      <li><a href="orders.php"<?php echo navIsActive('orders.php', $currentPage); ?>>Orders</a></li>
      <li><a href="profile.php"<?php echo navIsActive('profile.php', $currentPage); ?>>Profile</a></li>
      <?php
      $adminEmailsList = defined('ADMIN_EMAILS') ? array_map('trim', explode(',', ADMIN_EMAILS)) : [];
      if (!empty($_SESSION['email']) && !empty($adminEmailsList) && in_array($_SESSION['email'], $adminEmailsList)): ?>
        <li><a href="admin.php"<?php echo navIsActive('admin.php', $currentPage); ?>>Admin</a></li>
        <li><a href="inventory.php"<?php echo navIsActive('inventory.php', $currentPage); ?>>Inventory</a></li>
        <li><a href="shipments.php"<?php echo navIsActive('shipments.php', $currentPage); ?>>Shipments</a></li>
      <?php endif; ?>
      <li>
        <form method="post" action="Extras/logout.php" style="display: inline;">
          <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
          <button type="submit" class="nav-logout">Log Out</button>
        </form>
      </li>
    <?php else: ?>
      <li><a href="register.php"<?php echo navIsActive('register.php', $currentPage); ?>>Register</a></li>
      <li><a href="login.php"<?php echo navIsActive('login.php', $currentPage); ?>>Log In</a></li>
    <?php endif; ?>
  </ul>
</nav>
