<?php
$pageTitle = "JMF 509 Warehouse - Search";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Extras/database.php';

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
  header("Location: index.php");
  exit;
}

require_once __DIR__ . '/Extras/header.php';

$db = Database::getInstance();
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($query !== '' && $db->getPDO()) {
  try {
    $stmt = $db->getPDO()->prepare("SELECT id, name, price, category, description, image FROM products WHERE name ILIKE ? OR description ILIKE ? OR category ILIKE ? ORDER BY name");
    $term = '%' . $query . '%';
    $stmt->execute([$term, $term, $term]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $error = "Search failed.";
  }
}
?>

<main>
  <h2>Search Results<?php if ($query !== ''): ?> for "<?php echo htmlspecialchars($query); ?>"<?php endif; ?></h2>

  <?php if (isset($error)): ?>
    <p class="alert"><?php echo htmlspecialchars($error); ?></p>
  <?php elseif ($query === ''): ?>
    <p>Enter a search term in the header search bar.</p>
  <?php elseif (empty($results)): ?>
    <p>No products found for "<?php echo htmlspecialchars($query); ?>".</p>
    <p><a href="items.php">Browse all products</a></p>
  <?php else: ?>
    <p class="search-result-count"><?php echo count($results); ?> result(s)</p>
    <div class="items-grid">
      <?php foreach ($results as $product): ?>
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
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
