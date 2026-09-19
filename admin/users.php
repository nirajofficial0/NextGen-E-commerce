<?php
/**
 * Admin - User Management
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getConnection();
$users = $db->query("SELECT u.*, COUNT(o.id) as order_count FROM users u LEFT JOIN orders o ON u.id = o.user_id GROUP BY u.id ORDER BY u.id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Profiles | NextGen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon"><i class="fa-solid fa-users"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">CUSTOMERS</span>
                </div>
            </a>
            <a href="dashboard.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">&larr; Dashboard</a>
        </div>
    </header>

    <div class="container my-5">
        <h1 class="section-title mb-4">Customer User Profiles</h1>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Orders Placed</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td><strong style="color: #fff;"><?= htmlspecialchars($u['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['phone'] ?: 'N/A') ?></td>
                            <td>
                                <span style="background: <?= $u['role'] === 'admin' ? 'rgba(236, 72, 153, 0.2)' : 'rgba(99, 102, 241, 0.2)' ?>; color: <?= $u['role'] === 'admin' ? 'var(--neon-pink)' : 'var(--primary)' ?>; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 99px; text-transform: uppercase;">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                            </td>
                            <td style="font-weight: 700; color: var(--emerald);"><?= $u['order_count'] ?> orders</td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
