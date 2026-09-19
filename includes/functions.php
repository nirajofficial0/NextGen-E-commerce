<?php
/**
 * Utility Functions & E-Commerce Core Helpers
 */

require_once __DIR__ . '/auth.php';

function formatPrice($amount) {
    return '₹' . number_format((float)$amount, 2);
}

function renderRatingStars($rating) {
    $html = '<div class="star-rating" title="' . number_format($rating, 1) . ' out of 5">';
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i class="fa-solid fa-star"></i>';
        } elseif ($i == $full + 1 && $half) {
            $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        } else {
            $html .= '<i class="fa-regular fa-star"></i>';
        }
    }
    $html .= ' <span class="rating-num">(' . number_format($rating, 1) . ')</span></div>';
    return $html;
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function getCartCount() {
    $db = Database::getConnection();
    $sessId = getSessionId();
    $userId = getCurrentUserId();

    if ($userId) {
        $stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ? OR session_id = ?");
        $stmt->execute([$userId, $sessId]);
    } else {
        $stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ?");
        $stmt->execute([$sessId]);
    }
    $res = $stmt->fetch();
    return (int)($res['total'] ?? 0);
}

function getWishlistCount() {
    if (!isLoggedIn()) return 0;
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
    $stmt->execute([getCurrentUserId()]);
    $res = $stmt->fetch();
    return (int)($res['total'] ?? 0);
}

function isInWishlist($productId) {
    if (!isLoggedIn()) return false;
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([getCurrentUserId(), $productId]);
    return (bool)$stmt->fetch();
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'n-a');
}

function setFlash($key, $message) {
    $_SESSION['flash_' . $key] = $message;
}

function getFlash($key) {
    if (isset($_SESSION['flash_' . $key])) {
        $msg = $_SESSION['flash_' . $key];
        unset($_SESSION['flash_' . $key]);
        return $msg;
    }
    return null;
}
