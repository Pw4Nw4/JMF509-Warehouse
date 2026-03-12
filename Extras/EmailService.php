<?php
class EmailService {
    private static function getEnv() {
        static $env = null;
        if ($env === null) {
            $envFile = __DIR__ . '/../.env';
            if (file_exists($envFile) && is_readable($envFile)) {
                $env = parse_ini_file($envFile);
                if ($env === false) $env = [];
            } else {
                $env = [];
            }
        }
        return $env;
    }

    private static function isMailEnabled() {
        $env = self::getEnv();
        return !empty($env['MAIL_ENABLED']) && ($env['MAIL_ENABLED'] === '1' || $env['MAIL_ENABLED'] === 'true');
    }

    private static function getFrom() {
        $env = self::getEnv();
        $email = isset($env['MAIL_FROM_EMAIL']) ? trim($env['MAIL_FROM_EMAIL']) : 'noreply@ayitico.com';
        $name = isset($env['MAIL_FROM_NAME']) ? trim($env['MAIL_FROM_NAME']) : 'AyitiCo';
        return [$email, $name];
    }

    private static function send($to, $subject, $bodyHtml) {
        $to = filter_var($to, FILTER_SANITIZE_EMAIL);
        if (!$to) return false;
        list($fromEmail, $fromName) = self::getFrom();
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'Reply-To: ' . $fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];
        if (self::isMailEnabled()) {
            return @mail($to, $subject, $bodyHtml, implode("\r\n", $headers));
        }
        error_log("EMAIL (not sent): TO=$to, SUBJECT=" . $subject);
        return true;
    }

    public static function sendWelcomeEmail($userEmail, $userName) {
        $subject = 'Welcome to AyitiCo';
        $name = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
        $body = "<html><body><h2>Welcome, $name!</h2><p>Thank you for registering with AyitiCo. You can now shop and place orders.</p></body></html>";
        return self::send($userEmail, $subject, $body);
    }

    public static function sendOrderConfirmation($userEmail, $orderId, $total, $destination = '') {
        $subject = "Order Confirmation #$orderId - AyitiCo";
        $totalFmt = htmlspecialchars(number_format((float)$total, 2), ENT_QUOTES, 'UTF-8');
        $body = "<html><body><h2>Order #$orderId received</h2><p>Total: \$$totalFmt</p><p>We'll process your order shortly.</p></body></html>";
        return self::send($userEmail, $subject, $body);
    }
}
?>
