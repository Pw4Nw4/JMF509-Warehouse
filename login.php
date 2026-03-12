<?php
$pageTitle = "JMF 509 Warehouse - Log In";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/header.php';
require_once __DIR__ . '/Extras/Security.php';
require_once __DIR__ . '/Extras/ErrorHandler.php';

if ($login) {
  header("Location: index.php");
  exit;
}

$message = '';
$messageClass = 'alert';

if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
  $message = "Registration successful. Please log in.";
  $messageClass = "alert-success";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $message = "Invalid request. Try again.";
  } elseif (!Security::rateLimitCheck('login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
    $message = "Too many attempts. Wait a few minutes.";
  } elseif (!$pdo) {
    $message = "Database unavailable.";
  } else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $validation = ErrorHandler::validateInput(['email' => $email, 'password' => $password], [
      'email' => ['required' => true, 'type' => 'email'],
      'password' => ['required' => true]
    ]);
    if (!empty($validation)) {
      $message = implode('. ', $validation);
    } else {
      try {
        $stmt = $pdo->prepare("SELECT username, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
          session_regenerate_id(true);
          $_SESSION['loggedin'] = true;
          $_SESSION['username'] = $user['username'];
          $_SESSION['email'] = $user['email'];
          header("Location: index.php");
          exit;
        }
        $message = "Invalid email or password.";
      } catch (PDOException $e) {
        $message = "Login error. Try again.";
        error_log("Login: " . $e->getMessage());
      }
    }
  }
}
?>

<main>
  <h2>Log In</h2>
  <?php if ($message): ?>
    <div class="<?php echo htmlspecialchars($messageClass); ?>"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>
  <form method="post" action="login.php" class="auth-form">
    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="auth-button">Log In</button>
  </form>
  <p style="text-align: center; margin-top: 1rem;">No account? <a href="register.php">Register</a></p>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
