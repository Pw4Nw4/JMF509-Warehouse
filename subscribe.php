<?php
$pageTitle = "AyitiCo - Subscribe";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/header.php';

$message = '';
$messageClass = 'alert';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    
    if ($email) {
        // For now, just show success - in production, you'd save to database or send to email service
        $message = "Thank you for subscribing! You'll receive updates about special offers.";
        $messageClass = 'alert-success';
    } else {
        $message = "Please enter a valid email address.";
    }
}
?>

<main>
    <h2>Subscribe to Our Newsletter</h2>
    
    <?php if ($message): ?>
        <div class="<?php echo $messageClass; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <p>Stay updated with the latest products, special offers, and news from AyitiCo.</p>
    <p><a href="index.php">Return to Homepage</a></p>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>