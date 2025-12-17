<?php
session_start();
require_once 'config/database.php';

$db = new Database();
$product_id = $_GET['id'] ?? 0;

// Get product with seller info
$product = $db->fetch("
    SELECT p.*, u.first_name, u.last_name, u.phone, u.profile_picture, u.province, u.district, u.sector
    FROM products p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
", [$product_id]);

if (!$product) {
    header('Location: feed.php');
    exit;
}

// Update views
$db->query("UPDATE products SET views_count = views_count + 1 WHERE id = ?", [$product_id]);

// Get more products from same seller
$more_products = $db->fetchAll("
    SELECT * FROM products 
    WHERE user_id = ? AND id != ? AND status = 'active' 
    LIMIT 4
", [$product['user_id'], $product_id]);

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
    <title><?= htmlspecialchars($product['product_name']) ?> - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; }
        .back-btn { display: inline-flex; align-items: center; gap: 0.5rem; color: var(--primary); text-decoration: none; font-weight: 600; margin-bottom: 1.5rem; }
        .back-btn:hover { text-decoration: underline; }
        
        .product-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        
        .image-section { position: sticky; top: 80px; }
        .main-image { width: 100%; height: 400px; background: #f1f5f9; border-radius: 16px; overflow: hidden; margin-bottom: 1rem; }
        .main-image img { width: 100%; height: 100%; object-fit: cover; }
        .main-image-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 4rem; color: #cbd5e1; }
        
        .thumbnail-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; }
        .thumbnail { height: 80px; background: #f1f5f9; border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; }
        .thumbnail:hover, .thumbnail.active { border-color: var(--primary); }
        .thumbnail img { width: 100%; height: 100%; object-fit: cover; }
        
        .info-section { }
        .product-header { margin-bottom: 1.5rem; }
        .product-title { font-size: 2rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem; }
        .product-category { display: inline-block; background: rgba(5,150,105,0.1); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem; font-weight: 600; }
        
        .price-box { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .price { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.25rem; }
        .price-unit { font-size: 1rem; opacity: 0.9; }
        .quantity { font-size: 0.875rem; opacity: 0.9; margin-top: 0.5rem; }
        
        .contact-box { background: white; border: 2px solid var(--primary); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .contact-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; }
        .seller-info { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb; }
        .seller-avatar { width: 60px; height: 60px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; overflow: hidden; }
        .seller-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .seller-name { font-size: 1.125rem; font-weight: 700; color: var(--text); }
        .seller-location { font-size: 0.875rem; color: var(--text-light); }
        
        .contact-buttons { display: flex; flex-direction: column; gap: 0.75rem; }
        .btn-contact { padding: 1rem; border-radius: 12px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s; }
        .btn-whatsapp { background: #25D366; color: white; }
        .btn-whatsapp:hover { background: #20BA5A; }
        .btn-call { background: var(--primary); color: white; }
        .btn-call:hover { background: var(--primary-dark); }
        .btn-profile { background: white; color: var(--primary); border: 2px solid var(--primary); }
        .btn-profile:hover { background: var(--primary); color: white; }
        
        .details-box { background: white; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid #e5e7eb; }
        .details-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; }
        .detail-item { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; }
        .detail-item:last-child { border-bottom: none; }
        .detail-label { color: var(--text-light); font-weight: 500; }
        .detail-value { color: var(--text); font-weight: 600; }
        
        .description-box { background: white; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid #e5e7eb; }
        .description { color: var(--text); line-height: 1.8; }
        
        .more-products { margin-top: 3rem; }
        .section-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem; }
        .products-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
        .product-card { background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; transition: all 0.2s; text-decoration: none; color: inherit; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        .product-image { height: 150px; background: #f1f5f9; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-info { padding: 1rem; }
        .product-name { font-weight: 700; margin-bottom: 0.5rem; font-size: 0.9rem; }
        .product-price { color: var(--primary); font-weight: 800; font-size: 1.125rem; }
        
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .product-grid { grid-template-columns: 1fr; gap: 1.5rem; }
            .image-section { position: static; }
            .main-image { height: 300px; }
            .product-title { font-size: 1.5rem; }
            .price { font-size: 2rem; }
            .products-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <a href="feed.php" class="back-btn">← Back to Products</a>
        
        <div class="product-grid">
            <!-- Image Section -->
            <div class="image-section">
                <div class="main-image" id="mainImage">
                    <?php if ($product['main_image'] && file_exists('uploads/products/' . $product['main_image'])): ?>
                        <img src="uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                    <?php else: ?>
                        <div class="main-image-placeholder">🌾</div>
                    <?php endif; ?>
                </div>
                
                <div class="thumbnail-grid">
                    <?php 
                    $images = array_filter([$product['main_image'], $product['image_2'], $product['image_3'], $product['image_4']]);
                    foreach ($images as $i => $img): 
                        if ($img && file_exists('uploads/products/' . $img)):
                    ?>
                        <div class="thumbnail <?= $i === 0 ? 'active' : '' ?>" onclick="changeImage('uploads/products/<?= htmlspecialchars($img) ?>', this)">
                            <img src="uploads/products/<?= htmlspecialchars($img) ?>" alt="Image <?= $i + 1 ?>">
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
            
            <!-- Info Section -->
            <div class="info-section">
                <div class="product-header">
                    <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
                    <span class="product-category"><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></span>
                </div>
                
                <div class="price-box">
                    <div class="price"><?= number_format($product['price']) ?> RWF</div>
                    <div class="price-unit">per <?= $product['unit'] ?></div>
                    <div class="quantity">📦 <?= $product['quantity_available'] ?> available</div>
                </div>
                
                <div class="contact-box">
                    <h3 class="contact-title">Contact Seller</h3>
                    <div class="seller-info">
                        <div class="seller-avatar">
                            <?php if ($product['profile_picture'] && file_exists('uploads/profiles/' . $product['profile_picture'])): ?>
                                <img src="uploads/profiles/<?= htmlspecialchars($product['profile_picture']) ?>" alt="<?= htmlspecialchars($product['first_name']) ?>">
                            <?php else: ?>
                                <?= strtoupper(substr($product['first_name'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="seller-name"><?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?></div>
                            <div class="seller-location">📍 <?= htmlspecialchars($product['district'] . ', ' . $product['province']) ?></div>
                        </div>
                    </div>
                    
                    <div class="contact-buttons">
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['user_id']): ?>
                            <a href="chat.php?user=<?= $product['user_id'] ?>&product=<?= $product_id ?>" class="btn-contact btn-call">
                                💬 Send Message
                            </a>
                        <?php endif; ?>
                        <?php if ($product['phone']): ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $product['phone']) ?>?text=Hi, I'm interested in your <?= urlencode($product['product_name']) ?>" class="btn-contact btn-whatsapp" target="_blank">
                                📱 WhatsApp Seller
                            </a>
                            <a href="tel:<?= htmlspecialchars($product['phone']) ?>" class="btn-contact btn-call">
                                📞 Call <?= htmlspecialchars($product['phone']) ?>
                            </a>
                        <?php endif; ?>
                        <a href="user-profile.php?id=<?= $product['user_id'] ?>" class="btn-contact btn-profile">
                            👤 View Seller Profile
                        </a>
                    </div>
                </div>
                
                <div class="details-box">
                    <h3 class="details-title">Product Details</h3>
                    <div class="detail-item">
                        <span class="detail-label">Type</span>
                        <span class="detail-value"><?= ucfirst(str_replace('_', ' ', $product['product_type'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Quality</span>
                        <span class="detail-value"><?= ucfirst($product['quality_grade']) ?></span>
                    </div>
                    <?php if ($product['organic_certified']): ?>
                    <div class="detail-item">
                        <span class="detail-label">Certification</span>
                        <span class="detail-value">🌿 Organic Certified</span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-item">
                        <span class="detail-label">Season</span>
                        <span class="detail-value"><?= ucfirst(str_replace('_', ' ', $product['harvest_season'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Location</span>
                        <span class="detail-value"><?= htmlspecialchars($product['district'] . ', ' . $product['province']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Delivery</span>
                        <span class="detail-value">
                            <?= $product['delivery_available'] ? '✅ Available' : '❌ Not Available' ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Pickup</span>
                        <span class="detail-value">
                            <?= $product['pickup_available'] ? '✅ Available' : '❌ Not Available' ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Views</span>
                        <span class="detail-value">👁️ <?= number_format($product['views_count']) ?></span>
                    </div>
                </div>
                
                <?php if ($product['description']): ?>
                <div class="description-box">
                    <h3 class="details-title">Description</h3>
                    <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($more_products)): ?>
        <div class="more-products">
            <h2 class="section-title">More from this Seller</h2>
            <div class="products-grid">
                <?php foreach ($more_products as $p): ?>
                    <a href="product-detail.php?id=<?= $p['id'] ?>" class="product-card">
                        <div class="product-image">
                            <?php if ($p['main_image'] && file_exists('uploads/products/' . $p['main_image'])): ?>
                                <img src="uploads/products/<?= htmlspecialchars($p['main_image']) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-name"><?= htmlspecialchars($p['product_name']) ?></div>
                            <div class="product-price"><?= number_format($p['price']) ?> RWF</div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/bottom-nav.php'; ?>
    
    <script>
        function changeImage(src, thumbnail) {
            document.getElementById('mainImage').innerHTML = `<img src="${src}" alt="Product Image">`;
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        }
    </script>
</body>
</html>
