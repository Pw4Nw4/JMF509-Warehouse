<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/database.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: index.php");
  exit;
}
if ($pdo === null) {
  header("Location: description.php?item=" . (int)($_POST['product_id'] ?? 0) . "&cart_add_status=db_error");
  exit;
}

$userEmail = $_SESSION['email'];
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'], $_POST['add_to_cart_action'])) {
  header("Location: index.php");
  exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = 1;

if (!$productId || $productId <= 0) {
  header("Location: description.php?item=" . ($productId ?: 0) . "&cart_add_status=invalid");
  exit;
}

try {
  $stockStmt = $pdo->prepare("SELECT COALESCE(stock_quantity, 0) AS stock FROM products WHERE id = ?");
  $stockStmt->execute([$productId]);
  $productStock = $stockStmt->fetch(PDO::FETCH_ASSOC);
  $available = $productStock ? (int)$productStock['stock'] : 0;

  $check = $pdo->prepare("SELECT cart_id, quantity FROM carts WHERE user_email = ? AND product_id = ?");
  $check->execute([$userEmail, $productId]);
  $existing = $check->fetch(PDO::FETCH_ASSOC);
  $requestedQty = $existing ? min($existing['quantity'] + $quantity, 10) : $quantity;

  if ($requestedQty > $available) {
    header("Location: description.php?item=" . $productId . "&cart_add_status=out_of_stock");
    exit;
  }

  if ($existing) {
    $stmt = $pdo->prepare("UPDATE carts SET quantity = ?, added_at = NOW() WHERE cart_id = ?");
    $stmt->execute([$requestedQty, $existing['cart_id']]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO carts (user_email, product_id, quantity, added_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$userEmail, $productId, $quantity]);
  }
  header("Location: description.php?item=" . $productId . "&cart_add_status=success");
  exit;
} catch (PDOException $e) {
  error_log("Add to cart: " . $e->getMessage());
  header("Location: description.php?item=" . $productId . "&cart_add_status=db_error");
  exit;
}
