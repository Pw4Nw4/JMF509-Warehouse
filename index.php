<?php
$pageTitle = "AyitiCo - Haitian Marketplace";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/header.php';
require_once __DIR__ . '/Extras/database.php';
require_once __DIR__ . '/Extras/Cache.php';

$featuredProducts = [];
$productsLoadError = null;
$cacheKey = 'featured_products';
if (CACHE_ENABLED) {
    $featuredProducts = SimpleCache::get($cacheKey);
}
if ($featuredProducts === null && $pdo) {
    $db = Database::getInstance();
    $featuredProducts = $db->fetchProducts(FEATURED_PRODUCTS_COUNT);
    if (!empty($featuredProducts) && CACHE_ENABLED) {
        SimpleCache::set($cacheKey, $featuredProducts, CACHE_TTL);
    }
    if (empty($featuredProducts)) $productsLoadError = "No featured products available.";
} elseif (!$pdo) {
    $productsLoadError = "Database connection not available.";
}
?>

<section class="hero-banner">
  <div class="hero-text">
    <h2>Your Trusted Haitian Marketplace</h2>
    <p>Quality products shipped to Haiti and the US. Connect with family through diaspora ordering.</p>
    <a href="items.php" class="hero-button">Start Shopping</a>
  </div>
</section>

<main>
  <?php if (!$login): ?>
    <p class="login-prompt">Please <a href="register.php">register</a> or <a href="login.php">log in</a> to shop and place orders.</p>
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
    <p>AyitiCo is your premier online marketplace for essential goods in Haiti. We offer solar products, electronics, survival supplies, and household essentials.</p>
    <p>Whether you're ordering from the US or Haiti, we make it easy to send essentials to your family. Our <strong>diaspora ordering</strong> service lets you support loved ones back home with quality products delivered to their door.</p>
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