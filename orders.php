<?php
/**
 * Order History & Live Tracking Stepper Page with Cancel/Return Actions
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db = Database::getConnection();
$userId = getCurrentUserId();

// Handle Order Cancellation / Return Request BEFORE HTML Output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_action'])) {
    $orderId = (int)$_POST['order_id'];
    $actionType = $_POST['action_type']; // 'cancel' or 'return'

    if ($actionType === 'cancel') {
        $stmt = $db->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userId]);
        setFlash('success', 'Order #' . $orderId . ' has been cancelled successfully.');
    } elseif ($actionType === 'return') {
        $stmt = $db->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userId]);
        setFlash('success', 'Return & Refund request initiated for Order #' . $orderId . '. Refund will process in 2-3 business days.');
    }

    header("Location: orders.php");
    exit;
}

// Fetch User Orders
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <h1 class="section-title mb-4"><i class="fa-solid fa-box-archive"></i> Order History & Tracking</h1>

    <?php if ($msg = getFlash('success')): ?>
        <div class="flash-alert success mb-4"><?= $msg ?></div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 4rem 2rem; text-align: center; max-width: 600px; margin: 0 auto;">
            <div style="font-size: 3.5rem; color: var(--text-muted); margin-bottom: 1rem;"><i class="fa-solid fa-truck-ramp-box"></i></div>
            <h2>No orders placed yet</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Start shopping and track your delivery in real-time!</p>
            <a href="products.php" class="btn-primary">Browse Catalog</a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php foreach ($orders as $order): ?>
                <?php
                $itemStmt = $db->prepare("SELECT oi.*, p.image_url FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                $itemStmt->execute([$order['id']]);
                $items = $itemStmt->fetchAll();

                $status = $order['order_status'];
                $steps = ['Processing', 'Shipped', 'Out for Delivery', 'Delivered'];
                $currentStepIdx = array_search($status, $steps);
                if ($currentStepIdx === false) $currentStepIdx = 0;
                ?>

                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.75rem;">
                    <!-- Order Header Info -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <span style="font-size: 1.2rem; font-weight: 700; color: #fff;">Order #<?= $order['id'] ?></span>
                            <span style="color: var(--text-muted); font-size: 0.85rem; margin-left: 0.5rem;">Placed on <?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="background: var(--bg-surface); border: 1px solid var(--border-highlight); color: var(--primary); padding: 0.35rem 0.85rem; border-radius: 99px; font-weight: 700; font-size: 0.85rem;">
                                Tracking: <?= htmlspecialchars($order['tracking_number'] ?: 'TRACK-NEXTGEN-PENDING') ?>
                            </span>

                            <!-- Cancel / Return Actions -->
                            <?php if ($order['order_status'] === 'Processing'): ?>
                                <form action="orders.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="action_type" value="cancel">
                                    <button type="submit" name="order_action" class="btn-secondary" style="padding: 0.35rem 0.85rem; font-size: 0.8rem; color: var(--rose);" onclick="return confirm('Cancel this order?')">
                                        Cancel Order
                                    </button>
                                </form>
                            <?php elseif ($order['order_status'] === 'Delivered'): ?>
                                <form action="orders.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="action_type" value="return">
                                    <button type="submit" name="order_action" class="btn-secondary" style="padding: 0.35rem 0.85rem; font-size: 0.8rem; color: var(--amber);" onclick="return confirm('Initiate Return & Refund request?')">
                                        Return & Refund
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Visual Order Stepper Timeline -->
                    <?php if ($order['order_status'] === 'Cancelled'): ?>
                        <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3); color: #fb7185; padding: 0.75rem 1rem; border-radius: var(--radius-md); font-weight: 600; text-align: center; margin-bottom: 1.5rem;">
                            <i class="fa-solid fa-ban"></i> Order Cancelled / Refunded
                        </div>
                    <?php else: ?>
                        <div class="order-stepper mb-4">
                            <?php foreach ($steps as $idx => $stepName): ?>
                                <div class="step-item <?= $idx <= $currentStepIdx ? 'active' : '' ?>">
                                    <div class="step-icon">
                                        <?php if ($idx < $currentStepIdx): ?>
                                            <i class="fa-solid fa-check"></i>
                                        <?php elseif ($idx === $currentStepIdx): ?>
                                            <i class="fa-solid fa-circle-dot"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-circle"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 0.8rem; font-weight: 600; color: <?= $idx <= $currentStepIdx ? '#fff' : 'var(--text-muted)' ?>;"><?= $stepName ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Items List -->
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
                        <?php foreach ($items as $it): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface); padding: 0.75rem 1rem; border-radius: var(--radius-md);">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="<?= htmlspecialchars($it['image_url'] ?: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=100&q=80') ?>" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px;">
                                    <div>
                                        <div style="font-weight: 600; color: #fff; font-size: 0.95rem;"><?= htmlspecialchars($it['product_name']) ?></div>
                                        <small style="color: var(--text-muted);"><?= $it['quantity'] ?> x <?= formatPrice($it['price']) ?></small>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <strong style="color: var(--primary);"><?= formatPrice($it['subtotal']) ?></strong>
                                    <a href="product-details.php?id=<?= $it['product_id'] ?>" class="btn-secondary" style="padding: 0.25rem 0.65rem; font-size: 0.75rem;">Buy Again</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Shipping Address & Total -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid var(--border-color); padding-top: 1rem; font-size: 0.9rem;">
                        <div style="color: var(--text-secondary);">
                            <strong>Delivery Address:</strong><br>
                            <?= htmlspecialchars($order['shipping_name']) ?> (<?= htmlspecialchars($order['shipping_phone']) ?>)<br>
                            <?= htmlspecialchars($order['shipping_address']) ?>, <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['state']) ?> - <?= htmlspecialchars($order['pincode']) ?>
                        </div>
                        <div style="text-align: right;">
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Paid via <?= htmlspecialchars($order['payment_method']) ?></div>
                            <div style="font-size: 1.3rem; font-weight: 800; color: #fff;">Total: <?= formatPrice($order['final_amount']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
