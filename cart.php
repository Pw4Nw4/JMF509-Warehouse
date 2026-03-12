<?php
$pageTitle = "JMF 509 Warehouse - Cart";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/database.php';

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
  header("Location: index.php");
  exit;
}

require_once __DIR__ . '/Extras/header.php';
require_once __DIR__ . '/Extras/ErrorHandler.php';

$db = Database::getInstance();
if (!$db->getPDO()) {
  echo "<main><div class='alert'>Database not available.</div></main>";
  require_once __DIR__ . '/Extras/footer.php';
  exit;
}

$userEmail = $_SESSION['email'];
$cartProducts = [];
$grandTotal = 0;
$message = '';
$messageClass = 'alert';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantities'], $_POST['quantities']) && is_array($_POST['quantities'])) {
  foreach ($_POST['quantities'] as $cartId => $qty) {
    $cartId = filter_var($cartId, FILTER_VALIDATE_INT);
    $qty = filter_var($qty, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]);
    if ($cartId && $qty !== false) {
      try {
        $stmt = $db->getPDO()->prepare("UPDATE carts SET quantity = ? WHERE cart_id = ? AND user_email = ?");
        $stmt->execute([$qty, $cartId, $userEmail]);
      } catch (PDOException $e) { /* ignore */ }
    }
  }
  $message = "Cart updated.";
  $messageClass = "alert-success";
}

try {
  $items = $db->getCartItems($userEmail);
  foreach ($items as $item) {
    $lineTotal = (float)$item['product_price'] * (int)$item['quantity'];
    $cartProducts[] = array_merge($item, ['line_total' => $lineTotal]);
    $grandTotal += $lineTotal;
  }
} catch (Exception $e) {
  $message = $e->getMessage();
}

if (isset($_GET['status'])) {
  $s = $_GET['status'];
  if ($s === 'added') { $message = "Item added to cart."; $messageClass = "alert-success"; }
  elseif ($s === 'removed') { $message = "Item removed."; $messageClass = "alert-success"; }
  elseif ($s === 'updated') { $message = "Cart updated."; $messageClass = "alert-success"; }
  else { $message = "Something went wrong."; }
}

require_once __DIR__ . '/Extras/nav.php';
?>

<main>
  <h2>Your Cart</h2>
  <?php if ($message): ?>
    <div class="<?php echo htmlspecialchars($messageClass); ?>"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <?php if (empty($cartProducts)): ?>
    <p>Your cart is empty.</p>
    <p><a href="items.php">Continue Shopping</a></p>
  <?php else: ?>
    <form method="POST" action="cart.php">
      <div class="cart-items-grid">
        <?php foreach ($cartProducts as $item): ?>
          <div class="cart-item">
            <?php
            if (!empty($item['product_image'])) {
              try {
                $imgData = base64_encode($item['product_image']);
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $imgMime = $finfo->buffer($item['product_image']);
                echo '<img src="data:'.$imgMime.';base64,'.$imgData.'" alt="" />';
              } catch (Exception $e) {
                echo '<img src="images/placeholder.jpg" alt="" />';
              }
            } else {
              echo '<img src="images/placeholder.jpg" alt="" />';
            }
            ?>
            <h4><a href="description.php?item=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['product_name']); ?></a></h4>
            <p class="price">$<?php echo number_format((float)$item['product_price'], 2); ?></p>
            <label>Qty: <input type="number" name="quantities[<?php echo $item['cart_id']; ?>]" value="<?php echo (int)$item['quantity']; ?>" min="1" max="10" style="width: 60px;"></label>
            <p>Line: $<?php echo number_format((float)$item['line_total'], 2); ?></p>
            <a href="remove_from_cart.php?cart_id=<?php echo $item['cart_id']; ?>" class="remove-link" onclick="return confirm('Remove this item?');">Remove</a>
          </div>
        <?php endforeach; ?>
      </div>
      <p style="text-align: center;"><button type="submit" name="update_quantities" class="update-button">Update Quantities</button></p>
    </form>

    <div class="cart-total">
      <p><strong>Grand Total: $<?php echo number_format($grandTotal, 2); ?></strong></p>
      <a href="payment.php" class="add-to-cart-button">Proceed to Payment</a>
    </div>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
