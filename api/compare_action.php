<?php
/**
 * API Endpoint: Product Comparison Manager
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;

$action = $data['action'] ?? '';
$productId = (int)($data['product_id'] ?? 0);

if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}

if ($action === 'add' && $productId > 0) {
    if (!in_array($productId, $_SESSION['compare_list'])) {
        if (count($_SESSION['compare_list']) >= 4) {
            array_shift($_SESSION['compare_list']);
        }
        $_SESSION['compare_list'][] = $productId;
    }
    echo json_encode([
        'status' => 'success',
        'message' => 'Product added to comparison studio!',
        'compare_count' => count($_SESSION['compare_list'])
    ]);
    exit;
}

if ($action === 'remove' && $productId > 0) {
    $_SESSION['compare_list'] = array_values(array_filter($_SESSION['compare_list'], function($id) use ($productId) {
        return $id !== $productId;
    }));
    echo json_encode([
        'status' => 'success',
        'message' => 'Product removed from comparison.',
        'compare_count' => count($_SESSION['compare_list'])
    ]);
    exit;
}

if ($action === 'clear') {
    $_SESSION['compare_list'] = [];
    echo json_encode([
        'status' => 'success',
        'message' => 'Comparison list cleared.',
        'compare_count' => 0
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
