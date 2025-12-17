<?php
session_start();
require_once '../config/database.php';

// Set admin session for testing
$_SESSION['admin_id'] = 1;

$db = new Database();

// Test approval for user ID 5
$userId = 5;

echo "Testing approval for user ID: $userId<br>";

// Get pending subscription
$subscription = $db->fetch(
    "SELECT * FROM user_subscriptions WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1", 
    [$userId]
);

if ($subscription) {
    echo "Found pending subscription ID: " . $subscription['id'] . "<br>";
    
    // Update to active
    $result = $db->query(
        "UPDATE user_subscriptions SET status = 'active' WHERE id = ?", 
        [$subscription['id']]
    );
    
    echo "Update result: " . ($result ? "Success" : "Failed") . "<br>";
    
    // Check if updated
    $updated = $db->fetch(
        "SELECT status FROM user_subscriptions WHERE id = ?", 
        [$subscription['id']]
    );
    
    echo "New status: " . $updated['status'] . "<br>";
} else {
    echo "No pending subscription found<br>";
}
?>