<?php
/**
 * Admin - Smart Inventory & Stock Health Studio
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ai_engine.php';

requireAdmin();

$alerts = AIEngine::getInventoryRestockAlerts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Inventory | NextGen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon" style="background: linear-gradient(135deg, var(--amber), var(--rose));"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">SMART INVENTORY</span>
                </div>
            </a>
            <a href="dashboard.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">&larr; Dashboard</a>
        </div>
    </header>

    <div class="container my-5">
        <div style="margin-bottom: 2.5rem;">
            <h1 class="section-title"><i class="fa-solid fa-warehouse text-glow"></i> Smart Inventory & Restock Intelligence</h1>
            <p style="color: var(--text-secondary);">Real-time stock level monitoring and estimated days-to-stockout predictions</p>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.75rem;">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>AI Health Status</th>
                        <th>Est. Days to Stockout</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alerts as $item): ?>
                        <tr>
                            <td><strong style="color: #fff;"><?= htmlspecialchars($item['name']) ?></strong></td>
                            <td><span style="color: var(--primary); font-weight: 600;"><?= htmlspecialchars($item['category']) ?></span></td>
                            <td style="font-weight: 800; font-size: 1.1rem;"><?= $item['stock'] ?> units</td>
                            <td>
                                <span style="background: rgba(99, 102, 241, 0.15); color: var(--<?= $item['badge'] ?>); font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 99px;">
                                    <?= $item['status'] ?>
                                </span>
                            </td>
                            <td style="font-weight: 700; color: #fff;">~ <?= $item['est_days_remaining'] ?> Days</td>
                            <td>
                                <a href="edit-product.php?id=<?= $item['id'] ?>" class="btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">
                                    <i class="fa-solid fa-boxes-packing"></i> Restock Stock
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
