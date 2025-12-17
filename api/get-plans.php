<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $db = new Database();
    $plans = $db->fetchAll("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY price ASC");
    echo json_encode($plans);
} catch (Exception $e) {
    echo json_encode([]);
}
?>