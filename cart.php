<?php
/**
 * Shopping Cart Management Page
 */

require_once __DIR__ . '/includes/header.php';

$db = Database::getConnection();
$sessId = getSessionId();
$userId = getCurrentUserId();

// Fetch Cart Items
if ($userId) {
    $stmt = $db->prepare("SELECT c.id as cart_id, c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? OR c.session_id = ?");
    $stmt->execute([$userId, $sessId]);
} else {
    $stmt = $db->prepare("SELECT c.id as cart_id, c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ?");
    $stmt->execute([$sessId]);
}
$cartItems = $stmt->fetchAll();

// Calculate Subtotals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}

// Applied Coupon Logic
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discount = 0;
if ($appliedCoupon) {
    if ($subtotal >= $appliedCoupon['min_order_amount']) {
        $discount = min($appliedCoupon['max_discount'], ($subtotal * ($appliedCoupon['discount_percentage'] / 100)));
    } else {
        unset($_SESSION['applied_coupon']);
        $appliedCoupon = null;
    }
}

$finalTotal = max(0, $subtotal - $discount);
?>

<div class="container my-5">
    <h1 class="section-title mb-4"><i class="fa-solid fa-bag-shopping"></i> Your Shopping Cart</h1>

    <?php if (empty($cartItems)): ?>
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 4rem 2rem; text-align: center; max-width: 600px; margin: 0 auto;">
            <div style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"><i class="fa-solid fa-cart-flatbed-empty"></i></div>
            <h2>Your cart is currently empty</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem;">Explore our catalog or ask the AI Assistant for recommendations!</p>
            <a href="products.php" class="btn-primary">Browse Catalog</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 360px; gap: 2.5rem;">
            <!-- Cart Items Table -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <div>
                                        <a href="product-details.php?id=<?= $item['id'] ?>" style="font-weight: 600; color: #fff;"><?= htmlspecialchars($item['name']) ?></a>
                                    </div>
                                </td>
                                <td style="font-weight: 600;"><?= formatPrice($item['price']) ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 99px; width: fit-content; padding: 0 0.25rem;">
                                        <button onclick="updateCartQty(<?= $item['cart_id'] ?>, <?= $item['quantity'] - 1 ?>)" style="background: none; border: none; color: #fff; width: 28px; height: 28px; cursor: pointer;">-</button>
                                        <span style="padding: 0 0.5rem; font-weight: 700;"><?= $item['quantity'] ?></span>
                                        <button onclick="updateCartQty(<?= $item['cart_id'] ?>, <?= $item['quantity'] + 1 ?>)" style="background: none; border: none; color: #fff; width: 28px; height: 28px; cursor: pointer;">+</button>
                                    </div>
                                </td>
                                <td style="font-weight: 700; color: var(--primary);"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                                <td>
                                    <button onclick="removeCartItem(<?= $item['cart_id'] ?>)" style="background: none; border: none; color: var(--rose); cursor: pointer; font-size: 1.1rem;" title="Remove Item">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Cart Summary & Coupon Side Box -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Coupon Box -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
                    <h4 style="font-family: var(--font-heading); margin-bottom: 0.75rem;"><i class="fa-solid fa-ticket"></i> Apply Promo Coupon</h4>
                    <form id="couponForm" style="display: flex; gap: 0.5rem;">
                        <input type="text" id="couponCodeInput" class="form-control" placeholder="Code e.g. NEXTGEN10" value="<?= $appliedCoupon ? htmlspecialchars($appliedCoupon['code']) : '' ?>" style="text-transform: uppercase;">
                        <button type="submit" class="btn-secondary" style="padding: 0.5rem 1rem;">Apply</button>
                    </form>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">Try promo code: <strong>NEXTGEN10</strong> or <strong>WELCOME20</strong></small>
                </div>

                <!-- Summary Box -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 1.5rem;">
                    <h3 style="font-family: var(--font-heading); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">Order Summary</h3>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: var(--text-secondary);">
                        <span>Bag Subtotal:</span>
                        <span style="color: #fff; font-weight: 600;"><?= formatPrice($subtotal) ?></span>
                    </div>

                    <?php if ($appliedCoupon): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: var(--emerald);">
                            <span>Discount (<?= $appliedCoupon['code'] ?>):</span>
                            <span style="font-weight: 700;">-<?= formatPrice($discount) ?></span>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--text-secondary);">
                        <span>Estimated Shipping:</span>
                        <span style="color: var(--emerald); font-weight: 600;">FREE</span>
                    </div>

                    <hr style="border-color: var(--border-color); margin-bottom: 1rem;">

                    <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; font-size: 1.3rem;">
                        <strong>Total Amount:</strong>
                        <strong style="color: var(--primary);"><?= formatPrice($finalTotal) ?></strong>
                    </div>

                    <a href="checkout.php" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem;">
                        Proceed to Checkout <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateCartQty(cartId, qty) {
    fetch('api/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update', cart_id: cartId, quantity: qty })
    }).then(() => window.location.reload());
}

function removeCartItem(cartId) {
    fetch('api/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'remove', cart_id: cartId })
    }).then(() => window.location.reload());
}

document.getElementById('couponForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const code = document.getElementById('couponCodeInput').value;
    fetch('api/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'apply_coupon', coupon_code: code })
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') setTimeout(() => window.location.reload(), 1000);
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
