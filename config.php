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

// Categories for Haiti-focused store
define('CATEGORIES', [
    'Solar products',
    'Phones & electronics',
    'Survival supplies',
    'Essential household items'
]);
?>
