<?php
$pageTitle = "AyitiCo - Product";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/database.php';

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
  header("Location: index.php");
  exit;
}

$itemId = isset($_GET['item']) ? filter_input(INPUT_GET, 'item', FILTER_VALIDATE_INT) : 0;
if (!$itemId || $itemId <= 0) {
  header("Location: items.php");
  exit;
}

$product = null;
$reviews = [];
$message = '';
$messageClass = 'alert';

if ($pdo !== null) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$itemId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
      $pageTitle = "AyitiCo - " . htmlspecialchars($product['name']);
      $stmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r LEFT JOIN users u ON r.user_email = u.email WHERE r.product_id = ? ORDER BY r.created_at DESC");
      $stmt->execute([$itemId]);
      $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  } catch (PDOException $e) {
    $product = false;
    $message = "Error loading product.";
  }

  if ($product && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit_action'])) {
    $reviewText = trim($_POST['review'] ?? '');
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
    if (empty($reviewText)) {
      $message = "Please enter your review.";
    } elseif (!$rating) {
      $message = "Please select a rating 1–5.";
    } else {
      try {
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_email, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt->execute([$itemId, $_SESSION['email'], $rating, $reviewText])) {
          header("Location: description.php?item=" . $itemId . "&review=success#reviews-section");
          exit;
        }
      } catch (PDOException $e) {
        $message = "Could not save review.";
      }
    }
  }
}

require_once __DIR__ . '/Extras/header.php';
if ($pdo === null) {
  echo "<main><div class='alert'>Database not available.</div></main>";
  require_once __DIR__ . '/Extras/footer.php';
  exit;
}

if (isset($_GET['review']) && $_GET['review'] === 'success') {
  $message = "Review posted.";
  $messageClass = "alert-success";
}
if (isset($_GET['cart_add_status'])) {
  if ($_GET['cart_add_status'] === 'success') {
    $message = "Item added to cart.";
    $messageClass = "alert-success";
  } elseif ($_GET['cart_add_status'] === 'out_of_stock') {
    $message = "Not enough stock. Reduce quantity or try again later.";
  } else {
    $message = "Failed to add to cart.";
  }
}

function displayStars($rating) {
  $rating = max(0, min(5, (int)$rating));
  return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}
?>

<main>
  <?php if ($message): ?>
    <div class="<?php echo htmlspecialchars($messageClass); ?>"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <?php if (!$product): ?>
    <h2>Product Not Found</h2>
    <p><a href="items.php">Back to Shop</a></p>
  <?php else: ?>
    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
    <div class="product-detail">
      <div class="product-image">
        <?php
        if (!empty($product['image'])) {
          try {
            $imgData = base64_encode($product['image']);
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $imgMime = $finfo->buffer($product['image']);
            echo '<img src="data:'.$imgMime.';base64,'.$imgData.'" alt="'.htmlspecialchars($product['name']).'" />';
          } catch (Exception $e) {
            echo '<img src="images/placeholder.jpg" alt="" />';
          }
        } else {
          echo '<img src="images/placeholder.jpg" alt="" />';
        }
        ?>
      </div>
      <div class="product-info">
        <p><strong>Price:</strong> $<?php echo number_format((float)$product['price'], 2); ?></p>
        <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        <form action="add_to_cart.php" method="POST" style="margin-top: 1rem;">
          <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
          <button type="submit" name="add_to_cart_action" class="add-to-cart-button">Add to Cart</button>
        </form>
      </div>
    </div>

    <section class="reviews-section" id="reviews-section">
      <h3>Reviews</h3>
      <?php if (empty($reviews)): ?>
        <p>No reviews yet. Be the first!</p>
      <?php else: ?>
        <ul class="reviews">
          <?php foreach ($reviews as $rev): ?>
            <li>
              <strong><?php echo htmlspecialchars($rev['username'] ?? $rev['user_email']); ?></strong>
              <span class="star-rating"><?php echo displayStars($rev['rating']); ?></span>
              <p><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <h3>Leave a Review</h3>
      <form method="post" action="description.php?item=<?php echo $itemId; ?>#reviews-section" class="review-form">
        <input type="hidden" name="review_submit_action" value="1">
        <div class="form-group">
          <label for="rating">Rating (1–5):</label>
          <select id="rating" name="rating" required>
            <option value="">Select</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <option value="<?php echo $i; ?>"><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="review">Your review:</label>
          <textarea id="review" name="review" rows="4" required></textarea>
        </div>
        <button type="submit" class="update-button">Post Review</button>
      </form>
    </section>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
