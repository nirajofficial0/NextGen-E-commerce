<?php
/**
 * Admin Product Management
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getConnection();

// Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $prodId = (int)$_GET['id'];
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$prodId]);
    setFlash('success', 'Product #' . $prodId . ' deleted successfully.');
    header("Location: products.php");
    exit;
}

// Fetch Products
$products = $db->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products Management | NextGen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon"><i class="fa-solid fa-box"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">PRODUCTS MANAGER</span>
                </div>
            </a>
            <a href="dashboard.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">&larr; Dashboard</a>
        </div>
    </header>

    <div class="container my-5">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Products Inventory</h1>
            <a href="add-product.php" class="btn-primary"><i class="fa-solid fa-plus"></i> Add New Product</a>
        </div>

        <?php if ($msg = getFlash('success')): ?>
            <div class="flash-alert success mb-4"><?= $msg ?></div>
        <?php endif; ?>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $prod): ?>
                        <tr>
                            <td style="display: flex; align-items: center; gap: 1rem;">
                                <img src="<?= htmlspecialchars($prod['image_url']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                                <div>
                                    <strong style="color: #fff; display: block;"><?= htmlspecialchars($prod['name']) ?></strong>
                                    <small style="color: var(--text-muted);"><?= htmlspecialchars($prod['slug']) ?></small>
                                </div>
                            </td>
                            <td><span style="color: var(--primary); font-weight: 600;"><?= htmlspecialchars($prod['category_name']) ?></span></td>
                            <td style="font-weight: 700; color: var(--emerald);"><?= formatPrice($prod['price']) ?></td>
                            <td>
                                <span style="background: var(--bg-surface); padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.85rem; font-weight: 700;">
                                    <?= $prod['stock_quantity'] ?> units
                                </span>
                            </td>
                            <td><?= renderRatingStars($prod['rating']) ?></td>
                            <td>
                                <a href="edit-product.php?id=<?= $prod['id'] ?>" style="color: var(--primary); margin-right: 1rem;" title="Edit Product"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a href="products.php?action=delete&id=<?= $prod['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')" style="color: var(--rose);" title="Delete Product"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
