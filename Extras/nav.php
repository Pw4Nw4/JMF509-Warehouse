<?php
require_once __DIR__ . '/Security.php';

function navIsActive($page, $currentPage) {
    return basename($page) === $currentPage ? ' class="active"' : '';
}
?>
<div class="nav-wrapper">
<button type="button" class="nav-toggle" aria-label="Toggle menu" aria-expanded="false">
  <span></span><span></span><span></span>
</button>
<nav>
  <ul>
    <li><a href="index.php"<?php echo navIsActive('index.php', $currentPage); ?>>All</a></li>
    <li><a href="items.php"<?php echo navIsActive('items.php', $currentPage); ?>>Shop</a></li>
    <?php if ($login): ?>
      <?php foreach (defined('CATEGORIES') ? CATEGORIES : [] as $cat): ?>
        <li><a href="items.php?category=<?php echo urlencode($cat); ?>"<?php echo (basename($_SERVER['SCRIPT_FILENAME']) === 'items.php' && isset($_GET['category']) && $_GET['category'] === $cat) ? ' class="active"' : ''; ?>><?php echo htmlspecialchars($cat); ?></a></li>
      <?php endforeach; ?>
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
</div>
