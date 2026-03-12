<?php
// AyitiCo - Haitian Marketplace Application Configuration
define('APP_NAME', 'AyitiCo');
define('APP_VERSION', '1.0');
define('DEBUG_MODE', false);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

define('SESSION_TIMEOUT', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('RATE_LIMIT_WINDOW', 300);

define('CACHE_ENABLED', true);
define('CACHE_TTL', 600);

define('MAX_IMAGE_SIZE', 2097152);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

define('PRODUCTS_PER_PAGE', 12);
define('FEATURED_PRODUCTS_COUNT', 4);

define('BUSINESS_NAME', 'AyitiCo');
define('SUPPORT_EMAIL', 'support@ayitico.com');
define('POWERED_BY', 'Product of F09 tech');

// Set to true when Stripe is configured (keys in .env); when false, US customers use "submit order, we'll contact you"
define('STRIPE_ENABLED', false);

// Admin: comma-separated emails that can access admin/inventory/shipments
define('ADMIN_EMAILS', 'admin@ayitico.com');

// Categories - loaded from database dynamically
function getCategories() {
    try {
        $env = [];
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
        }
        $host = $env['DB_HOST'] ?? '10.10.1.38';
        $name = $env['DB_NAME'] ?? 'ayitico_store';
        $user = $env['DB_USER'] ?? 'ayitico_user';
        $pass = $env['DB_PASS'] ?? 'Hfy74h5Vmty';
        $port = $env['DB_PORT'] ?? 5432;

        $dsn = "pgsql:host={$host};port={$port};dbname={$name}";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query("SELECT name FROM categories ORDER BY name");
        $cats = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $cats ?: [];
    } catch (Exception $e) {
        return ['Solar', 'Phones', 'Electronics', 'Survival', 'Essentials'];
    }
}

define('CATEGORIES', getCategories());
?>
