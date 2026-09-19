<?php
/**
 * Admin - Order Management
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getConnection();

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitizeInput($_POST['order_status']);
    $trackingNum = sanitizeInput($_POST['tracking_number']);

    $stmt = $db->prepare("UPDATE orders SET order_status = ?, tracking_number = ? WHERE id = ?");
    $stmt->execute([$newStatus, $trackingNum, $orderId]);

    setFlash('success', 'Order #' . $orderId . ' updated to ' . $newStatus);
    header("Location: orders.php");
    exit;
}

// Fetch all orders
$orders = $db->query("SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management | NextGen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">ORDERS MANAGER</span>
                </div>
            </a>
            <a href="dashboard.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">&larr; Dashboard</a>
        </div>
    </header>

    <div class="container my-5">
        <h1 class="section-title mb-4">Customer Orders & Dispatch</h1>

        <?php if ($msg = getFlash('success')): ?>
            <div class="flash-alert success mb-4"><?= $msg ?></div>
        <?php endif; ?>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach ($orders as $ord): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <strong style="font-size: 1.2rem; color: #fff;">Order #<?= $ord['id'] ?></strong>
                            <span style="color: var(--text-muted); font-size: 0.85rem; margin-left: 0.5rem;">by <?= htmlspecialchars($ord['shipping_name'] ?: $ord['full_name']) ?> (<?= htmlspecialchars($ord['shipping_phone']) ?>)</span>
                        </div>
                        <div style="font-size: 1.2rem; font-weight: 800; color: var(--emerald);"><?= formatPrice($ord['final_amount']) ?></div>
                    </div>

                    <form action="orders.php" method="POST" style="display: flex; gap: 1rem; align-items: center; background: var(--bg-surface); padding: 1rem; border-radius: var(--radius-md); flex-wrap: wrap;">
                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">

                        <div style="flex: 1; min-width: 180px;">
                            <label style="font-size: 0.75rem; color: var(--text-muted); display: block;">Tracking Number</label>
                            <input type="text" name="tracking_number" class="form-control" value="<?= htmlspecialchars($ord['tracking_number'] ?? '') ?>" placeholder="TRACK-NEXTGEN-xxxx">
                        </div>

                        <div style="flex: 1; min-width: 180px;">
                            <label style="font-size: 0.75rem; color: var(--text-muted); display: block;">Order Status</label>
                            <select name="order_status" class="form-control">
                                <option value="Processing" <?= $ord['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="Shipped" <?= $ord['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="Out for Delivery" <?= $ord['order_status'] === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                <option value="Delivered" <?= $ord['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="Cancelled" <?= $ord['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>

                        <button type="submit" name="update_order_status" class="btn-primary" style="padding: 0.7rem 1.25rem; align-self: flex-end;">
                            Update Order
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>
