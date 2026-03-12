<?php
$pageTitle = "JMF 509 Warehouse - Checkout";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/database.php';
require_once __DIR__ . '/Extras/Security.php';
require_once __DIR__ . '/Extras/PaymentGateway.php';
require_once __DIR__ . '/Extras/EmailService.php';

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
  header("Location: index.php");
  exit;
}

$db = Database::getInstance();
$cartItems = [];
$total = 0;
$message = '';
$messageClass = 'alert';
$orderComplete = false;
$orderId = null;
$isTransferOrder = false;

try {
  $cartItems = $db->getCartItems($_SESSION['email']);
  foreach ($cartItems as $item) {
    $total += (float)$item['product_price'] * (int)$item['quantity'];
  }
} catch (Exception $e) {
  $message = "Error loading cart.";
}

if (empty($cartItems)) {
  header("Location: cart.php");
  exit;
}

require_once __DIR__ . '/Extras/header.php';

$stripeEnabled = defined('STRIPE_ENABLED') && STRIPE_ENABLED;

function paymentStockOk($cartItems) {
  foreach ($cartItems as $item) {
    $need = (int)$item['quantity'];
    $have = (int)($item['product_stock'] ?? 0);
    if ($need > $have) return [false, $item['product_name'] ?? 'Item', $have];
  }
  return [true, null, null];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
  $destination = isset($_POST['shipping_destination']) && in_array($_POST['shipping_destination'], ['Haiti', 'US'], true) ? $_POST['shipping_destination'] : 'US';
  $deliveryType = isset($_POST['delivery_type']) && in_array($_POST['delivery_type'], ['delivery', 'pickup'], true) ? $_POST['delivery_type'] : 'delivery';
  $recipientName = trim($_POST['recipient_name'] ?? '');
  $shippingAddress = trim($_POST['shipping_address'] ?? '');
  $payWithCard = $stripeEnabled && $destination === 'US' && !empty(preg_replace('/\s+/', '', $_POST['card_number'] ?? ''));

  list($stockOk, $stockProductName, $stockAvailable) = paymentStockOk($cartItems);
  if (!$stockOk) {
    $message = "Not enough stock for \"$stockProductName\" (only $stockAvailable available). Update your cart or try again later.";
  } elseif ($payWithCard) {
    $cardData = [
      'number' => preg_replace('/\s+/', '', $_POST['card_number'] ?? ''),
      'expiry' => $_POST['expiry'] ?? '',
      'cvv' => $_POST['cvv'] ?? '',
      'name' => $_POST['card_name'] ?? ''
    ];
    $result = PaymentGateway::processPayment($total, $cardData, $_SESSION['email']);
    if ($result['success']) {
      try {
        $stmt = $db->getPDO()->prepare("INSERT INTO orders (user_email, total, status, shipping_address, shipping_destination, delivery_type, recipient_name, transaction_id, created_at) VALUES (?, ?, 'paid', ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$_SESSION['email'], $total, $shippingAddress, $destination, $deliveryType, $recipientName, $result['transaction_id']]);
        $orderId = $db->getPDO()->lastInsertId();
        foreach ($cartItems as $item) {
          $ins = $db->getPDO()->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
          $ins->execute([$orderId, $item['product_id'], $item['quantity'], $item['product_price']]);
          $dec = $db->getPDO()->prepare("UPDATE products SET stock_quantity = GREATEST(0, COALESCE(stock_quantity, 0) - ?) WHERE id = ?");
          $dec->execute([(int)$item['quantity'], $item['product_id']]);
        }
        $stmt = $db->getPDO()->prepare("DELETE FROM carts WHERE user_email = ?");
        $stmt->execute([$_SESSION['email']]);
        $orderComplete = true;
        EmailService::sendOrderConfirmation($_SESSION['email'], $orderId, $total, $destination);
        $message = "Payment successful! Order #$orderId created.";
        $messageClass = 'alert-success';
      } catch (Exception $e) {
        $message = "Payment processed but order save failed. Contact support.";
      }
    } else {
      $message = "Payment failed: " . ($result['error'] ?? 'Unknown error');
    }
  } else {
    if (empty($shippingAddress)) {
      $message = "Please enter your shipping address.";
    } else {
      try {
        $stmt = $db->getPDO()->prepare("INSERT INTO orders (user_email, total, status, shipping_address, shipping_destination, delivery_type, recipient_name, transaction_id, created_at) VALUES (?, ?, 'pending', ?, ?, ?, ?, NULL, NOW())");
        $stmt->execute([$_SESSION['email'], $total, $shippingAddress, $destination, $deliveryType, $recipientName]);
        $orderId = $db->getPDO()->lastInsertId();
        foreach ($cartItems as $item) {
          $ins = $db->getPDO()->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
          $ins->execute([$orderId, $item['product_id'], $item['quantity'], $item['product_price']]);
          $dec = $db->getPDO()->prepare("UPDATE products SET stock_quantity = GREATEST(0, COALESCE(stock_quantity, 0) - ?) WHERE id = ?");
          $dec->execute([(int)$item['quantity'], $item['product_id']]);
        }
        $stmt = $db->getPDO()->prepare("DELETE FROM carts WHERE user_email = ?");
        $stmt->execute([$_SESSION['email']]);
        $orderComplete = true;
        $isTransferOrder = true;
        EmailService::sendOrderConfirmation($_SESSION['email'], $orderId, $total, $destination);
        $message = "Order #$orderId received. We'll contact you to complete payment (Zelle, transfer, etc.). Total: $" . number_format($total, 2);
        $messageClass = 'alert-success';
      } catch (Exception $e) {
        $message = "Could not save order. Please try again.";
      }
    }
  }
}
?>

<main>
  <h2>Checkout</h2>
  <?php if ($message): ?>
    <div class="<?php echo htmlspecialchars($messageClass); ?>"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <?php if ($orderComplete): ?>
    <p><a href="orders.php">View Orders</a> | <a href="index.php">Continue Shopping</a></p>
  <?php else: ?>
    <div class="payment-layout">
      <div class="order-summary-box">
        <h3>Order Summary</h3>
        <?php foreach ($cartItems as $item): ?>
          <div class="order-line">
            <span><?php echo htmlspecialchars($item['product_name']); ?> × <?php echo (int)$item['quantity']; ?></span>
            <span>$<?php echo number_format((float)$item['product_price'] * (int)$item['quantity'], 2); ?></span>
          </div>
        <?php endforeach; ?>
        <p class="order-total-line"><strong>Total: $<?php echo number_format($total, 2); ?></strong></p>
      </div>

      <div class="payment-form-box">
        <h3>Where are you shipping?</h3>
        <form method="POST" id="checkout-form">
          <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
          <div class="form-group">
            <label>Destination</label>
            <select name="shipping_destination" id="shipping_destination">
              <option value="US">United States</option>
              <option value="Haiti">Haiti</option>
            </select>
          </div>
          <div class="form-group">
            <label>Delivery type</label>
            <select name="delivery_type">
              <option value="delivery">Delivery</option>
              <option value="pickup">Pickup</option>
            </select>
          </div>
          <div class="form-group">
            <label>Recipient name (for diaspora / sending to family)</label>
            <input type="text" name="recipient_name" placeholder="Full name of recipient">
          </div>
          <div class="form-group">
            <label>Shipping address</label>
            <textarea name="shipping_address" rows="3" required placeholder="Street, city, state/region, zip or postal code"><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
          </div>

          <div id="transfer-submit-block">
            <p class="transfer-note">We'll contact you to complete payment via Zelle or transfer. No card needed.</p>
            <button type="submit" name="submit_order" class="update-button">Submit order – we'll contact you</button>
          </div>

          <?php if ($stripeEnabled): ?>
          <div id="card-payment-block" style="display: none;">
            <h3>Pay with card (US only)</h3>
            <div class="form-group">
              <label>Card number</label>
              <input type="text" name="card_number" id="card_number" placeholder="4111 1111 1111 1111">
            </div>
            <div class="form-group form-row">
              <div><label>Expiry (MM/YY)</label><input type="text" name="expiry" id="card_expiry" placeholder="12/25"></div>
              <div><label>CVV</label><input type="text" name="cvv" id="card_cvv" placeholder="123"></div>
            </div>
            <div class="form-group">
              <label>Cardholder name</label>
              <input type="text" name="card_name" id="card_name">
            </div>
            <button type="submit" name="pay_with_card" class="update-button">Pay $<?php echo number_format($total, 2); ?></button>
          </div>
          <?php endif; ?>
        </form>
      </div>
    </div>
    <?php if ($stripeEnabled): ?>
    <script>
    (function() {
      var dest = document.getElementById('shipping_destination');
      var transferBlock = document.getElementById('transfer-submit-block');
      var cardBlock = document.getElementById('card-payment-block');
      var cardInputs = document.querySelectorAll('#card_number, #card_expiry, #card_cvv, #card_name');
      function toggle() {
        var isUS = dest && dest.value === 'US';
        if (transferBlock) transferBlock.style.display = isUS ? 'none' : 'block';
        if (cardBlock) cardBlock.style.display = isUS ? 'block' : 'none';
        cardInputs.forEach(function(inp) { inp.required = isUS; });
      }
      if (dest) { dest.addEventListener('change', toggle); toggle(); }
    })();
    </script>
    <?php endif; ?>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
