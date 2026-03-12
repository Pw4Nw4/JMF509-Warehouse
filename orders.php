<?php
$pageTitle = "JMF 509 Warehouse - Orders";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/database.php';

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
  header("Location: index.php");
  exit;
}

require_once __DIR__ . '/Extras/header.php';

$db = Database::getInstance();
$orders = [];
try {
  $stmt = $db->getPDO()->prepare("SELECT * FROM orders WHERE user_email = ? ORDER BY created_at DESC");
  $stmt->execute([$_SESSION['email']]);
  $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $error = "Could not load orders.";
}
?>

<main>
  <h2>Your Orders</h2>
  <?php if (!empty($error)): ?>
    <div class="alert"><?php echo htmlspecialchars($error); ?></div>
  <?php elseif (empty($orders)): ?>
    <p>You have no orders yet.</p>
    <p><a href="items.php">Shop now</a></p>
  <?php else: ?>
    <div class="orders-list">
      <?php foreach ($orders as $order): ?>
        <div class="order-item">
          <h4>Order #<?php echo (int)$order['id']; ?></h4>
          <p>Date: <?php echo htmlspecialchars(date('M j, Y', strtotime($order['created_at']))); ?></p>
          <p>Total: $<?php echo number_format((float)$order['total'], 2); ?></p>
          <p>Status: <?php echo htmlspecialchars(ucfirst($order['status'] ?? 'pending')); ?></p>
          <?php if (!empty($order['shipping_destination'])): ?>
            <p>Destination: <?php echo htmlspecialchars($order['shipping_destination']); ?></p>
          <?php endif; ?>
          <?php if (!empty($order['delivery_type'])): ?>
            <p>Delivery: <?php echo htmlspecialchars($order['delivery_type']); ?></p>
          <?php endif; ?>
          <?php if (!empty($order['recipient_name'])): ?>
            <p>Recipient: <?php echo htmlspecialchars($order['recipient_name']); ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
