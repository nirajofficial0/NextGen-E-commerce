<?php
/**
 * NextGen Product Comparison Studio Page
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$compareIds = $_SESSION['compare_list'] ?? [];
$db = Database::getConnection();

$products = [];
if (!empty($compareIds)) {
    $placeholders = implode(',', array_fill(0, count($compareIds), '?'));
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id IN ($placeholders)");
    $stmt->execute($compareIds);
    $products = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="section-title"><i class="fa-solid fa-code-compare text-primary"></i> Product Comparison Studio</h1>
            <p style="color: var(--text-secondary);">Compare specifications, prices, and customer ratings side-by-side</p>
        </div>

        <?php if (!empty($products)): ?>
            <button onclick="clearCompareList()" class="btn-secondary" style="color: var(--rose);">
                <i class="fa-solid fa-trash-can"></i> Clear Comparison List
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($products)): ?>
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 4rem 2rem; text-align: center; max-width: 600px; margin: 0 auto;">
            <div style="font-size: 3.5rem; color: var(--text-muted); margin-bottom: 1rem;"><i class="fa-solid fa-scale-balanced"></i></div>
            <h2>No products selected for comparison</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Click the comparison icon <i class="fa-solid fa-code-compare"></i> on any product card to add it here!</p>
            <a href="products.php" class="btn-primary">Browse Products Catalog</a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
            <table class="cart-table" style="min-width: 750px;">
                <thead>
                    <tr>
                        <th style="width: 200px;">Features</th>
                        <?php foreach ($products as $prod): ?>
                            <th style="text-align: center; min-width: 220px;">
                                <div style="position: relative; margin-bottom: 0.5rem;">
                                    <img src="<?= htmlspecialchars($prod['image_url']) ?>" style="width: 100px; height: 100px; object-fit: contain; margin: 0 auto 0.5rem auto; display: block; border-radius: 8px;">
                                    <button onclick="removeCompareItem(<?= $prod['id'] ?>)" style="position: absolute; top: 0; right: 0; background: var(--rose); color: #fff; border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer;" title="Remove">
                                        &times;
                                    </button>
                                </div>
                                <a href="product-details.php?id=<?= $prod['id'] ?>" style="font-weight: 700; color: #fff; font-size: 1rem;"><?= htmlspecialchars($prod['name']) ?></a>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Category</strong></td>
                        <?php foreach ($products as $prod): ?>
                            <td style="text-align: center; color: var(--primary); font-weight: 600;"><?= htmlspecialchars($prod['category_name']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Price</strong></td>
                        <?php foreach ($products as $prod): ?>
                            <td style="text-align: center; font-weight: 800; font-size: 1.2rem; color: var(--emerald);"><?= formatPrice($prod['price']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Customer Rating</strong></td>
                        <?php foreach ($products as $prod): ?>
                            <td style="text-align: center;"><?= renderRatingStars($prod['rating']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Stock Status</strong></td>
                        <?php foreach ($products as $prod): ?>
                            <td style="text-align: center;">
                                <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 0.8rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 99px;">
                                    <?= $prod['stock_quantity'] ?> units
                                </span>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Technical Specs</strong></td>
                        <?php foreach ($products as $prod): ?>
                            <?php $specs = json_decode($prod['specs_json'] ?? '{}', true) ?: []; ?>
                            <td style="font-size: 0.85rem; color: var(--text-secondary);">
                                <?php foreach ($specs as $k => $v): ?>
                                    <div style="margin-bottom: 0.25rem;"><strong><?= htmlspecialchars($k) ?>:</strong> <?= htmlspecialchars($v) ?></div>
                                <?php endforeach; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Buy Action</strong></td>
                        <?php foreach ($products as $prod): ?>
                            <td style="text-align: center;">
                                <button class="btn-primary add-cart-btn" data-product-id="<?= $prod['id'] ?>" style="width: 100%; justify-content: center;">
                                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                </button>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function removeCompareItem(prodId) {
    fetch('api/compare_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'remove', product_id: prodId })
    }).then(() => window.location.reload());
}

function clearCompareList() {
    fetch('api/compare_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'clear' })
    }).then(() => window.location.reload());
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
