<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/Extras/database.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: index.php");
  exit;
}
if ($pdo === null) {
  header("Location: cart.php?status=error");
  exit;
}

$userEmail = $_SESSION['email'];
$cartId = isset($_GET['cart_id']) ? filter_input(INPUT_GET, 'cart_id', FILTER_VALIDATE_INT) : 0;

if (!$cartId) {
  header("Location: cart.php?status=invalid");
  exit;
}

try {
  $stmt = $pdo->prepare("DELETE FROM carts WHERE cart_id = ? AND user_email = ?");
  $stmt->execute([$cartId, $userEmail]);
  header("Location: cart.php?status=removed");
  exit;
} catch (PDOException $e) {
  header("Location: cart.php?status=error");
  exit;
}
