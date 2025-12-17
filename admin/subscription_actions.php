<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: payments.php');
    exit;
}

$db = new Database();

try {
    $action = $_POST['action'] ?? '';
    $subscriptionId = (int)($_POST['subscription_id'] ?? 0);
    
    if (!$action || !$subscriptionId) {
        $_SESSION['error'] = 'Missing required fields';
        header('Location: payments.php');
        exit;
    }
    
    // Get subscription and user details
    $subscription = $db->fetch(
        "SELECT us.*, u.id as user_id, sp.name as plan_name 
         FROM user_subscriptions us 
         JOIN users u ON us.user_id = u.id
         LEFT JOIN subscription_plans sp ON us.plan_id = sp.id 
         WHERE us.id = ?", 
        [$subscriptionId]
    );
    
    if (!$subscription) {
        $_SESSION['error'] = 'Subscription not found';
        header('Location: payments.php');
        exit;
    }
    
    $userId = $subscription['user_id'];

    switch ($action) {
        case 'approve':
            // Update subscription to active with dates
            $db->query(
                "UPDATE user_subscriptions 
                 SET status = 'active', 
                     starts_at = CURDATE(), 
                     expires_at = DATE_ADD(CURDATE(), INTERVAL (SELECT duration_days FROM subscription_plans WHERE id = plan_id) DAY)
                 WHERE id = ?", 
                [$subscriptionId]
            );
            
            // Update payment proof status
            $db->query(
                "UPDATE payment_proofs SET status = 'verified', verified_at = NOW() WHERE subscription_id = ?",
                [$subscriptionId]
            );
            
            // Create notification
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')",
                [
                    $userId,
                    'Subscription Approved!',
                    "Congratulations! Your {$subscription['plan_name']} subscription has been approved. You can now add products to the marketplace."
                ]
            );
            
            $_SESSION['success'] = 'Subscription approved successfully';
            break;
            
        case 'reject':
            // Update subscription to cancelled
            $db->query(
                "UPDATE user_subscriptions SET status = 'cancelled' WHERE id = ?", 
                [$subscriptionId]
            );
            
            // Update payment proof status
            $db->query(
                "UPDATE payment_proofs SET status = 'rejected', verified_at = NOW() WHERE subscription_id = ?",
                [$subscriptionId]
            );
            
            // Create notification
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'error')",
                [
                    $userId,
                    'Subscription Rejected',
                    "Your {$subscription['plan_name']} subscription payment was not approved. Please check your payment details or contact Curuza Muhinzi support for assistance."
                ]
            );
            
            $_SESSION['success'] = 'Subscription rejected';
            break;
            
        default:
            $_SESSION['error'] = 'Invalid action';
            break;
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

header('Location: payments.php');
exit;
?>