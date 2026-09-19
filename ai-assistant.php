<?php
/**
 * Dedicated AI Shopping Assistant & Requirement Analyzer Page
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/ai_engine.php';

$queryPrompt = trim($_GET['prompt'] ?? '');
$aiResponse = null;

if (!empty($queryPrompt)) {
    $aiResponse = AIEngine::getRecommendationsForPrompt($queryPrompt, 6);
}
?>

<div class="container my-5">
    <div style="text-align: center; max-width: 750px; margin: 0 auto 3rem auto;">
        <div style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--neon-pink)); color: #fff; font-size: 2rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; box-shadow: var(--shadow-glow);">
            <i class="fa-solid fa-brain"></i>
        </div>
        <h1 style="font-family: var(--font-heading); font-size: 2.8rem; font-weight: 800; margin-bottom: 1rem;">
            NextGen AI Shopping Assistant
        </h1>
        <p style="color: var(--text-secondary); font-size: 1.15rem;">
            Enter your budget, intended usage, and feature priorities in natural everyday language. Our match engine will calculate exact percentage compatibility for catalog items.
        </p>
    </div>

    <!-- Interactive Query Input Form -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 2rem; max-width: 800px; margin: 0 auto 3rem auto; box-shadow: var(--shadow-card);">
        <form action="ai-assistant.php" method="GET" style="display: flex; flex-direction: column; gap: 1rem;">
            <textarea name="prompt" rows="3" class="form-control" style="font-size: 1.1rem; line-height: 1.6;" placeholder="Example: I need a gaming laptop under ₹70,000 with RTX graphics card and 16GB RAM for coding..." required><?= htmlspecialchars($queryPrompt) ?></textarea>
            
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <span style="font-size: 0.8rem; color: var(--text-muted); align-self: center;">Popular:</span>
                    <a href="ai-assistant.php?prompt=Coding+laptop+under+60000+with+16GB+RAM" class="prompt-chip">💻 Coding Laptop (₹60k)</a>
                    <a href="ai-assistant.php?prompt=Noise+cancelling+headphones+under+5000" class="prompt-chip">🎧 ANC Headphones (₹5k)</a>
                    <a href="ai-assistant.php?prompt=Flagship+5G+camera+phone+under+80000" class="prompt-chip">📱 5G Flagship Phone</a>
                </div>

                <button type="submit" class="btn-primary" style="padding: 0.85rem 2rem;">
                    <i class="fa-solid fa-sparkles"></i> Analyze & Match
                </button>
            </div>
        </form>
    </div>

    <!-- AI Recommendation Results -->
    <?php if ($aiResponse): ?>
        <div style="max-width: 1000px; margin: 0 auto;">
            <!-- Parsed Requirements Overview Card -->
            <div style="background: rgba(99, 102, 241, 0.1); border: 1px solid var(--border-highlight); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem;">
                <h3 style="font-family: var(--font-heading); color: #c7d2fe; margin-bottom: 0.75rem;"><i class="fa-solid fa-microchip"></i> Extracted Requirements Breakdown</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; font-size: 0.95rem;">
                    <div><strong>Target Category:</strong> <?= $aiResponse['requirement']['category_slug'] ? ucfirst(str_replace('-', ' ', $aiResponse['requirement']['category_slug'])) : 'All Tech' ?></div>
                    <div><strong>Max Budget:</strong> <?= $aiResponse['requirement']['budget'] ? formatPrice($aiResponse['requirement']['budget']) : 'No Limit' ?></div>
                    <div><strong>Usage Intent:</strong> <?= !empty($aiResponse['requirement']['purposes']) ? implode(', ', $aiResponse['requirement']['purposes']) : 'General' ?></div>
                    <div><strong>Priority Specs:</strong> <?= !empty($aiResponse['requirement']['features']) ? implode(', ', $aiResponse['requirement']['features']) : 'Standard' ?></div>
                </div>
            </div>

            <h2 class="section-title mb-4">Ranked Product Matches</h2>

            <div class="products-grid">
                <?php foreach ($aiResponse['recommendations'] as $product): ?>
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
                            
                            <div style="background: var(--bg-surface); border-radius: var(--radius-sm); padding: 0.5rem; margin-bottom: 0.75rem; font-size: 0.75rem;">
                                <?php foreach ($product['ai_reasons'] as $reason): ?>
                                    <div style="color: #34d399; margin-bottom: 0.2rem;"><?= htmlspecialchars($reason) ?></div>
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
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
