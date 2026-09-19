<?php
/**
 * Authentication & Session Helper Module
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id, full_name, email, phone, address, city, state, pincode, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = "Please log in to access this page.";
        header("Location: login.php");
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['flash_error'] = "Access denied. Administrator rights required.";
        header("Location: ../admin/login.php");
        exit;
    }
}

function getSessionId() {
    if (isLoggedIn()) {
        return 'user_' . $_SESSION['user_id'];
    }
    if (!isset($_SESSION['anonymous_id'])) {
        $_SESSION['anonymous_id'] = 'anon_' . bin2hex(random_bytes(8));
    }
    return $_SESSION['anonymous_id'];
}
