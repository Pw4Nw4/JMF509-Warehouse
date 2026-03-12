<?php
$pageTitle = "AyitiCo - Shop";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/header.php';
require_once __DIR__ . '/Extras/database.php';
require_once __DIR__ . '/Extras/Cache.php';

$db = Database::getInstance();
if (!$db->getPDO()) {
  echo "<main><div class='alert'>Database connection not available.</div></main>";
  require_once __DIR__ . '/Extras/footer.php';
  exit;
}

$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;

$categories = [];
$productsLoadError = null;

// Build cache key including filters
$cacheKey = 'products_cat_' . $selectedCategory . '_' . md5($searchQuery . $sortBy . $minPrice . $maxPrice);
if (CACHE_ENABLED) $categories = SimpleCache::get($cacheKey);

if ($categories === null) {
  try {
    $categoryFilter = ($selectedCategory !== 'all' && in_array($selectedCategory, CATEGORIES)) ? $selectedCategory : null;
    $products = $db->fetchProducts(null, $categoryFilter);

    // Filter by search
    if ($searchQuery) {
      $products = array_filter($products, function($p) use ($searchQuery) {
        $search = strtolower($searchQuery);
        return strpos(strtolower($p['name']), $search) !== false ||
               strpos(strtolower($p['description'] ?? ''), $search) !== false ||
               strpos(strtolower($p['category']), $search) !== false;
      });
    }

    // Filter by price
    if ($minPrice !== null) {
      $products = array_filter($products, function($p) use ($minPrice) {
        return (float)$p['price'] >= $minPrice;
      });
    }
    if ($maxPrice !== null) {
      $products = array_filter($products, function($p) use ($maxPrice) {
        return (float)$p['price'] <= $maxPrice;
      });
    }

    // Sort products
    switch ($sortBy) {
      case 'price_low':
        usort($products, function($a, $b) { return $a['price'] - $b['price']; });
        break;
      case 'price_high':
        usort($products, function($a, $b) { return $b['price'] - $a['price']; });
        break;
      case 'newest':
        usort($products, function($a, $b) { return strtotime($b['created_at'] ?? '') - strtotime($a['created_at'] ?? ''); });
        break;
      default:
        usort($products, function($a, $b) { return strcmp($a['name'], $b['name']); });
    }

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
  <h2>Shop</h2>

  <div class="shop-filters">
    <form method="get" action="items.php" class="filter-form">
      <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">

      <div class="filter-row">
        <div class="filter-search">
          <input type="search" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>

        <div class="filter-sort">
          <select name="sort">
            <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
            <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
            <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
          </select>
        </div>

        <div class="filter-price">
          <input type="number" name="min_price" placeholder="Min $" value="<?php echo $minPrice !== null ? htmlspecialchars($minPrice) : ''; ?>" min="0" step="0.01">
          <span>-</span>
          <input type="number" name="max_price" placeholder="Max $" value="<?php echo $maxPrice !== null ? htmlspecialchars($maxPrice) : ''; ?>" min="0" step="0.01">
        </div>

        <button type="submit" class="filter-button">Apply</button>
        <a href="items.php?category=<?php echo urlencode($selectedCategory); ?>" class="clear-filters">Clear</a>
      </div>
    </form>
  </div>

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
            <a class="add-to-cart-button" href="description.php?item=<?php echo urlencode($product['id']); ?>">View Details</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
