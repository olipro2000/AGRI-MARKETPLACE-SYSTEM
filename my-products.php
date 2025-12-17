<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$db = new Database();
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = $_POST['product_id'] ?? 0;
    
    // Verify ownership
    $product = $db->fetch("SELECT * FROM products WHERE id = ? AND user_id = ?", [$product_id, $_SESSION['user_id']]);
    
    if ($product) {
        switch ($action) {
            case 'delete':
                $db->query("DELETE FROM products WHERE id = ?", [$product_id]);
                $_SESSION['success'] = "Product deleted successfully";
                break;
            case 'sold_out':
                $db->query("UPDATE products SET status = 'sold_out' WHERE id = ?", [$product_id]);
                $_SESSION['success'] = "Product marked as sold out";
                break;
            case 'activate':
                $db->query("UPDATE products SET status = 'active' WHERE id = ?", [$product_id]);
                $_SESSION['success'] = "Product activated successfully";
                break;
        }
    }
    
    header('Location: my-products.php');
    exit;
}

$products = $db->fetchAll("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC", [$_SESSION['user_id']]);
$stats = [
    'total' => count($products),
    'active' => count(array_filter($products, fn($p) => $p['status'] === 'active')),
    'draft' => count(array_filter($products, fn($p) => $p['status'] === 'draft')),
    'sold_out' => count(array_filter($products, fn($p) => $p['status'] === 'sold_out')),
];

$current_user = $user;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - Curuzamuhinzi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding-top: 80px; padding-bottom: 80px; }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }
        
        .header { margin-bottom: 30px; }
        .header h1 { color: #27ae60; font-size: 2rem; margin-bottom: 10px; }
        
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(16,185,129,0.1); color: #059669; border: 1px solid rgba(16,185,129,0.3); }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-value { font-size: 2rem; font-weight: 700; color: #27ae60; }
        .stat-label { color: #666; font-size: 0.9rem; margin-top: 0.5rem; }
        
        .actions-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #27ae60; color: white; }
        .btn-primary:hover { background: #219a52; }
        
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
        .product-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .product-image { height: 200px; background: #eee; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-status { position: absolute; top: 10px; right: 10px; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .status-active { background: #10b981; color: white; }
        .status-draft { background: #f59e0b; color: white; }
        .status-sold_out { background: #ef4444; color: white; }
        
        .product-body { padding: 1.5rem; }
        .product-name { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; }
        .product-price { color: #27ae60; font-size: 1.3rem; font-weight: 700; margin-bottom: 1rem; }
        .product-meta { display: flex; gap: 1rem; margin-bottom: 1rem; font-size: 0.85rem; color: #666; }
        
        .product-actions { display: flex; gap: 0.5rem; }
        .btn-action { flex: 1; padding: 0.6rem; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; }
        .btn-edit { background: #3b82f6; color: white; }
        .btn-sold { background: #f59e0b; color: white; }
        .btn-activate { background: #10b981; color: white; }
        .btn-delete { background: #ef4444; color: white; }
        
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-icon { font-size: 4rem; margin-bottom: 1rem; }
        
        @media (max-width: 768px) {
            body { padding-top: 70px; }
            .products-grid { grid-template-columns: 1fr; }
            .actions-bar { flex-direction: column; gap: 1rem; align-items: stretch; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="header">
            <h1>📦 My Products</h1>
            <p>Manage your product listings</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['active'] ?></div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['draft'] ?></div>
                <div class="stat-label">Draft</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['sold_out'] ?></div>
                <div class="stat-label">Sold Out</div>
            </div>
        </div>
        
        <div class="actions-bar">
            <h2>Your Products (<?= $stats['total'] ?>)</h2>
            <a href="add-product.php" class="btn btn-primary">+ Add New Product</a>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h3>No products yet</h3>
                <p>Start by adding your first product</p>
                <a href="add-product.php" class="btn btn-primary" style="margin-top: 20px;">Add Product</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['main_image']): ?>
                                <img src="/curuzamuhinzi/uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            <?php else: ?>
                                <div style="font-size: 3rem;">🌾</div>
                            <?php endif; ?>
                            <div class="product-status status-<?= $product['status'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $product['status'])) ?>
                            </div>
                        </div>
                        
                        <div class="product-body">
                            <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                            <div class="product-price"><?= number_format($product['price']) ?> RWF/<?= $product['unit'] ?></div>
                            
                            <div class="product-meta">
                                <span>👁️ <?= $product['views_count'] ?> views</span>
                                <span>📅 <?= date('M j, Y', strtotime($product['created_at'])) ?></span>
                            </div>
                            
                            <div class="product-actions">
                                <a href="edit-product.php?id=<?= $product['id'] ?>" class="btn-action btn-edit">Edit</a>
                                
                                <?php if ($product['status'] === 'active'): ?>
                                    <form method="POST" style="flex: 1;">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="action" value="sold_out">
                                        <button type="submit" class="btn-action btn-sold" style="width: 100%;">Sold Out</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="flex: 1;">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn-action btn-activate" style="width: 100%;">Activate</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" style="flex: 1;" onsubmit="return confirm('Delete this product?')">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-action btn-delete" style="width: 100%;">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/bottom-nav.php'; ?>
</body>
</html>
