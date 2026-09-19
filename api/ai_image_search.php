<?php
/**
 * API Endpoint: AI Vision Image Search Simulator
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/ai_engine.php';

// Accept JSON or multipart file upload
$category = $_POST['category'] ?? 'laptops-computers';
$queryText = $_POST['query'] ?? 'laptop';

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $fileName = strtolower($_FILES['product_image']['name']);
    if (preg_match('/phone|mobile|galaxy|iphone/i', $fileName)) {
        $category = 'smartphones-mobile';
        $queryText = 'smartphone camera phone';
    } elseif (preg_match('/headphone|earbud|audio|music/i', $fileName)) {
        $category = 'audio-headphones';
        $queryText = 'headphones anc';
    } elseif (preg_match('/watch|band/i', $fileName)) {
        $category = 'smart-wearables';
        $queryText = 'smartwatch';
    } else {
        $category = 'laptops-computers';
        $queryText = 'laptop';
    }
}

$results = AIEngine::getRecommendationsForPrompt($queryText, 4);

echo json_encode([
    'status' => 'success',
    'vision_analysis' => [
        'detected_object' => ucfirst(str_replace('-', ' ', $category)),
        'color_palette' => ['Midnight Black', 'Metallic Silver'],
        'confidence_score' => 98.4
    ],
    'matched_products' => array_map(function($p) {
        return [
            'id' => $p['id'],
            'name' => $p['name'],
            'price' => formatPrice($p['price']),
            'image_url' => $p['image_url'],
            'rating' => (float)$p['rating'],
            'ai_score' => rand(91, 99),
            'url' => 'product-details.php?id=' . $p['id']
        ];
    }, $results['recommendations'])
]);
