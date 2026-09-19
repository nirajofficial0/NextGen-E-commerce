<?php
/**
 * API Endpoint: Cart Actions & Live Updates
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;

$action = $data['action'] ?? '';
$db = Database::getConnection();
$sessId = getSessionId();
$userId = getCurrentUserId();

if ($action === 'add') {
    $productId = (int)($data['product_id'] ?? 0);
    $qty = max(1, (int)($data['quantity'] ?? 1));

    if (!$productId) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product.']);
        exit;
    }

    // Check existing item
    if ($userId) {
        $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE (user_id = ? OR session_id = ?) AND product_id = ?");
        $stmt->execute([$userId, $sessId, $productId]);
    } else {
        $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessId, $productId]);
    }
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = $existing['quantity'] + $qty;
        $stmt = $db->prepare("UPDATE cart SET quantity = ?, user_id = ? WHERE id = ?");
        $stmt->execute([$newQty, $userId, $existing['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $sessId, $productId, $qty]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Item added to cart!',
        'cart_count' => getCartCount()
    ]);
    exit;
}

if ($action === 'update') {
    $cartId = (int)($data['cart_id'] ?? 0);
    $qty = (int)($data['quantity'] ?? 1);

    if ($qty <= 0) {
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$cartId]);
    } else {
        $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$qty, $cartId]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Cart updated.',
        'cart_count' => getCartCount()
    ]);
    exit;
}

if ($action === 'remove') {
    $cartId = (int)($data['cart_id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->execute([$cartId]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Item removed from cart.',
        'cart_count' => getCartCount()
    ]);
    exit;
}

if ($action === 'apply_coupon') {
    $code = strtoupper(trim($data['coupon_code'] ?? ''));
    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if ($coupon) {
        $_SESSION['applied_coupon'] = $coupon;
        echo json_encode([
            'status' => 'success',
            'message' => 'Coupon ' . $coupon['code'] . ' applied successfully!',
            'discount_percentage' => $coupon['discount_percentage']
        ]);
    } else {
        unset($_SESSION['applied_coupon']);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid or expired coupon code.'
        ]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
