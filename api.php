<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/database.php';
require_once __DIR__ . '/Extras/Security.php';

if (session_status() == PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

if ($action === 'cart_count') {
  try {
    $stmt = $db->getPDO()->prepare("SELECT COALESCE(SUM(quantity), 0) AS total FROM carts WHERE user_email = ?");
    $stmt->execute([$_SESSION['email']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['count' => (int)$row['total']]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
  }
  exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
