<?php
class SimpleCache {
    private static $cache = [];
    private static $cacheDir = null;

    public static function init() {
        self::$cacheDir = __DIR__ . '/../cache/';
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }

    public static function get($key, $default = null) {
        if (isset(self::$cache[$key])) return self::$cache[$key];
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $file = self::$cacheDir . hash('sha256', $safeKey) . '.cache';
        if (file_exists($file) && is_readable($file)) {
            $data = @json_decode(file_get_contents($file), true);
            if ($data && isset($data['expires']) && $data['expires'] > time()) {
                self::$cache[$key] = $data['value'];
                return $data['value'];
            }
            @unlink($file);
        }
        return $default;
    }

    public static function set($key, $value, $ttl = 300) {
        self::init();
        self::$cache[$key] = $value;
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $file = self::$cacheDir . hash('sha256', $safeKey) . '.cache';
        @file_put_contents($file, json_encode(['value' => $value, 'expires' => time() + $ttl]), LOCK_EX);
    }
}
?>
