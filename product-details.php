<?php
/**
 * NextGen Product Details View
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/ai_engine.php';

$productId = (int)($_GET['id'] ?? 0);
$db = Database::getConnection();

// Handle Direct "Buy Now" Action
if (isset($_GET['action']) && $_GET['action'] === 'buy_now') {
    $sessId = getSessionId();
    $userId = getCurrentUserId();
    $db->prepare("DELETE FROM cart WHERE user_id = ? OR session_id = ?")->execute([$userId, $sessId]);
    $stmt = $db->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, 1)");
    $stmt->execute([$userId, $sessId, $productId]);
    header("Location: checkout.php");
    exit;
}

// Fetch product & category
$stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit;
}

$specs = json_decode($product['specs_json'] ?? '{}', true) ?: [];

// Handle New Review Submission BEFORE HTML Output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in to submit a product review.');
        header("Location: login.php");
        exit;
    }

    $rating = max(1, min(5, (int)$_POST['rating']));
    $comment = sanitizeInput($_POST['comment']);

    if (!empty($comment)) {
        $stmt = $db->prepare("INSERT INTO reviews (product_id, user_id, user_name, rating, comment) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$productId, getCurrentUserId(), getCurrentUser()['full_name'], $rating, $comment]);

        $stmt = $db->prepare("SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM reviews WHERE product_id = ?");
        $stmt->execute([$productId]);
        $revStat = $stmt->fetch();

        $stmt = $db->prepare("UPDATE products SET rating = ?, total_reviews = ? WHERE id = ?");
        $stmt->execute([round($revStat['avg_r'], 2), $revStat['cnt'], $productId]);

        setFlash('success', 'Thank you! Your review has been submitted.');
        header("Location: product-details.php?id=" . $productId);
        exit;
    }
}

// Fetch Reviews, AI Summary, Q&A, and Bundle
$stmt = $db->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY id DESC");
$stmt->execute([$productId]);
$reviews = $stmt->fetchAll();

$aiReviewSummary = AIEngine::generateReviewSummary($productId);
$frequentlyBought = AIEngine::getFrequentlyBoughtTogether($productId);
$setupItems = AIEngine::getSetupRecommendations($productId, 4);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <!-- Main Product Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 4rem;">
        <!-- Left: Product Image Showcase -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden; padding: 2rem; display: flex; align-items: center; justify-content: center; position: relative;">
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="max-width: 100%; max-height: 450px; object-fit: contain;">
            
            <div style="position: absolute; top: 15px; right: 15px; display: flex; flex-direction: column; gap: 0.5rem;">
                <button class="wishlist-toggle-btn <?= isInWishlist($product['id']) ? 'active' : '' ?>" data-product-id="<?= $product['id'] ?>" style="width: 44px; height: 44px; font-size: 1.2rem; position: relative; top: 0; right: 0;">
                    <i class="fa-<?= isInWishlist($product['id']) ? 'solid' : 'regular' ?> fa-heart"></i>
                </button>
                <button onclick="addToCompare(<?= $product['id'] ?>)" class="action-btn" title="Add to Comparison Studio">
                    <i class="fa-solid fa-code-compare"></i>
                </button>
            </div>
        </div>

        <!-- Right: Product Info & Actions -->
        <div>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 0.5rem;">
                <a href="products.php?cat=<?= $product['category_slug'] ?>"><?= htmlspecialchars($product['category_name']) ?></a>
            </div>

            <h1 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.2;">
                <?= htmlspecialchars($product['name']) ?>
            </h1>

            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <?= renderRatingStars($product['rating']) ?>
                <span style="color: var(--text-muted); font-size: 0.9rem;">(<?= $product['total_reviews'] ?> customer reviews)</span>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <span style="font-size: 2.2rem; font-weight: 800; color: #ffffff; margin-right: 1rem;"><?= formatPrice($product['price']) ?></span>
                <?php if ($product['original_price']): ?>
                    <span style="font-size: 1.2rem; color: var(--text-muted); text-decoration: line-through;"><?= formatPrice($product['original_price']) ?></span>
                    <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 0.85rem; padding: 0.2rem 0.6rem; border-radius: 99px; margin-left: 0.5rem; font-weight: 700;">Save <?= formatPrice($product['original_price'] - $product['price']) ?></span>
                <?php endif; ?>
            </div>

            <p style="color: var(--text-secondary); font-size: 1rem; margin-bottom: 1.5rem; line-height: 1.7;">
                <?= htmlspecialchars($product['description']) ?>
            </p>

            <!-- Stock & Delivery Status -->
            <div style="display: flex; gap: 2rem; margin-bottom: 2rem; font-size: 0.9rem; color: var(--text-secondary);">
                <div><i class="fa-solid fa-circle-check text-emerald"></i> In Stock (<?= $product['stock_quantity'] ?> units available)</div>
                <div><i class="fa-solid fa-truck-fast text-primary"></i> Free Express Delivery</div>
            </div>

            <!-- Quantity, Cart & Buy Now Buttons -->
            <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-full); padding: 0 0.5rem;">
                    <button type="button" onclick="document.getElementById('productQtyInput').stepDown()" style="background: none; border: none; color: #fff; width: 32px; height: 32px; cursor: pointer;">-</button>
                    <input type="number" id="productQtyInput" value="1" min="1" max="<?= $product['stock_quantity'] ?>" style="width: 45px; background: transparent; border: none; text-align: center; color: #fff; font-weight: 700;">
                    <button type="button" onclick="document.getElementById('productQtyInput').stepUp()" style="background: none; border: none; color: #fff; width: 32px; height: 32px; cursor: pointer;">+</button>
                </div>

                <button class="btn-primary add-cart-btn" data-product-id="<?= $product['id'] ?>" style="flex: 1; min-width: 150px; justify-content: center; font-size: 1rem;">
                    <i class="fa-solid fa-cart-plus"></i> Add To Cart
                </button>

                <a href="product-details.php?id=<?= $product['id'] ?>&action=buy_now" class="btn-primary" style="background: linear-gradient(135deg, var(--emerald), #059669); flex: 1; min-width: 150px; justify-content: center; font-size: 1rem;">
                    <i class="fa-solid fa-bolt"></i> Buy Now
                </a>
            </div>

            <!-- Specifications Table -->
            <?php if (!empty($specs)): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem;">
                    <h4 style="font-family: var(--font-heading); margin-bottom: 0.75rem; color: var(--primary);"><i class="fa-solid fa-list-check"></i> Technical Specs</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">
                        <?php foreach ($specs as $key => $val): ?>
                            <div>
                                <strong style="color: var(--text-muted); text-transform: uppercase;"><?= htmlspecialchars($key) ?>:</strong>
                                <span style="color: #fff; margin-left: 0.3rem;"><?= htmlspecialchars($val) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 💬 Ask AI About This Product Widget -->
    <section class="my-5" style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 2rem;">
        <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--neon-pink)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div>
                <h3 style="font-family: var(--font-heading); font-size: 1.4rem; color: #fff;">Ask AI About This Product</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem;">Instant answers based on technical specifications and customer reviews</p>
            </div>
        </div>

        <form id="productQaForm" style="display: flex; gap: 0.75rem; margin-bottom: 1rem;">
            <input type="text" id="productQaInput" class="form-control" placeholder="Ask e.g., Is this good for programming and gaming?" required>
            <button type="submit" class="btn-primary" style="padding: 0.75rem 1.5rem;"><i class="fa-solid fa-paper-plane"></i> Ask AI</button>
        </form>

        <div id="productQaAnswer" style="display: none; background: var(--bg-surface); border: 1px solid var(--border-highlight); border-radius: var(--radius-md); padding: 1rem; font-size: 0.95rem; color: #fff;"></div>
    </section>

    <!-- 🛒 Frequently Bought Together Bundle Deal Box -->
    <?php if (!empty($frequentlyBought['accessories'])): ?>
        <section class="my-5" style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 2.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 1.5rem;"><i class="fa-solid fa-layer-group text-emerald"></i> Frequently Bought Together</h3>
            
            <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                <!-- Main Product -->
                <div style="display: flex; align-items: center; gap: 1rem; background: var(--bg-surface); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" style="width: 60px; height: 60px; object-fit: contain;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.9rem; color: #fff; max-width: 180px;"><?= htmlspecialchars($product['name']) ?></div>
                        <strong style="color: var(--primary);"><?= formatPrice($product['price']) ?></strong>
                    </div>
                </div>

                <div style="font-size: 1.5rem; color: var(--text-muted);">+</div>

                <!-- Accessories -->
                <?php foreach ($frequentlyBought['accessories'] as $acc): ?>
                    <div style="display: flex; align-items: center; gap: 1rem; background: var(--bg-surface); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <img src="<?= htmlspecialchars($acc['image_url']) ?>" style="width: 60px; height: 60px; object-fit: contain;">
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: #fff; max-width: 180px;"><?= htmlspecialchars($acc['name']) ?></div>
                            <strong style="color: var(--primary);"><?= formatPrice($acc['price']) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="font-size: 1.5rem; color: var(--text-muted);">=</div>

                <!-- Bundle Price & CTA -->
                <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(99, 102, 241, 0.15)); padding: 1.25rem 1.75rem; border-radius: var(--radius-md); border: 1px solid var(--emerald); text-align: center;">
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Bundle Price (Save <?= formatPrice($frequentlyBought['savings']) ?>)</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--emerald); margin: 0.2rem 0;"><?= formatPrice($frequentlyBought['bundle_discount_price']) ?></div>
                    <button onclick="addBundleToCart([<?= $product['id'] ?>, <?= implode(',', array_column($frequentlyBought['accessories'], 'id')) ?>])" class="btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.85rem;">
                        <i class="fa-solid fa-cart-plus"></i> Buy All 3 Bundle
                    </button>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ⭐ AI Review Sentiment Analysis Summary Card -->
    <?php if ($aiReviewSummary): ?>
        <section class="my-5" style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h3 style="font-family: var(--font-heading); font-size: 1.5rem;">
                    <i class="fa-solid fa-wand-magic-sparkles text-glow"></i> AI Review Sentiment Summary
                </h3>
                <span style="background: rgba(245, 158, 11, 0.15); color: var(--amber); font-size: 0.85rem; font-weight: 700; padding: 0.35rem 0.85rem; border-radius: 99px;">
                    Analyzed <?= $aiReviewSummary['total_analyzed'] ?> verified customer reviews
                </span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1.5rem;">
                <!-- Positive Pros -->
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-md); padding: 1.25rem;">
                    <h4 style="color: #34d399; margin-bottom: 0.75rem;"><i class="fa-solid fa-thumbs-up"></i> Positive Highlights</h4>
                    <ul style="list-style: none; padding: 0; font-size: 0.9rem; color: var(--text-secondary);">
                        <?php foreach ($aiReviewSummary['pros'] as $pro): ?>
                            <li style="margin-bottom: 0.5rem;">✓ <?= htmlspecialchars($pro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Negative Cons -->
                <div style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.3); border-radius: var(--radius-md); padding: 1.25rem;">
                    <h4 style="color: #fb7185; margin-bottom: 0.75rem;"><i class="fa-solid fa-thumbs-down"></i> Things to Keep in Mind</h4>
                    <ul style="list-style: none; padding: 0; font-size: 0.9rem; color: var(--text-secondary);">
                        <?php foreach ($aiReviewSummary['cons'] as $con): ?>
                            <li style="margin-bottom: 0.5rem;">• <?= htmlspecialchars($con) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div style="background: var(--bg-surface); border-left: 4px solid var(--primary); padding: 1rem 1.25rem; border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                <strong style="color: var(--primary);">AI Verdict:</strong>
                <span style="color: #fff; font-size: 0.95rem; margin-left: 0.4rem;"><?= htmlspecialchars($aiReviewSummary['verdict']) ?></span>
            </div>
        </section>
    <?php endif; ?>

    <!-- Customer Reviews Section -->
    <div style="margin-top: 4rem;">
        <h2 class="section-title">Verified Customer Reviews (<?= count($reviews) ?>)</h2>

        <!-- Flash Messages -->
        <?php if ($msg = getFlash('success')): ?>
            <div class="flash-alert success"><?= $msg ?></div>
        <?php endif; ?>
        <?php if ($msg = getFlash('error')): ?>
            <div class="flash-alert error"><?= $msg ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 3rem; margin-top: 2rem;">
            <!-- Reviews List -->
            <div>
                <?php if (empty($reviews)): ?>
                    <p style="color: var(--text-secondary);">No customer reviews yet. Be the first to write a review!</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <?php foreach ($reviews as $rev): ?>
                            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <strong><?= htmlspecialchars($rev['user_name']) ?></strong>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                                </div>
                                <div style="margin-bottom: 0.5rem;"><?= renderRatingStars($rev['rating']) ?></div>
                                <p style="color: var(--text-secondary); font-size: 0.95rem;"><?= htmlspecialchars($rev['comment']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submit Review Form -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; height: fit-content;">
                <h3 style="font-family: var(--font-heading); font-size: 1.2rem; margin-bottom: 1rem;">Write a Review</h3>
                <form action="product-details.php?id=<?= $productId ?>" method="POST">
                    <div class="form-group">
                        <label>Rating (1 to 5 Stars)</label>
                        <select name="rating" class="form-control" required>
                            <option value="5">5 Stars - Outstanding</option>
                            <option value="4">4 Stars - Very Good</option>
                            <option value="3">3 Stars - Average</option>
                            <option value="2">2 Stars - Poor</option>
                            <option value="1">1 Star - Terrible</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Your Review</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience with this product..." required></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn-primary" style="width: 100%; justify-content: center;">Submit Review</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function addToCompare(productId) {
    fetch('api/compare_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', product_id: productId })
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message, 'success');
        const badge = document.getElementById('headerCompareBadge');
        if (badge) badge.textContent = data.compare_count;
    });
}

function addBundleToCart(productIds) {
    let promises = productIds.map(id => {
        return fetch('api/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', product_id: id, quantity: 1 })
        });
    });
    Promise.all(promises).then(() => {
        showToast('All 3 bundle items added to cart!', 'success');
        setTimeout(() => window.location.href = 'cart.php', 1000);
    });
}

document.getElementById('productQaForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const q = document.getElementById('productQaInput').value;
    const ansBox = document.getElementById('productQaAnswer');
    ansBox.style.display = 'block';
    ansBox.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> AI is evaluating specifications...';

    fetch('api/ai_product_qa.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: <?= $productId ?>, question: q })
    })
    .then(res => res.json())
    .then(data => {
        ansBox.innerHTML = `<strong>🤖 AI Response:</strong> ${data.answer}`;
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
