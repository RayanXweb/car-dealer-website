<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$productId = intval($data['product_id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$productId) {
    echo json_encode(['success' => false, 'error' => 'Invalid product']);
    exit();
}

if (isInWishlist($userId, $productId)) {
    removeFromWishlist($userId, $productId);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    addToWishlist($userId, $productId);
    echo json_encode(['success' => true, 'action' => 'added']);
}
?>
