<?php
/**
 * Admin - AI Sales & Demand Forecasting Studio
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ai_engine.php';

requireAdmin();

$forecast = AIEngine::getDemandForecast();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Demand Forecasting | NextGen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon" style="background: linear-gradient(135deg, var(--emerald), var(--primary));"><i class="fa-solid fa-chart-line-up"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">DEMAND FORECAST</span>
                </div>
            </a>
            <a href="dashboard.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">&larr; Dashboard</a>
        </div>
    </header>

    <div class="container my-5">
        <div style="margin-bottom: 2.5rem;">
            <h1 class="section-title"><i class="fa-solid fa-wand-magic-sparkles text-glow"></i> Predictive AI Demand & Sales Forecasting</h1>
            <p style="color: var(--text-secondary);">AI model projection of upcoming monthly product demand and inventory surge risks</p>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.75rem;">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Current Trend</th>
                        <th>Predicted Next Month Demand</th>
                        <th>Stockout Risk</th>
                        <th>AI Recommendation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forecast as $f): ?>
                        <tr>
                            <td><strong style="color: #fff; font-size: 1rem;"><?= htmlspecialchars($f['category']) ?></strong></td>
                            <td style="color: var(--emerald); font-weight: 700;"><?= $f['current_sales_trend'] ?></td>
                            <td style="font-size: 1.2rem; font-weight: 800; color: var(--primary);"><?= $f['predicted_next_month_demand'] ?></td>
                            <td>
                                <span style="background: <?= $f['stockout_risk'] === 'High' ? 'rgba(244, 63, 94, 0.2)' : 'rgba(245, 158, 11, 0.2)' ?>; color: <?= $f['stockout_risk'] === 'High' ? 'var(--rose)' : 'var(--amber)' ?>; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 99px;">
                                    <?= $f['stockout_risk'] ?> Risk
                                </span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-secondary); max-width: 320px;"><?= htmlspecialchars($f['ai_recommendation']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
