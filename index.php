<?php
$pageTitle = "JMF 509 Warehouse - Home";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/header.php';
require_once __DIR__ . '/Extras/nav.php';
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
    <p>Solar products, electronics, survival supplies. Order online from the U.S. or Haiti—delivery or pickup.</p>
    <a href="items.php" class="hero-button">Shop Now</a>
  </div>
</section>

<main>
  <section class="intro-content">
    <h2>Our Mission</h2>
    <p>JMF 509 Warehouse is an online store and logistics platform that sells essential goods—solar products, electronics, and survival items—for people in Haiti. Customers in the U.S. and Haiti can order products online and have them delivered or picked up.</p>
    <p>We support <strong>diaspora ordering</strong>: send essentials to family in Haiti. Browse by category, add to cart, checkout, and pay online. We manage inventory and shipping so you don’t have to.</p>
    <?php if (!$login): ?>
      <p>Please <a href="register.php">register</a> or <a href="login.php">log in</a> to shop and place orders.</p>
    <?php endif; ?>
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
