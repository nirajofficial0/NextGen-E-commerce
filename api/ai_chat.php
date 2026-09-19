<?php
/**
 * API Endpoint: AI Shopping Assistant Query
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/ai_engine.php';

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$prompt = trim($data['prompt'] ?? $_POST['prompt'] ?? $_GET['prompt'] ?? '');

if (empty($prompt)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Prompt requirement cannot be empty.'
    ]);
    exit;
}

$results = AIEngine::getRecommendationsForPrompt($prompt, 6);

echo json_encode([
    'status' => 'success',
    'prompt' => $prompt,
    'requirement' => $results['requirement'],
    'count' => count($results['recommendations']),
    'recommendations' => array_map(function($p) {
        return [
            'id' => $p['id'],
            'name' => $p['name'],
            'price' => formatPrice($p['price']),
            'original_price' => $p['original_price'] ? formatPrice($p['original_price']) : null,
            'image_url' => $p['image_url'],
            'rating' => (float)$p['rating'],
            'ai_score' => $p['ai_score'],
            'ai_reasons' => $p['ai_reasons'],
            'category_name' => $p['category_name'],
            'url' => 'product-details.php?id=' . $p['id']
        ];
    }, $results['recommendations'])
]);
