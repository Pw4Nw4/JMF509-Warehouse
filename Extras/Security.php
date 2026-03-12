<?php
class Security {
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function rateLimitCheck($key, $maxAttempts = 5, $timeWindow = 300) {
        $attempts = $_SESSION['rate_limit'][$key] ?? [];
        $now = time();
        $attempts = array_filter($attempts, function($time) use ($now, $timeWindow) {
            return ($now - $time) < $timeWindow;
        });
        if (count($attempts) >= $maxAttempts) return false;
        $attempts[] = $now;
        $_SESSION['rate_limit'][$key] = $attempts;
        return true;
    }
}
?>
