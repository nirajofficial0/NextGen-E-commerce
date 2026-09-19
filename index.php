<?php
/**
 * NextGen E-Commerce Homepage
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/ai_engine.php';

$db = Database::getConnection();

// Fetch categories
$stmt = $db->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $stmt->fetchAll();

// Fetch trending & featured products
$stmt = $db->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_trending = 1 OR p.is_featured = 1 ORDER BY p.rating DESC LIMIT 8");
$featuredProducts = $stmt->fetchAll();

// Handle instant AI Quick Search on Homepage
$aiQuickResults = null;
if (isset($_GET['ai_req']) && !empty($_GET['ai_req'])) {
    $aiQuickResults = AIEngine::getRecommendationsForPrompt($_GET['ai_req'], 4);
}
?>

<!-- Hero Banner Section -->
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fa-solid fa-sparkles"></i> AI-POWERED SHOPPING PLATFORM
            </div>
            <h1 class="hero-title">NEXT GENERATION SHOPPING</h1>
            <p class="hero-subtitle">Discover gadgets and tech tailored to your exact requirements using our intelligent AI Recommendation Engine.</p>
            
            <div class="hero-cta">
                <a href="products.php" class="btn-primary"><i class="fa-solid fa-bag-shopping"></i> Explore Store</a>
                <a href="ai-assistant.php" class="btn-secondary"><i class="fa-solid fa-robot"></i> Try AI Recommender</a>
            </div>
        </div>

        <!-- AI Requirement Card Box -->
        <div class="hero-ai-card">
            <span class="ai-card-badge">NEXTGEN AI</span>
            <h3><i class="fa-solid fa-wand-magic-sparkles text-glow"></i> What are you looking for?</h3>
            <p>Type naturally e.g. <em>"Laptop for coding & college under ₹60,000 with 16GB RAM"</em></p>
            
            <form action="index.php" method="GET" class="ai-input-group">
                <textarea name="ai_req" placeholder="Describe your requirement, budget, or preferred features..." required><?= htmlspecialchars($_GET['ai_req'] ?? '') ?></textarea>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fa-solid fa-brain"></i> Generate AI Matches
                </button>
            </form>
        </div>
    </div>
</section>

<!-- AI Match Quick Results (If Submitted) -->
<?php if ($aiQuickResults): ?>
<section class="container my-5">
    <div class="flash-alert success mb-4">
        <span><i class="fa-solid fa-robot"></i> AI Analyzed: <strong>"<?= htmlspecialchars($_GET['ai_req']) ?>"</strong></span>
    </div>
    
    <div class="section-header">
        <h2 class="section-title"><i class="fa-solid fa-star text-amber"></i> AI Recommended For You</h2>
        <p class="section-subtitle">Ranked by requirement match score</p>
    </div>

    <div class="products-grid">
        <?php foreach ($aiQuickResults['recommendations'] as $product): ?>
            <div class="product-card">
                <div class="product-img-wrap">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="ai-match-badge">
                        <i class="fa-solid fa-bolt"></i> <?= $product['ai_score'] ?>% Match
                    </div>
                    <button class="wishlist-toggle-btn <?= isInWishlist($product['id']) ? 'active' : '' ?>" data-product-id="<?= $product['id'] ?>" title="Save to Wishlist">
                        <i class="fa-<?= isInWishlist($product['id']) ? 'solid' : 'regular' ?> fa-heart"></i>
                    </button>
                </div>
                <div class="product-info">
                    <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                    <h3 class="product-title"><a href="product-details.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h3>
                    
                    <div style="font-size: 0.8rem; color: #34d399; margin-bottom: 0.5rem;">
                        <?php foreach (array_slice($product['ai_reasons'], 0, 2) as $reason): ?>
                            <div><?= htmlspecialchars($reason) ?></div>
                        <?php endforeach; ?>
                    </div>

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
</section>
<?php endif; ?>

<!-- Shop By Category Section -->
<section class="container my-5">
    <div class="section-header">
        <h2 class="section-title">Explore Categories</h2>
        <p class="section-subtitle">Browse products across next-generation tech categories</p>
    </div>

    <div class="categories-grid">
        <?php foreach ($categories as $cat): ?>
            <a href="products.php?cat=<?= $cat['slug'] ?>" class="category-card">
                <div class="cat-icon"><i class="fa-solid <?= $cat['icon_class'] ?>"></i></div>
                <h4><?= htmlspecialchars($cat['name']) ?></h4>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Trending Products Section -->
<section class="container my-5">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <h2 class="section-title"><i class="fa-solid fa-fire text-rose"></i> Trending Tech 2026</h2>
            <p class="section-subtitle">Top rated flagship devices and smart electronics</p>
        </div>
        <a href="products.php" class="btn-secondary" style="padding: 0.5rem 1.25rem; font-size: 0.9rem;">View All Catalog &rarr;</a>
    </div>

    <div class="products-grid">
        <?php foreach ($featuredProducts as $product): ?>
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
</section>

<!-- NextGen Features Highlights -->
<section class="container my-5" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 3rem 2rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2rem; text-align: center;">
        <div>
            <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.5rem;"><i class="fa-solid fa-microchip"></i></div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.25rem;">AI Requirement Engine</h4>
            <p style="color: var(--text-secondary); font-size: 0.85rem;">Understands natural language shopping requests and matches specs instantly.</p>
        </div>
        <div>
            <div style="font-size: 2.5rem; color: var(--accent); margin-bottom: 0.5rem;"><i class="fa-solid fa-qrcode"></i></div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.25rem;">Instant UPI & QR Payment</h4>
            <p style="color: var(--text-secondary); font-size: 0.85rem;">Fast checkout with instant QR scan, GPay, PhonePe, and Cards.</p>
        </div>
        <div>
            <div style="font-size: 2.5rem; color: var(--emerald); margin-bottom: 0.5rem;"><i class="fa-solid fa-truck-fast"></i></div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.25rem;">Express Dispatch</h4>
            <p style="color: var(--text-secondary); font-size: 0.85rem;">Real-time tracking stepper timeline from processing to delivery.</p>
        </div>
        <div>
            <div style="font-size: 2.5rem; color: var(--neon-pink); margin-bottom: 0.5rem;"><i class="fa-solid fa-shield-check"></i></div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.25rem;">100% Verified Quality</h4>
            <p style="color: var(--text-secondary); font-size: 0.85rem;">Authentic products with manufacturer warranty and hassle-free returns.</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
