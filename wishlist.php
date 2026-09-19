<?php
/**
 * User Saved Wishlist Page
 */

require_once __DIR__ . '/includes/header.php';

requireLogin();

$db = Database::getConnection();
$userId = getCurrentUserId();

// Fetch Wishlist Items
$stmt = $db->prepare("SELECT w.id as wishlist_id, p.*, c.name as category_name FROM wishlist w JOIN products p ON w.product_id = p.id JOIN categories c ON p.category_id = c.id WHERE w.user_id = ? ORDER BY w.id DESC");
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll();
?>

<div class="container my-5">
    <h1 class="section-title mb-4"><i class="fa-solid fa-heart text-rose"></i> Saved Wishlist</h1>

    <?php if (empty($wishlistItems)): ?>
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 4rem 2rem; text-align: center; max-width: 600px; margin: 0 auto;">
            <div style="font-size: 3.5rem; color: var(--rose); margin-bottom: 1rem;"><i class="fa-regular fa-heart"></i></div>
            <h2>Your wishlist is empty</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Click the heart icon on any product to save it here for later!</p>
            <a href="products.php" class="btn-primary">Explore Products</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($wishlistItems as $product): ?>
                <div class="product-card">
                    <div class="product-img-wrap">
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <button class="wishlist-toggle-btn active" data-product-id="<?= $product['id'] ?>" title="Remove from Wishlist">
                            <i class="fa-solid fa-heart"></i>
                        </button>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                        <h3 class="product-title"><a href="product-details.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h3>
                        
                        <?= renderRatingStars($product['rating']) ?>
                        
                        <div class="product-footer">
                            <div class="price-wrap">
                                <span class="current-price"><?= formatPrice($product['price']) ?></span>
                            </div>
                            <button class="add-cart-btn" data-product-id="<?= $product['id'] ?>">
                                <i class="fa-solid fa-cart-plus"></i> Move to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
