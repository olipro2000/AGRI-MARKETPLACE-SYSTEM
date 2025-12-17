<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $db = new Database();
    
    // Get all provinces
    $provinces = $db->fetchAll("SELECT DISTINCT province FROM users WHERE province IS NOT NULL ORDER BY province");
    
    // Get all districts
    $districts = $db->fetchAll("SELECT DISTINCT district FROM users WHERE district IS NOT NULL ORDER BY district");
    
    // Get user type statistics
    $user_stats = $db->fetchAll("
        SELECT user_type, COUNT(*) as count 
        FROM users 
        WHERE status = 'active' 
        GROUP BY user_type
    ");
    
    echo json_encode([
        'success' => true,
        'provinces' => $provinces,
        'districts' => $districts,
        'user_stats' => $user_stats
    ]);
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch location data'
    ]);
}
?>