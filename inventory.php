<?php
$pageTitle = "JMF 509 Warehouse - Inventory";
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
$products = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
  if (isset($_POST['update_inventory'], $_POST['stock']) && is_array($_POST['stock'])) {
    $updated = 0;
    foreach ($_POST['stock'] as $productId => $stock) {
      $productId = filter_var($productId, FILTER_VALIDATE_INT);
      $stock = filter_var($stock, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
      if ($productId && $stock !== false) {
        try {
          $stmt = $db->getPDO()->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
          if ($stmt->execute([$stock, $productId])) $updated++;
        } catch (Exception $e) { /* skip */ }
      }
    }
    $message = "$updated product(s) updated.";
  }
}

try {
  $stmt = $db->getPDO()->query("SELECT id, name, category, price, COALESCE(stock_quantity, 0) AS current_stock FROM products ORDER BY category, name");
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $message = "Error loading inventory.";
}

$totalProducts = count($products);
$lowStock = count(array_filter($products, fn($p) => $p['current_stock'] > 0 && $p['current_stock'] < 10));
$outOfStock = count(array_filter($products, fn($p) => (int)$p['current_stock'] === 0));
?>

<main>
  <h2>Inventory Management</h2>
  <?php if ($message): ?>
    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <div class="inventory-summary">
    <div class="summary-box"><h3><?php echo $totalProducts; ?></h3><p>Total products</p></div>
    <div class="summary-box low"><h3><?php echo $lowStock; ?></h3><p>Low stock</p></div>
    <div class="summary-box out"><h3><?php echo $outOfStock; ?></h3><p>Out of stock</p></div>
  </div>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
    <table class="inventory-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Category</th>
          <th>Price</th>
          <th>Current stock</th>
          <th>New stock</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo htmlspecialchars($p['category']); ?></td>
            <td>$<?php echo number_format((float)$p['price'], 2); ?></td>
            <td><?php echo (int)$p['current_stock']; ?></td>
            <td><input type="number" name="stock[<?php echo (int)$p['id']; ?>]" value="<?php echo (int)$p['current_stock']; ?>" min="0" style="width: 80px;"></td>
            <td>
              <?php if ((int)$p['current_stock'] === 0): ?>
                <span class="status-out">Out of stock</span>
              <?php elseif ((int)$p['current_stock'] < 10): ?>
                <span class="status-low">Low stock</span>
              <?php else: ?>
                <span class="status-ok">In stock</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p><button type="submit" name="update_inventory" class="update-button">Update inventory</button></p>
  </form>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
