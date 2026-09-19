<?php
/**
 * API Endpoint: Live Instant Search Autocomplete
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['status' => 'success', 'results' => []]);
    exit;
}

$db = Database::getConnection();
$searchTerm = '%' . $query . '%';
$stmt = $db->prepare("SELECT p.id, p.name, p.price, p.image_url, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.name LIKE ? OR p.tags LIKE ? OR c.name LIKE ? LIMIT 6");
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$products = $stmt->fetchAll();

$formatted = array_map(function($p) {
    return [
        'id' => $p['id'],
        'name' => $p['name'],
        'price' => formatPrice($p['price']),
        'image_url' => $p['image_url'],
        'category_name' => $p['category_name'],
        'url' => 'product-details.php?id=' . $p['id']
    ];
}, $products);

echo json_encode([
    'status' => 'success',
    'query' => $query,
    'results' => $formatted
]);
