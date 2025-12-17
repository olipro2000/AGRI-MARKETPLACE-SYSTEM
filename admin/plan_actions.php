<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $db = new Database();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $plan = $db->fetch("SELECT * FROM subscription_plans WHERE id = ?", [$_GET['id']]);
        echo json_encode($plan);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    switch ($input['action']) {
        case 'add':
            $db->query("INSERT INTO subscription_plans (name, price, duration_days, max_products, features, is_active) VALUES (?, ?, ?, ?, ?, ?)", [
                $input['name'],
                $input['price'],
                $input['duration_days'],
                $input['max_products'] ?: null,
                $input['features'] ?: null,
                $input['is_active']
            ]);
            break;
            
        case 'edit':
            $db->query("UPDATE subscription_plans SET name = ?, price = ?, duration_days = ?, max_products = ?, features = ?, is_active = ? WHERE id = ?", [
                $input['name'],
                $input['price'],
                $input['duration_days'],
                $input['max_products'] ?: null,
                $input['features'] ?: null,
                $input['is_active'],
                $input['id']
            ]);
            break;
            
        case 'toggle':
            $db->query("UPDATE subscription_plans SET is_active = NOT is_active WHERE id = ?", [$input['id']]);
            break;
            
        case 'delete':
            $db->query("DELETE FROM subscription_plans WHERE id = ?", [$input['id']]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }

    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>