<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $db = new Database();
    $province = $_GET['province'] ?? '';
    
    if (empty($province)) {
        echo json_encode(['success' => false, 'message' => 'Province is required']);
        exit;
    }
    
    $districts = $db->fetchAll(
        "SELECT DISTINCT district FROM users WHERE province = ? AND district IS NOT NULL ORDER BY district",
        [$province]
    );
    
    echo json_encode([
        'success' => true,
        'districts' => $districts
    ]);
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch districts'
    ]);
}
?>