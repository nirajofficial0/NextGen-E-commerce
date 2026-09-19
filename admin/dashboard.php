<?php
/**
 * Admin Dashboard & Overview
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ai_engine.php';

requireAdmin();

$db = Database::getConnection();

// Metrics
$totalSales = $db->query("SELECT SUM(final_amount) FROM orders WHERE payment_status = 'Paid'")->fetchColumn() ?: 0;
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCustomers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

// Recent Orders
$recentOrders = $db->query("SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 5")->fetchAll();

// AI Customer Analytics Card Summary
$aiAnalytics = AIEngine::getAICustomerAnalytics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | NextGen E-Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- Admin Header -->
    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon" style="background: linear-gradient(135deg, var(--accent), var(--rose));"><i class="fa-solid fa-gauge-high"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">ADMIN CONSOLE</span>
                </div>
            </a>

            <div style="display: flex; gap: 1.5rem; align-items: center;">
                <a href="../index.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;" target="_blank">
                    <i class="fa-solid fa-store"></i> View Storefront
                </a>
                <a href="../login.php?action=logout" class="logout-link" style="color: var(--rose); font-weight: 600; font-size: 0.9rem;">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>

        <nav class="nav-bar">
            <div class="container nav-container">
                <ul class="nav-links">
                    <li><a href="dashboard.php" style="color: var(--primary); font-weight: 700;"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                    <li><a href="ai-analytics.php" class="highlight-link"><i class="fa-solid fa-brain"></i> AI Insights</a></li>
                    <li><a href="ai-forecasting.php"><i class="fa-solid fa-chart-line-up"></i> Demand Forecast</a></li>
                    <li><a href="inventory.php"><i class="fa-solid fa-boxes-stacked"></i> Smart Inventory</a></li>
                    <li><a href="smart-pricing.php"><i class="fa-solid fa-tags"></i> Smart Pricing</a></li>
                    <li><a href="products.php"><i class="fa-solid fa-box"></i> Products</a></li>
                    <li><a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> Orders</a></li>
                    <li><a href="users.php"><i class="fa-solid fa-users"></i> Customers</a></li>
                    <li><a href="coupons.php"><i class="fa-solid fa-ticket"></i> Coupons</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container my-5">
        <h1 class="section-title mb-4">Merchant Dashboard Overview</h1>

        <!-- KPI Metrics Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <div style="background: var(--bg-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-md); padding: 1.5rem;">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Total Revenue</div>
                <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: var(--emerald); margin: 0.5rem 0;"><?= formatPrice($totalSales) ?></div>
                <small style="color: var(--text-secondary);"><i class="fa-solid fa-circle-check"></i> From completed orders</small>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem;">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Total Orders</div>
                <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: #fff; margin: 0.5rem 0;"><?= $totalOrders ?></div>
                <small style="color: var(--text-secondary);"><i class="fa-solid fa-truck-fast"></i> Active store transactions</small>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem;">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Products Catalog</div>
                <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: var(--primary); margin: 0.5rem 0;"><?= $totalProducts ?></div>
                <small style="color: var(--text-secondary);"><i class="fa-solid fa-boxes-stacked"></i> Listed item variants</small>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem;">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Registered Customers</div>
                <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: var(--neon-pink); margin: 0.5rem 0;"><?= $totalCustomers ?></div>
                <small style="color: var(--text-secondary);"><i class="fa-solid fa-users"></i> Active user profiles</small>
            </div>
        </div>

        <!-- AI Customer Analytics Highlight Card -->
        <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(236, 72, 153, 0.12)); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.5rem; color: #fff;">
                        <i class="fa-solid fa-robot text-glow"></i> AI Customer Requirement Insights
                    </h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Real-time analysis of customer shopping prompts and budget trends</p>
                </div>
                <a href="ai-analytics.php" class="btn-primary" style="padding: 0.6rem 1.25rem; font-size: 0.9rem;">Full AI Analytics Report &rarr;</a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem;">
                    <div style="color: var(--text-muted); font-size: 0.8rem;">MOST DEMANDED CATEGORY</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary); margin-top: 0.25rem;"><?= $aiAnalytics['top_category'] ?></div>
                </div>

                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem;">
                    <div style="color: var(--text-muted); font-size: 0.8rem;">POPULAR PRICE BAND</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--emerald); margin-top: 0.25rem;"><?= $aiAnalytics['popular_budget'] ?></div>
                </div>

                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem;">
                    <div style="color: var(--text-muted); font-size: 0.8rem;">RISING USER INTENT</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--amber); margin-top: 0.25rem;"><?= implode(', ', $aiAnalytics['top_purposes']) ?></div>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-family: var(--font-heading); font-size: 1.3rem;">Recent Orders</h3>
                <a href="orders.php" style="color: var(--primary); font-weight: 600; font-size: 0.9rem;">View All Orders &rarr;</a>
            </div>

            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $ord): ?>
                        <tr>
                            <td><strong>#<?= $ord['id'] ?></strong></td>
                            <td><?= htmlspecialchars($ord['shipping_name'] ?: $ord['full_name']) ?></td>
                            <td style="font-weight: 700; color: var(--emerald);"><?= formatPrice($ord['final_amount']) ?></td>
                            <td><span class="pay-badge"><?= htmlspecialchars($ord['payment_method']) ?></span></td>
                            <td>
                                <span style="background: rgba(99, 102, 241, 0.2); color: var(--primary); font-size: 0.8rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 99px;">
                                    <?= htmlspecialchars($ord['order_status']) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= date('M d, H:i', strtotime($ord['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
