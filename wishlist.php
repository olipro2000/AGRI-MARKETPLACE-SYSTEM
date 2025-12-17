<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$db = new Database();
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Get wishlist products
$products = $db->fetchAll("
    SELECT p.*, u.first_name, u.last_name, w.created_at as added_at
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
", [$_SESSION['user_id']]);

$current_user = $user;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Curuzamuhinzi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding-top: 80px; padding-bottom: 80px; }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }
        
        .header { margin-bottom: 30px; }
        .header h1 { color: #27ae60; font-size: 2rem; margin-bottom: 10px; }
        
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .product-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; position: relative; }
        .product-image { height: 200px; background: #eee; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .remove-btn { position: absolute; top: 10px; right: 10px; background: rgba(239,68,68,0.9); color: white; border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; z-index: 10; }
        .remove-btn:hover { background: #dc2626; }
        
        .product-body { padding: 1.5rem; }
        .product-name { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; }
        .product-price { color: #27ae60; font-size: 1.3rem; font-weight: 700; margin-bottom: 1rem; }
        .product-seller { font-size: 0.9rem; color: #666; margin-bottom: 1rem; }
        .product-actions { display: flex; gap: 0.5rem; }
        .btn { flex: 1; padding: 0.75rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; display: block; }
        .btn-primary { background: #27ae60; color: white; }
        .btn-secondary { background: #3498db; color: white; }
        
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-icon { font-size: 4rem; margin-bottom: 1rem; }
        
        @media (max-width: 768px) {
            body { padding-top: 70px; }
            .products-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="header">
            <h1>❤️ My Wishlist</h1>
            <p><?= count($products) ?> saved product<?= count($products) !== 1 ? 's' : '' ?></p>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-icon">❤️</div>
                <h3>Your wishlist is empty</h3>
                <p>Start adding products you love!</p>
                <a href="products.php" class="btn btn-primary" style="margin-top: 20px; display: inline-block; width: auto; padding: 12px 24px;">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <button class="remove-btn" onclick="removeFromWishlist(<?= $product['id'] ?>)" title="Remove from wishlist">×</button>
                        
                        <div class="product-image">
                            <?php if ($product['main_image']): ?>
                                <img src="/curuzamuhinzi/uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            <?php else: ?>
                                <div style="font-size: 3rem;">🌾</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-body">
                            <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                            <div class="product-price"><?= number_format($product['price']) ?> RWF</div>
                            <div class="product-seller">👤 <?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?></div>
                            
                            <div class="product-actions">
                                <a href="product-detail.php?id=<?= $product['id'] ?>" class="btn btn-primary">View</a>
                                <a href="chat.php?user=<?= $product['user_id'] ?>&product=<?= $product['id'] ?>" class="btn btn-secondary">Message</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/bottom-nav.php'; ?>
    
    <script>
    function removeFromWishlist(productId) {
        if (!confirm('Remove from wishlist?')) return;
        
        fetch('/curuzamuhinzi/api/wishlist.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'remove', product_id: productId})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to remove from wishlist');
            }
        });
    }
    </script>
</body>
</html>
