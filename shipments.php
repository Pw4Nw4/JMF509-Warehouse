<?php
$pageTitle = "JMF 509 Warehouse - Shipments";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/database.php';
require_once __DIR__ . '/Extras/Security.php';

if (session_status() == PHP_SESSION_NONE) session_start();
$adminEmails = defined('ADMIN_EMAILS') ? array_map('trim', explode(',', ADMIN_EMAILS)) : [];
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || empty($adminEmails) || !in_array($_SESSION['email'] ?? '', $adminEmails)) {
  header("Location: index.php");
  exit;
}

require_once __DIR__ . '/Extras/header.php';

$db = Database::getInstance();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
  if (isset($_POST['add_incoming'])) {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $notes = trim($_POST['notes'] ?? '');
    if ($productId && $quantity) {
      try {
        $stmt = $db->getPDO()->prepare("INSERT INTO incoming_shipments (product_id, quantity, notes, received_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$productId, $quantity, $notes]);
        $stmt = $db->getPDO()->prepare("UPDATE products SET stock_quantity = COALESCE(stock_quantity, 0) + ? WHERE id = ?");
        $stmt->execute([$quantity, $productId]);
        $message = "Incoming shipment recorded. Stock updated.";
      } catch (Exception $e) {
        $message = "Failed to record shipment.";
      }
    } else {
      $message = "Select product and enter quantity.";
    }
  }
}

$incoming = [];
$outgoing = [];
try {
  $stmt = $db->getPDO()->query("SELECT s.*, p.name AS product_name FROM incoming_shipments s JOIN products p ON s.product_id = p.id ORDER BY s.received_at DESC LIMIT 50");
  $incoming = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $stmt = $db->getPDO()->query("SELECT id, user_email, total, status, shipping_destination, delivery_type, recipient_name, created_at FROM orders ORDER BY created_at DESC LIMIT 30");
  $outgoing = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $message = "Error loading data.";
}

$products = [];
try {
  $products = $db->fetchProducts(null, null);
} catch (Exception $e) {}

require_once __DIR__ . '/Extras/nav.php';
?>

<main>
  <h2>Shipments &amp; Logistics</h2>
  <?php if ($message): ?>
    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <h3>Record incoming shipment</h3>
  <form method="post" class="shipment-form">
    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
    <div class="form-group">
      <label>Product</label>
      <select name="product_id" required>
        <option value="">Select product</option>
        <?php foreach ($products as $p): ?>
          <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Quantity received</label>
      <input type="number" name="quantity" min="1" required>
    </div>
    <div class="form-group">
      <label>Notes</label>
      <input type="text" name="notes" placeholder="Optional">
    </div>
    <button type="submit" name="add_incoming" class="update-button">Record incoming shipment</button>
  </form>

  <h3>Recent incoming shipments</h3>
  <?php if (empty($incoming)): ?>
    <p>No incoming shipments recorded yet.</p>
  <?php else: ?>
    <table class="inventory-table">
      <thead>
        <tr><th>Date</th><th>Product</th><th>Quantity</th><th>Notes</th></tr>
      </thead>
      <tbody>
        <?php foreach ($incoming as $row): ?>
          <tr>
            <td><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($row['received_at']))); ?></td>
            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
            <td><?php echo (int)$row['quantity']; ?></td>
            <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <h3>Outgoing orders (recent)</h3>
  <?php if (empty($outgoing)): ?>
    <p>No orders yet.</p>
  <?php else: ?>
    <table class="inventory-table">
      <thead>
        <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Destination</th><th>Delivery</th><th>Recipient</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php foreach ($outgoing as $row): ?>
          <tr>
            <td><?php echo (int)$row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
            <td>$<?php echo number_format((float)$row['total'], 2); ?></td>
            <td><?php echo htmlspecialchars($row['shipping_destination'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($row['delivery_type'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($row['recipient_name'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars(date('M j, Y', strtotime($row['created_at']))); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
