<?php
session_start();
require_once 'config/database.php';

$db = new Database();

// Get category filter
$category_group = $_GET['category_group'] ?? 'all';

// Build query
$whereClause = "1=1";
$params = [];

if ($category_group && $category_group !== 'all') {
    switch ($category_group) {
        case 'crops':
            $whereClause .= " AND p.category IN ('finished_crops', 'seeds')";
            break;
        case 'livestock':
            $whereClause .= " AND p.category = 'livestock'";
            break;
        case 'equipment':
            $whereClause .= " AND p.category IN ('equipment', 'tools')";
            break;
    }
}

try {
    $products = $db->fetchAll("
        SELECT p.*, u.first_name, u.last_name, u.province, u.district, u.phone, u.profile_picture 
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE $whereClause 
        ORDER BY p.created_at DESC 
        LIMIT 50
    ", $params);
} catch (PDOException $e) {
    $products = [];
}

// Return HTML for products
if (empty($products)): ?>
    <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h3>No products found</h3>
        <p>No products available in this category</p>
    </div>
<?php else: ?>
    <?php foreach ($products as $product): ?>
        <div class="product-card" onclick="viewProduct(<?= $product['id'] ?>)">
            <div class="product-image">
                <?php if ($product['main_image'] && file_exists('uploads/products/' . $product['main_image'])): ?>
                    <img src="uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                <?php else: ?>
                    <div class="product-placeholder">🌾</div>
                <?php endif; ?>
                
                <?php if ($product['organic_certified']): ?>
                    <div class="product-badge">Organic</div>
                <?php endif; ?>
                
                <button class="wishlist-btn" onclick="event.stopPropagation(); toggleWishlist(<?= $product['id'] ?>)">♡</button>
            </div>
            
            <div class="product-content">
                <div class="product-category"><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></div>
                <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                <div class="product-price"><?= number_format($product['price']) ?> RWF/<?= str_replace('_', ' ', $product['unit']) ?></div>
                <div class="product-location">📍 <?= htmlspecialchars($product['district'] . ', ' . $product['province']) ?></div>
                
                <div class="product-actions">
                    <button class="btn-primary" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)">Add to Cart</button>
                    <?php if ($product['phone']): ?>
                        <a href="tel:<?= htmlspecialchars($product['phone']) ?>" class="btn-secondary" onclick="event.stopPropagation()">📞</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>