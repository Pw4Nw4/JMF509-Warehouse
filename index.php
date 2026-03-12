<?php
$pageTitle = "AyitiCo - Haitian Marketplace";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/header.php';
require_once __DIR__ . '/Extras/database.php';
require_once __DIR__ . '/Extras/Cache.php';

$featuredProducts = [];
$productsLoadError = null;
$debugInfo = '';

// Debug: show database status
if (DEBUG_MODE) {
    $debugInfo .= "PDO status: " . ($pdo ? "connected" : "NOT connected") . "<br>";
}

$cacheKey = 'featured_products';
if (CACHE_ENABLED) {
    $featuredProducts = SimpleCache::get($cacheKey);
}

if ($featuredProducts === null && $pdo) {
    try {
        $db = Database::getInstance();
        $featuredProducts = $db->fetchProducts(FEATURED_PRODUCTS_COUNT);
        if (DEBUG_MODE) {
            $debugInfo .= "Products found: " . count($featuredProducts) . "<br>";
        }
        if (!empty($featuredProducts) && CACHE_ENABLED) {
            SimpleCache::set($cacheKey, $featuredProducts, CACHE_TTL);
        }
        if (empty($featuredProducts)) $productsLoadError = "No featured products available.";
    } catch (Exception $e) {
        $productsLoadError = "Error: " . $e->getMessage();
        if (DEBUG_MODE) {
            $debugInfo .= "Exception: " . $e->getMessage() . "<br>";
        }
    }
} elseif (!$pdo) {
    $productsLoadError = "Database connection not available.";
}
?>

<section class="hero-banner">
  <div class="hero-text">
    <h2>Your Trusted Marketplace for Everything</h2>
    <p>Shop thousands of products across 15 categories. Great prices, fast shipping, and quality guaranteed.</p>
    <a href="items.php" class="hero-button">Shop Now</a>
  </div>
</section>

<main>
  <?php if (DEBUG_MODE && $debugInfo): ?>
    <div style="background: #eee; padding: 10px; margin-bottom: 20px; font-family: monospace; font-size: 12px;">
      <strong>Debug Info:</strong><br>
      <?php echo $debugInfo; ?>
    </div>
  <?php endif; ?>

  <?php if (!$login): ?>
    <p class="login-prompt">Please <a href="login.php">sign in</a> to shop and place orders.</p>
  <?php endif; ?>

  <section class="shop-categories">
    <h2>Browse Categories</h2>
    <div class="category-tabs">
      <a href="items.php?category=all">All</a>
      <?php foreach (defined('CATEGORIES') ? CATEGORIES : [] as $cat): ?>
        <a href="items.php?category=<?php echo urlencode($cat); ?>"><?php echo htmlspecialchars($cat); ?></a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="intro-content" id="mission">
    <h2>Welcome to AyitiCo</h2>
    <p>AyitiCo is your premier online marketplace offering a wide variety of products across multiple categories. From electronics and fashion to home essentials and services, we have everything you need.</p>
    <p>Shop with confidence knowing we offer <strong>secure payments</strong>, <strong>fast shipping</strong>, and <strong>quality products</strong> from trusted sellers.</p>
  </section>

  <section class="category-showcase">
    <h2>Shop by Category</h2>
    <div class="category-grid">
      <?php
      $category_icons = [
        'Electronics' => '📱',
        'Clothing & Fashion' => '👕',
        'Home & Garden' => '🏠',
        'Beauty & Health' => '💄',
        'Sports & Outdoors' => '⚽',
        'Toys & Games' => '🎮',
        'Books & Media' => '📚',
        'Automotive' => '🚗',
        'Food & Grocery' => '🛒',
        'Baby & Kids' => '👶',
        'Office & School Supplies' => '✏️',
        'Jewelry & Watches' => '💎',
        'Pet Supplies' => '🐕',
        'Music & Instruments' => '🎸',
        'Services & Digital' => '💻'
      ];
      foreach (CATEGORIES as $cat): ?>
        <a href="items.php?category=<?php echo urlencode($cat); ?>" class="category-card">
          <span class="category-icon"><?php echo $category_icons[$cat] ?? '📦'; ?></span>
          <span class="category-name"><?php echo htmlspecialchars($cat); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="why-choose">
    <div class="why-choose-card">
      <div class="icon">🚚</div>
      <h4>Fast Shipping</h4>
      <p>Reliable delivery to Haiti and the US with tracking available.</p>
    </div>
    <div class="why-choose-card">
      <div class="icon">💳</div>
      <h4>Secure Payments</h4>
      <p>Safe and secure checkout with multiple payment options.</p>
    </div>
    <div class="why-choose-card">
      <div class="icon">🤝</div>
      <h4>Diaspora Support</h4>
      <p>Send essential goods to family in Haiti easily.</p>
    </div>
    <div class="why-choose-card">
      <div class="icon">⭐</div>
      <h4>Quality Products</h4>
      <p>Curated selection of trusted brands and essentials.</p>
    </div>
  </section>

  <section class="featured-products">
    <h2>Featured Products</h2>
    <?php if ($productsLoadError): ?>
      <p class="alert"><?php echo htmlspecialchars($productsLoadError); ?></p>
    <?php elseif (empty($featuredProducts)): ?>
      <p>No featured products at the moment.</p>
    <?php else: ?>
      <div class="items-grid">
        <?php foreach ($featuredProducts as $product): ?>
          <div class="item-tile">
            <div class="item-tile-image">
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
            <p class="item-tile-badge">Ships to Haiti & US</p>
            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
            <p class="price">$<?php echo number_format((float)$product['price'], 2); ?></p>
            <a class="add-to-cart-button" href="description.php?item=<?php echo urlencode($product['id']); ?>">View Details</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>