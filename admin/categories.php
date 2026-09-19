<?php
/**
 * Admin - Categories Management
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = sanitizeInput($_POST['name']);
    $slug = slugify($name);
    $desc = sanitizeInput($_POST['description']);
    $icon = sanitizeInput($_POST['icon_class']);

    $stmt = $db->prepare("INSERT INTO categories (name, slug, description, icon_class) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $slug, $desc, $icon]);

    setFlash('success', 'Category "' . $name . '" created!');
    header("Location: categories.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $catId = (int)$_GET['id'];
    $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$catId]);
    setFlash('success', 'Category deleted.');
    header("Location: categories.php");
    exit;
}

$categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories | NextGen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon"><i class="fa-solid fa-layer-group"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">CATEGORIES</span>
                </div>
            </a>
            <a href="dashboard.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">&larr; Dashboard</a>
        </div>
    </header>

    <div class="container my-5">
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 3rem;">
            <!-- Categories Table -->
            <div>
                <h1 class="section-title mb-4">Store Categories</h1>

                <?php if ($msg = getFlash('success')): ?>
                    <div class="flash-alert success mb-4"><?= $msg ?></div>
                <?php endif; ?>

                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem;">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><i class="fa-solid <?= htmlspecialchars($cat['icon_class']) ?> text-primary" style="font-size: 1.2rem;"></i></td>
                                    <td><strong style="color: #fff;"><?= htmlspecialchars($cat['name']) ?></strong></td>
                                    <td><code style="color: var(--text-muted);"><?= htmlspecialchars($cat['slug']) ?></code></td>
                                    <td>
                                        <a href="categories.php?action=delete&id=<?= $cat['id'] ?>" onclick="return confirm('Delete category?')" style="color: var(--rose);"><i class="fa-solid fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create Category Form -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.75rem; height: fit-content;">
                <h3 style="font-family: var(--font-heading); margin-bottom: 1.25rem;"><i class="fa-solid fa-plus"></i> Add Category</h3>

                <form action="categories.php" method="POST">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Smart Wearables" required>
                    </div>

                    <div class="form-group">
                        <label>Icon FontAwesome Class</label>
                        <input type="text" name="icon_class" class="form-control" value="fa-box" placeholder="fa-laptop" required>
                    </div>

                    <div class="form-group mb-4">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" name="add_category" class="btn-primary" style="width: 100%; justify-content: center;">Add Category</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
