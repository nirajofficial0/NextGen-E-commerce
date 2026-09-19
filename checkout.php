<?php
/**
 * Checkout & Payment Modal Flow
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db = Database::getConnection();
$userId = getCurrentUserId();
$currentUser = getCurrentUser();

// Fetch Cart
$stmt = $db->prepare("SELECT c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? OR c.session_id = ?");
$stmt->execute([$userId, getSessionId()]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discount = 0;
if ($appliedCoupon && $subtotal >= $appliedCoupon['min_order_amount']) {
    $discount = min($appliedCoupon['max_discount'], ($subtotal * ($appliedCoupon['discount_percentage'] / 100)));
}
$finalTotal = max(0, $subtotal - $discount);

// Handle Order Submission BEFORE HTML Output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name = sanitizeInput($_POST['shipping_name']);
    $phone = sanitizeInput($_POST['shipping_phone']);
    $address = sanitizeInput($_POST['shipping_address']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $pincode = sanitizeInput($_POST['pincode']);
    $payMethod = sanitizeInput($_POST['payment_method']);

    $trackingNum = 'TRACK-NEXTGEN-' . rand(1000, 9999);

    // Create Order
    $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, shipping_name, shipping_phone, shipping_address, city, state, pincode, tracking_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $subtotal,
        $discount,
        $finalTotal,
        $payMethod,
        'Paid',
        'Processing',
        $name,
        $phone,
        $address,
        $city,
        $state,
        $pincode,
        $trackingNum
    ]);
    $orderId = $db->lastInsertId();

    // Create Order Items
    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($cartItems as $ci) {
        $itemStmt->execute([
            $orderId,
            $ci['id'],
            $ci['name'],
            $ci['price'],
            $ci['quantity'],
            ($ci['price'] * $ci['quantity'])
        ]);
    }

    // Clear Cart & Coupon
    $db->prepare("DELETE FROM cart WHERE user_id = ? OR session_id = ?")->execute([$userId, getSessionId()]);
    unset($_SESSION['applied_coupon']);

    setFlash('success', 'Order #' . $orderId . ' placed successfully! Tracking Number: ' . $trackingNum);
    header("Location: thank-you.php?order_id=" . $orderId);
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <h1 class="section-title mb-4"><i class="fa-solid fa-credit-card"></i> Checkout</h1>

    <form action="checkout.php" method="POST" id="checkoutForm">
        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 3rem;">
            <!-- Shipping Details & Payment Selection -->
            <div>
                <!-- Shipping Address Card -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem;">
                    <h3 style="font-family: var(--font-heading); margin-bottom: 1.25rem;"><i class="fa-solid fa-location-dot text-primary"></i> Shipping Address</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="shipping_name" class="form-control" value="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="shipping_phone" class="form-control" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Street Address</label>
                        <input type="text" name="shipping_address" class="form-control" value="<?= htmlspecialchars($currentUser['address'] ?? '') ?>" placeholder="Flat/House No, Building, Street Name" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($currentUser['city'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($currentUser['state'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($currentUser['pincode'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Card -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem;">
                    <h3 style="font-family: var(--font-heading); margin-bottom: 1.25rem;"><i class="fa-solid fa-wallet text-accent"></i> Payment Method</h3>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 1rem; background: var(--bg-surface); border: 1px solid var(--border-highlight); padding: 1rem; border-radius: var(--radius-md); cursor: pointer;">
                            <input type="radio" name="payment_method" value="UPI / QR Code" checked style="accent-color: var(--primary);">
                            <div>
                                <strong style="color: #fff;"><i class="fa-solid fa-qrcode text-emerald"></i> Instant UPI / QR Code (Recommended)</strong>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">GPay, PhonePe, Paytm, BHIM QR Scan</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; gap: 1rem; background: var(--bg-surface); border: 1px solid var(--border-color); padding: 1rem; border-radius: var(--radius-md); cursor: pointer;">
                            <input type="radio" name="payment_method" value="Credit/Debit Card" style="accent-color: var(--primary);">
                            <div>
                                <strong style="color: #fff;"><i class="fa-solid fa-credit-card text-primary"></i> Credit / Debit Card</strong>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">Visa, MasterCard, RuPay</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; gap: 1rem; background: var(--bg-surface); border: 1px solid var(--border-color); padding: 1rem; border-radius: var(--radius-md); cursor: pointer;">
                            <input type="radio" name="payment_method" value="Cash on Delivery" style="accent-color: var(--primary);">
                            <div>
                                <strong style="color: #fff;"><i class="fa-solid fa-money-bill-wave text-amber"></i> Cash on Delivery (COD)</strong>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">Pay when your order arrives</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Summary Side Column -->
            <div>
                <div style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 1.5rem; position: sticky; top: 100px;">
                    <h3 style="font-family: var(--font-heading); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">Summary (<?= count($cartItems) ?> Items)</h3>

                    <div style="max-height: 250px; overflow-y: auto; margin-bottom: 1rem;">
                        <?php foreach ($cartItems as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; font-size: 0.9rem;">
                                <div>
                                    <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($item['name']) ?></div>
                                    <small style="color: var(--text-muted);"><?= $item['quantity'] ?> x <?= formatPrice($item['price']) ?></small>
                                </div>
                                <strong style="color: #fff;"><?= formatPrice($item['price'] * $item['quantity']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr style="border-color: var(--border-color); margin-bottom: 1rem;">

                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--text-secondary);">
                        <span>Subtotal:</span>
                        <span><?= formatPrice($subtotal) ?></span>
                    </div>

                    <?php if ($discount > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--emerald);">
                            <span>Discount:</span>
                            <span>-<?= formatPrice($discount) ?></span>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; font-size: 1.3rem;">
                        <strong>Payable Amount:</strong>
                        <strong style="color: var(--emerald);"><?= formatPrice($finalTotal) ?></strong>
                    </div>

                    <button type="submit" name="place_order" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.9rem; font-size: 1.1rem;">
                        <i class="fa-solid fa-lock"></i> Complete Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
