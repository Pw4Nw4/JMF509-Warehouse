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
    $stmt = $db->getPDO()->prepare("SELECT id, name, price, category, description FROM products WHERE name LIKE ? OR description LIKE ? OR category LIKE ? ORDER BY name");
    $term = '%' . $query . '%';
    $stmt->execute([$term, $term, $term]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $error = "Search failed.";
  }
}

require_once __DIR__ . '/Extras/nav.php';
?>

<main>
  <h2>Search Products</h2>
  <form method="get" action="search.php" class="search-form">
    <input type="search" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Product name, category...">
    <button type="submit" class="update-button">Search</button>
  </form>

  <?php if (isset($error)): ?>
    <p class="alert"><?php echo htmlspecialchars($error); ?></p>
  <?php elseif ($query === ''): ?>
    <p>Enter a search term above.</p>
  <?php elseif (empty($results)): ?>
    <p>No products found for "<?php echo htmlspecialchars($query); ?>".</p>
  <?php else: ?>
    <p><?php echo count($results); ?> result(s).</p>
    <ul class="search-results">
      <?php foreach ($results as $p): ?>
        <li>
          <a href="description.php?item=<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></a>
          — $<?php echo number_format((float)$p['price'], 2); ?> (<?php echo htmlspecialchars($p['category']); ?>)
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/Extras/footer.php'; ?>
