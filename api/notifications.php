<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$db = new Database();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $countOnly = isset($_GET['count_only']);
    
    if ($countOnly) {
        // Return only unread count
        $unreadCount = $db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE",
            [$userId]
        ) ?: 0;
        
        echo json_encode(['unread_count' => $unreadCount]);
    } else {
        // Return all notifications
        $notifications = $db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
            [$userId]
        );
        
        $unreadCount = $db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE",
            [$userId]
        ) ?: 0;
        
        echo json_encode([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input['action'] === 'mark_read' && isset($input['id'])) {
        $db->query(
            "UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?",
            [$input['id'], $userId]
        );
        echo json_encode(['success' => true]);
    } elseif ($input['action'] === 'mark_all_read') {
        $db->query(
            "UPDATE notifications SET is_read = TRUE WHERE user_id = ?",
            [$userId]
        );
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Invalid action']);
    }
}
?>