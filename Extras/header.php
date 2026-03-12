<?php
require_once __DIR__ . '/../config.php';

if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    session_start();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

require_once __DIR__ . '/database.php';

date_default_timezone_set('America/New_York');

$dbConnected = ($pdo !== null);
$login = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$displayName = $login ? htmlspecialchars($_SESSION['email'] ?? 'User') : 'Guest';
$currentPage = basename($_SERVER['SCRIPT_FILENAME']);

if (!isset($pageTitle)) {
    $pageTitle = defined('BUSINESS_NAME') ? BUSINESS_NAME : 'JMF 509 Warehouse';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php require_once __DIR__ . '/Security.php'; echo Security::generateCSRFToken(); ?>">
  <link rel="stylesheet" href="jmf509_styles.css">
  <script src="app.js" defer></script>
</head>
<body>
<header class="site-header">
  <div class="header-tier1">
    <div class="header-logo">
      <a href="index.php"><?php echo defined('BUSINESS_NAME') ? BUSINESS_NAME : 'JMF 509 Warehouse'; ?></a>
    </div>
    <div class="header-search">
      <form method="get" action="search.php">
        <input type="search" name="q" placeholder="Search JMF 509 Warehouse" aria-label="Search">
        <button type="submit" aria-label="Search">Search</button>
      </form>
    </div>
    <div class="header-right">
      <?php if ($login): ?>
        <a href="profile.php"><span>Hello, <?php echo $displayName; ?></span><span>Account</span></a>
        <a href="orders.php"><span>Returns</span><span>& Orders</span></a>
        <a href="cart.php" class="header-cart"><span id="cart-count" class="cart-badge">0</span> Cart</a>
      <?php else: ?>
        <a href="login.php"><span>Hello, Sign in</span><span>Account</span></a>
        <a href="register.php"><span>New customer?</span><span>Register</span></a>
        <a href="cart.php" class="header-cart"><span id="cart-count" class="cart-badge">0</span> Cart</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="header-tier2">
    <?php require_once __DIR__ . '/nav.php'; ?>
  </div>
</header>
<div class="container">
