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
<div class="container">
  <header>
    <h1><?php echo defined('BUSINESS_NAME') ? BUSINESS_NAME : 'JMF 509 Warehouse'; ?></h1>
    <p class="header-welcome">Welcome, <?php echo $displayName; ?>!</p>
  </header>
