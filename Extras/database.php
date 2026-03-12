<?php
try {
    require_once __DIR__ . '/ErrorHandler.php';
} catch (Exception $e) {
    error_log('Failed to load ErrorHandler: ' . $e->getMessage());
    throw new Exception('Required dependencies not available');
}

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $envFile = __DIR__ . '/../.env';
            if (!file_exists($envFile) || !is_readable($envFile)) {
                throw new Exception('Environment file not found or not readable');
            }
            $env = parse_ini_file($envFile);
            if ($env === false || !isset($env['DB_HOST'], $env['DB_NAME'], $env['DB_USER'], $env['DB_PASS'])) {
                throw new Exception('Missing required database configuration');
            }
            $charset = $env['DB_CHARSET'] ?? 'utf8mb4';
            $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=$charset";
            $this->pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (Exception $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed');
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPDO() {
        return $this->pdo;
    }

    public function fetchProducts($limit = null, $category = null) {
        try {
            $sql = "SELECT id, name, price, image, description, category, stock_quantity FROM products";
            $params = [];
            if ($category) {
                $sql .= " WHERE category = ?";
                $params[] = $category;
            }
            $sql .= " ORDER BY created_at DESC";
            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching products: " . $e->getMessage());
            return [];
        }
    }

    public function getCartItems($userEmail) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT c.cart_id, c.product_id, c.quantity, c.added_at,
                        p.name AS product_name, p.price AS product_price, p.image AS product_image,
                        COALESCE(p.stock_quantity, 0) AS product_stock
                 FROM carts c
                 JOIN products p ON c.product_id = p.id
                 WHERE c.user_email = ?
                 ORDER BY c.added_at DESC"
            );
            $stmt->execute([$userEmail]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception(ErrorHandler::handleDatabaseError($e, "Error retrieving cart items"));
        }
    }
}

$pdo = null;
try {
    $pdo = Database::getInstance()->getPDO();
} catch (Exception $e) {
    $pdo = null;
}
?>
