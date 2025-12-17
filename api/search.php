<?php
header('Content-Type: application/json');
if (!isset($_SESSION)) {
    session_start();
}

try {
    require_once __DIR__ . '/../config/database.php';
    
    if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
        echo json_encode([]);
        exit;
    }
    
    $query = trim($_GET['q']);
    $db = new Database();
    $results = [];
    
    // Search products
    try {
        $products = $db->fetchAll("
            SELECT p.*, u.first_name, u.last_name 
            FROM products p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.name LIKE ? 
            LIMIT 5
        ", ["%$query%"]);
        
        foreach ($products as $product) {
            $results[] = [
                'type' => 'product',
                'name' => $product['name'],
                'price' => number_format($product['price']) . ' RWF',
                'seller' => $product['first_name'] . ' ' . $product['last_name'],
                'image' => '🌾',
                'url' => '/curuzamuhinzi/product.php?id=' . $product['id']
            ];
        }
    } catch (Exception $e) {
        // Skip products if table doesn't exist
    }
    
    // Search users
    try {
        $currentUserId = $_SESSION['user_id'] ?? 0;
        $users = $db->fetchAll("
            SELECT id, first_name, last_name, user_type, profile_picture 
            FROM users 
            WHERE first_name LIKE ? AND user_type != 'buyer' AND id != ?
            LIMIT 3
        ", ["%$query%", $currentUserId]);
        
        foreach ($users as $user) {
            $image = '👤';
            if ($user['profile_picture'] && file_exists(__DIR__ . '/../uploads/profiles/' . $user['profile_picture'])) {
                $image = '/curuzamuhinzi/uploads/profiles/' . $user['profile_picture'];
            }
            
            $results[] = [
                'type' => 'user',
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'role' => ucfirst($user['user_type']),
                'image' => $image,
                'url' => '/curuzamuhinzi/profile.php?user=' . $user['id']
            ];
        }
    } catch (Exception $e) {
        // Skip users if error
    }
    
    echo json_encode($results);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>