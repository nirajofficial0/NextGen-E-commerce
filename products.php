<?php
/**
 * NextGen Products Catalog & Search
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/ai_engine.php';

$db = Database::getConnection();

// Filter parameters
$catSlug = $_GET['cat'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');
$minPrice = (float)($_GET['min_price'] ?? 0);
$maxPrice = (float)($_GET['max_price'] ?? 100000);
$sort = $_GET['sort'] ?? 'newest';

// Fetch Categories
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Build Dynamic Query
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.price >= ? AND p.price <= ?";
$params = [$minPrice, $maxPrice];

if (!empty($catSlug)) {
    $query .= " AND c.slug = ?";
    $params[] = $catSlug;
}

if (!empty($searchQuery)) {
    $query .= " AND (p.name LIKE ? OR p.tags LIKE ? OR p.description LIKE ?)";
    $term = '%' . $searchQuery . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

// Sorting
if ($sort === 'price_low') {
    $query .= " ORDER BY p.price ASC";
} elseif ($sort === 'price_high') {
    $query .= " ORDER BY p.price DESC";
} elseif ($sort === 'rating') {
    $query .= " ORDER BY p.rating DESC";
} else {
    $query .= " ORDER BY p.id DESC";
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="container my-4">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 2rem;">
        <h1 class="section-title">Product Catalog</h1>
        <p style="color: var(--text-secondary);">Showing <?= count($products) ?> items <?= !empty($searchQuery) ? 'matching "' . htmlspecialchars($searchQuery) . '"' : '' ?></p>
    </div>

    <div style="display: grid; grid-template-columns: 260px 1fr; gap: 2rem;">
        <!-- Filters Sidebar -->
        <aside style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; height: fit-content;">
            <h3 style="font-family: var(--font-heading); font-size: 1.2rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fa-solid fa-filter"></i> Filters</span>
                <a href="products.php" style="font-size: 0.8rem; color: var(--primary);">Reset</a>
            </h3>

            <form action="products.php" method="GET">
                <?php if ($searchQuery): ?>
                    <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
                <?php endif; ?>

                <!-- Category Filter -->
                <div class="form-group mb-4">
                    <label style="font-weight: 600; color: #fff; margin-bottom: 0.5rem; display: block;">Category</label>
                    <select name="cat" class="form-control" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['slug'] ?>" <?= $catSlug === $cat['slug'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Price Range Filter -->
                <div class="form-group mb-4">
                    <label style="font-weight: 600; color: #fff; margin-bottom: 0.5rem; display: block;">Max Price: <?= formatPrice($maxPrice) ?></label>
                    <input type="range" name="max_price" min="1000" max="100000" step="1000" value="<?= $maxPrice ?>" style="width: 100%; accent-color: var(--primary);" onchange="this.form.submit()">
                </div>

                <!-- Sort Filter -->
                <div class="form-group mb-4">
                    <label style="font-weight: 600; color: #fff; margin-bottom: 0.5rem; display: block;">Sort By</label>
                    <select name="sort" class="form-control" onchange="this.form.submit()">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Customer Rating</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">Apply Filters</button>
            </form>
        </aside>

        <!-- Product Grid -->
        <main>
            <?php if (empty($products)): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 4rem 2rem; text-align: center;">
                    <div style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"><i class="fa-solid fa-box-open"></i></div>
                    <h3>No products found matching your filters</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Try adjusting your price range, category, or search query.</p>
                    <a href="products.php" class="btn-primary">View All Products</a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-img-wrap">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <button class="wishlist-toggle-btn <?= isInWishlist($product['id']) ? 'active' : '' ?>" data-product-id="<?= $product['id'] ?>" title="Save to Wishlist">
                                    <i class="fa-<?= isInWishlist($product['id']) ? 'solid' : 'regular' ?> fa-heart"></i>
                                </button>
                            </div>
                            <div class="product-info">
                                <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                                <h3 class="product-title"><a href="product-details.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h3>
                                
                                <?= renderRatingStars($product['rating']) ?>
                                
                                <div class="product-footer">
                                    <div class="price-wrap">
                                        <span class="current-price"><?= formatPrice($product['price']) ?></span>
                                        <?php if ($product['original_price']): ?>
                                            <span class="original-price"><?= formatPrice($product['original_price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="add-cart-btn" data-product-id="<?= $product['id'] ?>">
                                        <i class="fa-solid fa-cart-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
