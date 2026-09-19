<?php
/**
 * Admin - Coupon Code Manager
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(trim(sanitizeInput($_POST['code'])));
    $percentage = (int)$_POST['discount_percentage'];
    $minOrder = (float)$_POST['min_order_amount'];
    $maxDisc = (float)$_POST['max_discount'];

    $stmt = $db->prepare("INSERT INTO coupons (code, discount_percentage, min_order_amount, max_discount) VALUES (?, ?, ?, ?)");
    $stmt->execute([$code, $percentage, $minOrder, $maxDisc]);

    setFlash('success', 'Coupon code ' . $code . ' created!');
    header("Location: coupons.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'toggle') {
    $cpId = (int)$_GET['id'];
    $db->prepare("UPDATE coupons SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?")->execute([$cpId]);
    setFlash('success', 'Coupon status updated.');
    header("Location: coupons.php");
    exit;
}

$coupons = $db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coupons | NextGen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon"><i class="fa-solid fa-ticket"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">PROMO COUPONS</span>
                </div>
            </a>
            <a href="dashboard.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">&larr; Dashboard</a>
        </div>
    </header>

    <div class="container my-5">
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 3rem;">
            <!-- Coupons List -->
            <div>
                <h1 class="section-title mb-4">Promo Coupons</h1>

                <?php if ($msg = getFlash('success')): ?>
                    <div class="flash-alert success mb-4"><?= $msg ?></div>
                <?php endif; ?>

                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Min Order</th>
                                <th>Max Discount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $cp): ?>
                                <tr>
                                    <td><strong style="color: var(--primary); font-family: var(--font-heading); font-size: 1.1rem;"><?= htmlspecialchars($cp['code']) ?></strong></td>
                                    <td style="font-weight: 700; color: var(--emerald);"><?= $cp['discount_percentage'] ?>% OFF</td>
                                    <td><?= formatPrice($cp['min_order_amount']) ?></td>
                                    <td><?= formatPrice($cp['max_discount']) ?></td>
                                    <td>
                                        <span style="background: <?= $cp['is_active'] ? 'rgba(16, 185, 129, 0.2)' : 'rgba(244, 63, 94, 0.2)' ?>; color: <?= $cp['is_active'] ? 'var(--emerald)' : 'var(--rose)' ?>; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 99px;">
                                            <?= $cp['is_active'] ? 'Active' : 'Disabled' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="coupons.php?action=toggle&id=<?= $cp['id'] ?>" style="color: var(--text-secondary); font-size: 0.85rem;"><i class="fa-solid fa-power-off"></i> Toggle</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create Coupon Form -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.75rem; height: fit-content;">
                <h3 style="font-family: var(--font-heading); margin-bottom: 1.25rem;"><i class="fa-solid fa-plus"></i> Create Promo Coupon</h3>

                <form action="coupons.php" method="POST">
                    <div class="form-group">
                        <label>Coupon Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. AI500" style="text-transform: uppercase;" required>
                    </div>

                    <div class="form-group">
                        <label>Discount Percentage (%)</label>
                        <input type="number" name="discount_percentage" class="form-control" min="1" max="90" placeholder="15" required>
                    </div>

                    <div class="form-group">
                        <label>Min Order Amount (₹)</label>
                        <input type="number" step="0.01" name="min_order_amount" class="form-control" value="1000" required>
                    </div>

                    <div class="form-group mb-4">
                        <label>Max Discount Amount (₹)</label>
                        <input type="number" step="0.01" name="max_discount" class="form-control" value="3000" required>
                    </div>

                    <button type="submit" name="add_coupon" class="btn-primary" style="width: 100%; justify-content: center;">Create Coupon</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
