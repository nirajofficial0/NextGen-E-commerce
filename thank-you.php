<?php
/**
 * NextGen E-Commerce Thank You & Order Confirmation Page
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$type = $_GET['type'] ?? 'order';
$orderId = (int)($_GET['order_id'] ?? 0);
$order = null;
$orderItems = [];

if ($orderId > 0) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if ($order) {
        $itemStmt = $db->prepare("SELECT oi.*, p.image_url FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $itemStmt->execute([$orderId]);
        $orderItems = $itemStmt->fetchAll();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div style="max-width: 750px; margin: 0 auto;">
        
        <?php if ($type === 'welcome'): ?>
            <!-- Welcome Thank You Card for New Registration -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 3.5rem 2rem; text-align: center; box-shadow: var(--shadow-card);">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--emerald), var(--primary)); color: #fff; font-size: 2.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; box-shadow: var(--shadow-glow);">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h1 style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; margin-bottom: 0.75rem; background: linear-gradient(90deg, #ffffff, #c7d2fe); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Welcome to NextGen AI Store!
                </h1>
                <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 2rem; max-width: 550px; margin-left: auto; margin-right: auto;">
                    Your account has been created successfully. You can now track orders, save wishlists, and receive personalized AI product recommendations!
                </p>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="ai-assistant.php" class="btn-primary" style="padding: 0.85rem 2rem;"><i class="fa-solid fa-robot"></i> Explore AI Assistant</a>
                    <a href="products.php" class="btn-secondary" style="padding: 0.85rem 2rem;"><i class="fa-solid fa-bag-shopping"></i> Start Shopping</a>
                </div>
            </div>

        <?php else: ?>
            <!-- Successful Order Placement Thank You Card -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 3rem 2rem; text-align: center; box-shadow: var(--shadow-card); margin-bottom: 2rem;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--emerald), #059669); color: #fff; font-size: 2.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; box-shadow: 0 0 30px rgba(16, 185, 129, 0.4);">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h1 style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">
                    Thank You for Your Order!
                </h1>
                <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 1.5rem;">
                    Your order has been placed successfully and is now being processed.
                </p>

                <?php if ($order): ?>
                    <div style="display: inline-flex; gap: 1.5rem; background: var(--bg-surface); border: 1px solid var(--border-highlight); border-radius: var(--radius-full); padding: 0.6rem 1.5rem; font-size: 0.95rem; margin-bottom: 1.5rem;">
                        <span>Order ID: <strong style="color: #fff;">#<?= $order['id'] ?></strong></span>
                        <span class="divider" style="color: var(--text-muted);">|</span>
                        <span>Tracking: <strong style="color: var(--primary);"><?= htmlspecialchars($order['tracking_number']) ?></strong></span>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="orders.php" class="btn-primary" style="padding: 0.85rem 1.75rem;"><i class="fa-solid fa-truck-fast"></i> Track Order Status</a>
                    <a href="products.php" class="btn-secondary" style="padding: 0.85rem 1.75rem;"><i class="fa-solid fa-arrow-left"></i> Continue Shopping</a>
                </div>
            </div>

            <!-- Order Summary Break-down -->
            <?php if ($order): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem;">
                    <h3 style="font-family: var(--font-heading); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;"><i class="fa-solid fa-receipt"></i> Order Details Summary</h3>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
                        <?php foreach ($orderItems as $it): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface); padding: 0.75rem 1rem; border-radius: var(--radius-md);">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="<?= htmlspecialchars($it['image_url'] ?: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=100&q=80') ?>" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px;">
                                    <div>
                                        <div style="font-weight: 600; color: #fff; font-size: 0.95rem;"><?= htmlspecialchars($it['product_name']) ?></div>
                                        <small style="color: var(--text-muted);"><?= $it['quantity'] ?> x <?= formatPrice($it['price']) ?></small>
                                    </div>
                                </div>
                                <strong style="color: var(--primary);"><?= formatPrice($it['subtotal']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                        <span>Total Paid:</span>
                        <span style="color: var(--emerald);"><?= formatPrice($order['final_amount']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
