<?php
/**
 * API Endpoint: AI Product Q&A Assistant
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/ai_engine.php';

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;

$productId = (int)($data['product_id'] ?? 0);
$question = trim($data['question'] ?? '');

if (!$productId || empty($question)) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID and question are required.']);
    exit;
}

$answer = AIEngine::answerProductQuestion($productId, $question);

echo json_encode([
    'status' => 'success',
    'product_id' => $productId,
    'question' => $question,
    'answer' => $answer
]);
