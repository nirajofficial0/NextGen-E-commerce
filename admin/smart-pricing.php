<?php
/**
 * Admin - Smart Dynamic Pricing Module
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ai_engine.php';

requireAdmin();

$pricing = AIEngine::getDynamicPriceRecommendations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Pricing | NextGen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon" style="background: linear-gradient(135deg, var(--neon-pink), var(--accent));"><i class="fa-solid fa-tags"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">SMART PRICING</span>
                </div>
            </a>
            <a href="dashboard.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">&larr; Dashboard</a>
        </div>
    </header>

    <div class="container my-5">
        <div style="margin-bottom: 2.5rem;">
            <h1 class="section-title"><i class="fa-solid fa-chart-line text-glow"></i> AI Dynamic Pricing Simulation & Optimizer</h1>
            <p style="color: var(--text-secondary);">Market demand, competitor price indexing, and margin optimization recommendations</p>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.75rem;">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Current Price</th>
                        <th>Competitor Avg</th>
                        <th>Demand Level</th>
                        <th>AI Suggested Price</th>
                        <th>Projected Margin Delta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricing as $pr): ?>
                        <tr>
                            <td><strong style="color: #fff;"><?= htmlspecialchars($pr['name']) ?></strong></td>
                            <td style="font-weight: 700; color: #fff;"><?= formatPrice($pr['current_price']) ?></td>
                            <td style="color: var(--text-muted);"><?= formatPrice($pr['competitor_avg']) ?></td>
                            <td>
                                <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 99px;">
                                    <?= $pr['demand_level'] ?>
                                </span>
                            </td>
                            <td style="font-size: 1.1rem; font-weight: 800; color: var(--primary);"><?= formatPrice($pr['suggested_price']) ?></td>
                            <td style="font-weight: 700; color: var(--emerald);"><?= $pr['potential_revenue_change'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
