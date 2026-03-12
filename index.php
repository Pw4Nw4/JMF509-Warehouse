<?php
$pageTitle = "JMF 509 Warehouse - Home";
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
    <h2>Essential Goods &amp; Logistics for Haiti</h2>
    <p>Ships to Haiti &amp; US. Diaspora ordering — send to family.</p>
    <a href="items.php" class="hero-button">Shop Now</a>
  </div>
</section>

<main>
  <?php if (!$login): ?>
    <p class="login-prompt">Please <a href="register.php">register</a> or <a href="login.php">log in</a> to shop and place orders.</p>
  <?php endif; ?>

  <section class="shop-categories">
    <h2>Shop by Category</h2>
    <div class="category-tabs">
      <a href="items.php?category=all">All</a>
      <?php foreach (defined('CATEGORIES') ? CATEGORIES : [] as $cat): ?>
        <a href="items.php?category=<?php echo urlencode($cat); ?>"><?php echo htmlspecialchars($cat); ?></a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="intro-content" id="mission">
    <h2>Our Mission</h2>
    <p>JMF 509 Warehouse is an online marketplace for essential goods—solar products, electronics, survival items—for Haiti. Order from the U.S. or Haiti; delivery or pickup. We support <strong>diaspora ordering</strong>: send essentials to family.</p>
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
            <p class="item-tile-badge">Ships to Haiti &amp; US</p>
            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
            <p class="price">$<?php echo number_format((float)$product['price'], 2); ?></p>
            <a class="add-to-cart-button" href="description.php?item=<?php echo urlencode($product['id']); ?>">Add to Cart</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
