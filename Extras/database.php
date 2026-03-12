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

    private function loadConfig() {
        $env = [];
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile) && is_readable($envFile)) {
            $parsed = parse_ini_file($envFile);
            if ($parsed) $env = $parsed;
        }
        return [
            'DB_HOST' => getenv('DB_HOST') ?: ($env['DB_HOST'] ?? null),
            'DB_NAME' => getenv('DB_NAME') ?: ($env['DB_NAME'] ?? null),
            'DB_USER' => getenv('DB_USER') ?: ($env['DB_USER'] ?? null),
            'DB_PASS' => getenv('DB_PASS') ?: ($env['DB_PASS'] ?? null),
            'DB_PORT' => getenv('DB_PORT') ?: ($env['DB_PORT'] ?? 5432),
        ];
    }

    private function __construct() {
        try {
            $env = $this->loadConfig();
            $host = $env['DB_HOST'] ?? null;
            $name = $env['DB_NAME'] ?? null;
            $user = $env['DB_USER'] ?? null;
            $pass = $env['DB_PASS'] ?? null;
            if (!$host || !$name || !$user || $pass === null) {
                throw new Exception('Missing required database configuration');
            }
            $port = $env['DB_PORT'] ?? 5432;
            $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode=require";
            $this->pdo = new PDO($dsn, $user, $pass, [
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
            // Check if using old schema (category column) or new schema (category_id with join)
            $hasCategoryColumn = false;
            try {
                $stmt = $this->pdo->query("SELECT category FROM products LIMIT 1");
                $hasCategoryColumn = true;
            } catch (Exception $e) {
                $hasCategoryColumn = false;
            }

            if ($hasCategoryColumn) {
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
            } else {
                // New schema with category_id
                $sql = "SELECT p.id, p.name, p.price, p.image_url as image, p.description, c.name as category, p.stock_quantity, p.created_at
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id";
                $params = [];
                if ($category) {
                    $sql .= " WHERE c.name = ?";
                    $params[] = $category;
                }
                $sql .= " ORDER BY p.created_at DESC";
                if ($limit) {
                    $sql .= " LIMIT " . (int)$limit;
                }
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }
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
