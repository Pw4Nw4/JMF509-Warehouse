<?php
$pageTitle = "JMF 509 Warehouse - Profile";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/database.php';

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
  header("Location: index.php");
  exit;
}

require_once __DIR__ . '/Extras/header.php';

require_once __DIR__ . '/Extras/nav.php';

$email = htmlspecialchars($_SESSION['email'] ?? '');
$username = htmlspecialchars($_SESSION['username'] ?? '');
?>

<main>
  <h2>Your Profile</h2>
  <div class="profile-overview">
    <div class="profile-info">
      <p><strong>Username:</strong> <?php echo $username; ?></p>
      <p><strong>Email:</strong> <?php echo $email; ?></p>
      <p><a href="orders.php">View your orders</a></p>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
