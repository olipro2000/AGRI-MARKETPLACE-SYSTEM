<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$db = new Database();
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$productId = $data['product_id'] ?? 0;

try {
    if ($action === 'add') {
        // Check if already in wishlist
        $exists = $db->fetch("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $productId]);
        
        if ($exists) {
            echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
        } else {
            $db->query("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)", [$_SESSION['user_id'], $productId]);
            echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
        }
    } elseif ($action === 'remove') {
        $db->query("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $productId]);
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
    } elseif ($action === 'check') {
        $exists = $db->fetch("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $productId]);
        echo json_encode(['success' => true, 'in_wishlist' => !empty($exists)]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
