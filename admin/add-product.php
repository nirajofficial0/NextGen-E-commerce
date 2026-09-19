<?php
/**
 * Admin - Add New Product Form
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = Database::getConnection();
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $catId = (int)$_POST['category_id'];
    $name = sanitizeInput($_POST['name']);
    $slug = slugify($name);
    $shortDesc = sanitizeInput($_POST['short_description']);
    $desc = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $stock = (int)$_POST['stock_quantity'];
    $imageUrl = sanitizeInput($_POST['image_url']);
    $tags = sanitizeInput($_POST['tags']);
    $specsJson = sanitizeInput($_POST['specs_json']);

    $stmt = $db->prepare("INSERT INTO products (category_id, name, slug, short_description, description, price, original_price, stock_quantity, image_url, tags, specs_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$catId, $name, $slug, $shortDesc, $desc, $price, $origPrice, $stock, $imageUrl, $tags, $specsJson]);

    setFlash('success', 'New product "' . $name . '" created successfully!');
    header("Location: products.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product | NextGen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="main-header" style="background: #0d1322;">
        <div class="container header-container">
            <a href="dashboard.php" class="brand-logo">
                <div class="logo-icon"><i class="fa-solid fa-plus"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">ADD PRODUCT</span>
                </div>
            </a>
            <a href="products.php" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">&larr; Back to Products</a>
        </div>
    </header>

    <div class="container my-5">
        <div class="form-card" style="max-width: 700px; margin: 0 auto;">
            <h2 style="font-family: var(--font-heading); margin-bottom: 1.5rem;"><i class="fa-solid fa-plus"></i> Add New Product Variant</h2>

            <form action="add-product.php" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. ZenBook Ultra 14" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Short Description</label>
                    <input type="text" name="short_description" class="form-control" placeholder="Brief tagline overview..." required>
                </div>

                <div class="form-group">
                    <label>Full Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Detailed product specifications and features..." required></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Price (₹)</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="59999" required>
                    </div>
                    <div class="form-group">
                        <label>Original Price (MRP)</label>
                        <input type="number" step="0.01" name="original_price" class="form-control" placeholder="69999">
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" value="15" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Image URL</label>
                    <input type="url" name="image_url" class="form-control" placeholder="https://images.unsplash.com/..." required>
                </div>

                <div class="form-group">
                    <label>AI Keyword Tags (Comma separated)</label>
                    <input type="text" name="tags" class="form-control" placeholder="laptop,coding,16gb,battery,thin,student">
                    <small style="color: var(--text-muted);">Used by AI Requirement Parser to calculate match scores</small>
                </div>

                <div class="form-group mb-4">
                    <label>Specs JSON</label>
                    <textarea name="specs_json" class="form-control" rows="3" placeholder='{"ram":"16GB","battery":"12 Hours","storage":"512GB"}'></textarea>
                </div>

                <button type="submit" name="save_product" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Product
                </button>
            </form>
        </div>
    </div>

</body>
</html>
