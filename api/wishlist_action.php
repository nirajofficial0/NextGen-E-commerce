<?php
/**
 * API Endpoint: Wishlist Toggle
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode([
        'status' => 'unauthorized',
        'message' => 'Please log in to add items to your wishlist.'
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;
$productId = (int)($data['product_id'] ?? 0);

if (!$productId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product.']);
    exit;
}

$db = Database::getConnection();
$userId = getCurrentUserId();

// Check if already in wishlist
$stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $db->prepare("DELETE FROM wishlist WHERE id = ?");
    $stmt->execute([$existing['id']]);
    $inWishlist = false;
    $msg = 'Removed from wishlist.';
} else {
    $stmt = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$userId, $productId]);
    $inWishlist = true;
    $msg = 'Added to wishlist!';
}

echo json_encode([
    'status' => 'success',
    'in_wishlist' => $inWishlist,
    'message' => $msg,
    'wishlist_count' => getWishlistCount()
]);
