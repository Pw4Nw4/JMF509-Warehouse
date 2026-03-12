<?php
$pageTitle = "AyitiCo - Shop";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/database.php';

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
  header("Location: index.php");
  exit;
}

require_once __DIR__ . '/Extras/header.php';
require_once __DIR__ . '/Extras/Cache.php';

$db = Database::getInstance();
if (!$db->getPDO()) {
  echo "<main><div class='alert'>Database connection not available.</div></main>";
  require_once __DIR__ . '/Extras/footer.php';
  exit;
}

$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$categories = [];
$productsLoadError = null;
$cacheKey = 'products_cat_' . $selectedCategory;
if (CACHE_ENABLED) $categories = SimpleCache::get($cacheKey);

if ($categories === null) {
  try {
    $categoryFilter = ($selectedCategory !== 'all' && in_array($selectedCategory, CATEGORIES)) ? $selectedCategory : null;
    $products = $db->fetchProducts(null, $categoryFilter);
    $categories = [];
    foreach ($products as $p) {
      $cat = trim($p['category'] ?? 'Other');
      if (!isset($categories[$cat])) $categories[$cat] = [];
      $categories[$cat][] = $p;
    }
    ksort($categories);
    if (CACHE_ENABLED) SimpleCache::set($cacheKey, $categories, CACHE_TTL);
  } catch (Exception $e) {
    $productsLoadError = "Could not load products.";
    $categories = [];
  }
}
?>

<main>
  <h2>Shop by Category</h2>
  <div class="category-tabs">
    <a href="items.php?category=all"<?php echo $selectedCategory === 'all' ? ' class="active"' : ''; ?>>All</a>
    <?php foreach (CATEGORIES as $cat): ?>
      <a href="items.php?category=<?php echo urlencode($cat); ?>"<?php echo $selectedCategory === $cat ? ' class="active"' : ''; ?>><?php echo htmlspecialchars($cat); ?></a>
    <?php endforeach; ?>
  </div>

  <?php if ($productsLoadError): ?>
    <p class="alert"><?php echo htmlspecialchars($productsLoadError); ?></p>
  <?php elseif (empty($categories)): ?>
    <p>No products in this category.</p>
  <?php else: ?>
    <?php foreach ($categories as $categoryName => $productsInCategory): ?>
      <h3><?php echo htmlspecialchars($categoryName); ?></h3>
      <div class="items-grid">
        <?php foreach ($productsInCategory as $product): ?>
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
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
