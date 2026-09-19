<?php
/**
 * AI Customer Analytics & Demand Intelligence Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ai_engine.php';

requireAdmin();

$analytics = AIEngine::getAICustomerAnalytics();
$segmentation = AIEngine::getCustomerSegmentation();
$db = Database::getConnection();

// Fetch raw AI logs
$reqLogs = $db->query("SELECT * FROM ai_requirements ORDER BY id DESC LIMIT 15")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Customer Analytics | NextGen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon" style="background: linear-gradient(135deg, var(--accent), var(--neon-pink));"><i class="fa-solid fa-brain"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">AI INTELLIGENCE</span>
                </div>
            </a>
            <a href="dashboard.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">&larr; Back to Dashboard</a>
        </div>
    </header>

    <div class="container my-5">
        <div style="margin-bottom: 2.5rem;">
            <h1 class="section-title"><i class="fa-solid fa-sparkles text-glow"></i> AI Customer Analytics & Segmentation</h1>
            <p style="color: var(--text-secondary);">Real-time demand analysis, price sensitivity, customer personas, and conversion recommendations</p>
        </div>

        <!-- 4 Metrics Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <div style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 1.5rem;">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">🔥 MOST DEMANDED CATEGORY</div>
                <div style="font-family: var(--font-heading); font-size: 1.8rem; font-weight: 800; color: var(--primary); margin: 0.5rem 0;"><?= $analytics['top_category'] ?></div>
                <small style="color: var(--emerald);"><i class="fa-solid fa-arrow-trend-up"></i> High customer query volume</small>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">💰 POPULAR PRICE RANGE</div>
                <div style="font-family: var(--font-heading); font-size: 1.8rem; font-weight: 800; color: var(--emerald); margin: 0.5rem 0;"><?= $analytics['popular_budget'] ?></div>
                <small style="color: var(--text-secondary);"><i class="fa-solid fa-bullseye"></i> Sweet spot for checkout conversion</small>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">📈 RISING DEMAND INTENT</div>
                <div style="font-family: var(--font-heading); font-size: 1.8rem; font-weight: 800; color: var(--amber); margin: 0.5rem 0;"><?= implode(', ', $analytics['top_purposes']) ?></div>
                <small style="color: var(--amber);"><i class="fa-solid fa-bolt"></i> Key customer use-cases</small>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">🎯 CUSTOMER PREFERENCE</div>
                <div style="font-family: var(--font-heading); font-size: 1.8rem; font-weight: 800; color: var(--neon-pink); margin: 0.5rem 0;">Value + Specs</div>
                <small style="color: var(--text-secondary);"><i class="fa-solid fa-shield-check"></i> High RAM & Battery priority</small>
            </div>
        </div>

        <!-- 👥 Customer Segmentation Breakdown -->
        <h2 class="section-title mb-4">AI Customer Segmentation & Behavioral Archetypes</h2>
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.75rem; margin-bottom: 3rem;">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Customer Segment Persona</th>
                        <th>User Count</th>
                        <th>Share %</th>
                        <th>Avg Basket Spend</th>
                        <th>Favorite Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($segmentation as $seg): ?>
                        <tr>
                            <td><strong style="color: #fff; font-size: 1rem;"><?= htmlspecialchars($seg['segment']) ?></strong></td>
                            <td style="font-weight: 700; color: var(--primary);"><?= $seg['count'] ?></td>
                            <td><span style="background: rgba(99, 102, 241, 0.15); color: var(--neon-pink); font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.8rem;"><?= $seg['pct'] ?></span></td>
                            <td style="font-weight: 700; color: var(--emerald);"><?= $seg['avg_spend'] ?></td>
                            <td style="color: var(--text-secondary);"><?= htmlspecialchars($seg['fav_cat']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Strategic AI Insights Cards -->
        <h2 class="section-title mb-4">Strategic AI Business Advice</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 4rem;">
            <?php foreach ($analytics['insights'] as $insight): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <div style="width: 42px; height: 42px; border-radius: 50%; background: rgba(99, 102, 241, 0.2); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fa-solid <?= $insight['icon'] ?>"></i>
                        </div>
                        <h3 style="font-family: var(--font-heading); font-size: 1.2rem;"><?= htmlspecialchars($insight['title']) ?></h3>
                    </div>
                    <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6;">
                        <?= str_replace(['**', '**'], ['<strong>', '</strong>'], htmlspecialchars($insight['text'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Raw Customer AI Requirements Log Table -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.75rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.3rem; margin-bottom: 1.25rem;"><i class="fa-solid fa-list-ul"></i> Live Customer Query Stream Log</h3>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Raw Customer Prompt</th>
                        <th>Extracted Category</th>
                        <th>Budget</th>
                        <th>Parsed Features</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reqLogs as $log): ?>
                        <tr>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M d, H:i', strtotime($log['created_at'])) ?></td>
                            <td style="font-weight: 500; color: #fff; max-width: 320px;"><?= htmlspecialchars($log['raw_prompt']) ?></td>
                            <td><span style="color: var(--primary); font-weight: 600;"><?= htmlspecialchars($log['parsed_category'] ?: 'General') ?></span></td>
                            <td style="font-weight: 700; color: var(--emerald);"><?= $log['parsed_budget'] ? formatPrice($log['parsed_budget']) : 'N/A' ?></td>
                            <td style="font-size: 0.85rem; color: var(--text-secondary);"><?= htmlspecialchars($log['parsed_features'] ?: 'None') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
