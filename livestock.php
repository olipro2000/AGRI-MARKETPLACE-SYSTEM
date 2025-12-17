<?php
session_start();
require_once 'config/database.php';

$db = new Database();
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$whereClause = "p.category = 'livestock' AND p.status = 'active'";
$params = [];

if ($search) {
    $whereClause .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$orderClause = match($sort) {
    'price_low' => 'ORDER BY p.price ASC',
    'price_high' => 'ORDER BY p.price DESC',
    'name' => 'ORDER BY p.product_name ASC',
    default => 'ORDER BY p.created_at DESC'
};

$products = $db->fetchAll("
    SELECT p.*, u.first_name, u.last_name, u.province, u.district, u.phone, u.profile_picture 
    FROM products p 
    JOIN users u ON p.user_id = u.id 
    WHERE $whereClause 
    $orderClause
", $params);

$current_user = null;
if (isset($_SESSION['user_id'])) {
    $current_user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livestock - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; }
        .page-header { text-align: center; margin-bottom: 2rem; }
        .page-title { font-size: 2.5rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem; }
        .page-subtitle { color: var(--text-light); font-size: 1.125rem; }
        
        .filters { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .search-box { flex: 1; min-width: 250px; }
        .search-box input { width: 100%; padding: 0.75rem 1rem; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.875rem; }
        .sort-select { padding: 0.75rem 1rem; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.875rem; }
        
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .product-card { background: white; border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb; transition: all 0.2s; cursor: pointer; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        .product-image { height: 200px; background: #f1f5f9; position: relative; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-badge { position: absolute; top: 12px; right: 12px; background: var(--primary); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .product-info { padding: 1.25rem; }
        .product-name { font-weight: 700; font-size: 1.125rem; margin-bottom: 0.5rem; color: var(--text); }
        .product-price { color: var(--primary); font-weight: 800; font-size: 1.5rem; margin-bottom: 0.75rem; }
        .product-location { display: flex; align-items: center; gap: 0.5rem; color: var(--text-light); font-size: 0.875rem; margin-bottom: 0.75rem; }
        .product-seller { display: flex; align-items: center; gap: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9; }
        .seller-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 700; overflow: hidden; }
        .seller-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .seller-name { font-size: 0.875rem; font-weight: 600; color: var(--text); }
        
        .empty-state { text-align: center; padding: 4rem 2rem; }
        .empty-icon { font-size: 4rem; margin-bottom: 1rem; }
        .empty-title { font-size: 1.5rem; font-weight: 700; color: var(--text); margin-bottom: 0.5rem; }
        .empty-text { color: var(--text-light); }
        
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .page-title { font-size: 2rem; }
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">🐄 Livestock</h1>
            <p class="page-subtitle">Quality livestock and dairy products from local farms</p>
        </div>
        
        <div class="filters">
            <div class="search-box">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search livestock..." value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>
            <select class="sort-select" onchange="window.location.href='?sort='+this.value+'<?= $search ? '&search='.urlencode($search) : '' ?>'">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option>
            </select>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-icon">🐄</div>
                <h2 class="empty-title">No livestock found</h2>
                <p class="empty-text">Try adjusting your search or check back later for new listings</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card" 
                     style="width:100%;border-radius:16px;overflow:hidden;background:#fff;
                            box-shadow:0 2px 10px rgba(0,0,0,0.07);font-family:Arial, sans-serif;cursor:pointer;"
                     onclick="window.location.href='product-detail.php?id=<?= $product['id'] ?>'">

                    <div class="product-image" 
                         style="width:100%;height:230px;">
                        <?php if($product['main_image']): ?>
                            <img src="/curuzamuhinzi/uploads/products/<?= htmlspecialchars($product['main_image']) ?>" 
                                 alt="<?= htmlspecialchars($product['product_name']) ?>"
                                 style="width:100%;height:100%;object-fit:cover;display:block;">
                        <?php endif; ?>
                    </div>

                    <div class="product-info" style="padding:16px;">

                        <h3 class="product-name" 
                            style="font-size:20px;margin-bottom:6px;">
                            <?= htmlspecialchars($product['product_name']) ?>
                        </h3>

                        <div class="product-price" 
                             style="font-size:18px;font-weight:bold;color:#00a651;margin-bottom:12px;">
                            <?= number_format($product['price']) ?> RWF
                        </div>

                        <div class="product-seller" 
                             style="display:flex;align-items:center;margin-bottom:16px;gap:10px;">

                            <div class="seller-avatar" 
                                 style="width:42px;height:42px;border-radius:50%;overflow:hidden;">
                                <?php if($product['profile_picture']): ?>
                                    <img src="/curuzamuhinzi/uploads/profiles/<?= htmlspecialchars($product['profile_picture']) ?>" 
                                         alt=""
                                         style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:100%;height:100%;background:#00a651;color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;">
                                        <?= strtoupper(substr($product['first_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="seller-info" style="display:flex;flex-direction:column;">
                                <div class="seller-name"><?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?></div>
                                <div class="seller-location"><?= htmlspecialchars($product['district'] . ', ' . $product['province']) ?></div>
                            </div>
                        </div>

                        <div class="product-actions" 
                             style="display:flex;gap:10px;margin-top:10px;">

                            <button class="action-btn btn-primary" 
                                    style="flex:1;padding:10px 0;border-radius:10px;border:none;
                                           cursor:pointer;font-weight:600;background:#00a651;color:white;">
                                💬 Contact
                            </button>

                            <button class="action-btn btn-secondary wishlist-btn" 
                                    style="flex:1;padding:10px 0;border-radius:10px;border:none;
                                           cursor:pointer;font-weight:600;background:#f5f5f5;color:#d40055;">
                                ❤️ Save
                            </button>

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
