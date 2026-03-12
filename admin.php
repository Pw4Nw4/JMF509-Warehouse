<?php
$pageTitle = "JMF 509 Warehouse - Admin";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/header.php';
require_once __DIR__ . '/Extras/database.php';
require_once __DIR__ . '/Extras/Security.php';

$adminEmails = defined('ADMIN_EMAILS') ? array_map('trim', explode(',', ADMIN_EMAILS)) : [];
if (!$login || empty($adminEmails) || !in_array($_SESSION['email'], $adminEmails)) {
  header("Location: index.php");
  exit;
}

$db = Database::getInstance();
$stats = ['users' => 0, 'products' => 0, 'orders' => 0, 'revenue' => 0];
$message = '';

try {
  $stmt = $db->getPDO()->query("SELECT COUNT(*) AS c FROM users");
  $stats['users'] = (int)$stmt->fetch()['c'];
  $stmt = $db->getPDO()->query("SELECT COUNT(*) AS c FROM products");
  $stats['products'] = (int)$stmt->fetch()['c'];
  $stmt = $db->getPDO()->query("SELECT COUNT(*) AS c FROM orders");
  $stats['orders'] = (int)$stmt->fetch()['c'];
  $stmt = $db->getPDO()->query("SELECT COALESCE(SUM(total), 0) AS s FROM orders");
  $stats['revenue'] = (float)$stmt->fetch()['s'];
} catch (Exception $e) {
  $message = "Error loading stats.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
  $name = trim($_POST['name'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $category = trim($_POST['category'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $stock = (int)($_POST['stock'] ?? 0);
  if ($name && $price > 0 && $category) {
    try {
      $stmt = $db->getPDO()->prepare("INSERT INTO products (name, price, category, description, stock_quantity, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
      $stmt->execute([$name, $price, $category, $description, $stock]);
      $message = "Product added.";
    } catch (Exception $e) {
      $message = "Failed to add product.";
    }
  } else {
    $message = "Fill name, price, and category.";
  }
}

require_once __DIR__ . '/Extras/nav.php';
?>

<main>
  <h2>Admin Dashboard</h2>
  <?php if ($message): ?>
    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <div class="admin-stats">
    <div class="stat-box"><h3><?php echo $stats['users']; ?></h3><p>Users</p></div>
    <div class="stat-box"><h3><?php echo $stats['products']; ?></h3><p>Products</p></div>
    <div class="stat-box"><h3><?php echo $stats['orders']; ?></h3><p>Orders</p></div>
    <div class="stat-box"><h3>$<?php echo number_format($stats['revenue'], 2); ?></h3><p>Revenue</p></div>
  </div>

  <div class="admin-links">
    <a href="inventory.php" class="admin-link">📦 Inventory</a>
    <a href="shipments.php" class="admin-link">🚚 Shipments</a>
  </div>

  <h3>Add Product</h3>
  <form method="post" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
    <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
    <div class="form-group"><label>Price</label><input type="number" name="price" step="0.01" min="0" required></div>
    <div class="form-group"><label>Category</label>
      <select name="category" required>
        <?php foreach (CATEGORIES as $c): ?>
          <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label>Initial stock</label><input type="number" name="stock" min="0" value="0"></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
    <button type="submit" class="update-button">Add Product</button>
  </form>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
