<?php
$pageTitle = "AyitiCo - Register";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/header.php';
require_once __DIR__ . '/Extras/Security.php';

if ($login) {
  header("Location: index.php");
  exit;
}

$message = '';
$messageClass = 'alert';
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $message = "Invalid request.";
  } else {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
      $message = "All fields required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $message = "Invalid email.";
    } elseif (strlen($password) < 8) {
      $message = "Password must be at least 8 characters.";
    } elseif ($password !== $password_confirm) {
      $message = "Passwords do not match.";
    } elseif (!$pdo) {
      $message = "Database unavailable.";
    } else {
      try {
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ? OR username = ? LIMIT 1");
        $stmt->execute([$email, $username]);
        if ($stmt->fetchColumn()) {
          $message = "Username or email already in use.";
        } else {
          $hash = password_hash($password, PASSWORD_DEFAULT);
          if ($hash === false) {
            $message = "Could not process password.";
          } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
            if ($stmt->execute([$username, $email, $hash])) {
              require_once __DIR__ . '/Extras/EmailService.php';
              EmailService::sendWelcomeEmail($email, $username);
              header("Location: login.php?registered=success");
              exit;
            }
            $message = "Registration failed.";
          }
        }
      } catch (PDOException $e) {
        $message = "Database error. Try again.";
        error_log("Register: " . $e->getMessage());
      }
    }
  }
}
?>

<main>
  <h2>Register</h2>
  <?php if ($message): ?>
    <div class="<?php echo htmlspecialchars($messageClass); ?>"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>
  <form method="post" action="register.php" class="auth-form">
    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username); ?>">
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
    </div>
    <div class="form-group">
      <label for="password">Password (min 8 characters)</label>
      <input type="password" id="password" name="password" required>
    </div>
    <div class="form-group">
      <label for="password_confirm">Confirm password</label>
      <input type="password" id="password_confirm" name="password_confirm" required>
    </div>
    <button type="submit" class="auth-button">Register</button>
  </form>
  <p style="text-align: center; margin-top: 1rem;">Already have an account? <a href="login.php">Log in</a></p>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
