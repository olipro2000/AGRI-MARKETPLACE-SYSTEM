<?php
session_start();
require_once 'config/database.php';

$db = new Database();
$userId = $_GET['id'] ?? 0;

$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
if (!$user) {
    header('Location: feed.php');
    exit;
}

$current_user = null;
if (isset($_SESSION['user_id'])) {
    $current_user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

// Get user's products
$products = $db->fetchAll("SELECT * FROM products WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC", [$userId]);

// Get stats
$totalProducts = count($products);
$totalViews = $products ? array_sum(array_column($products, 'views_count')) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
    <style>
        .profile-banner { height: 250px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); position: relative; }
        .banner-overlay { position: absolute; inset: 0; background: linear-gradient(45deg, rgba(5,150,105,0.8), rgba(16,185,129,0.6)); }
        .profile-container { max-width: 1200px; margin: -100px auto 2rem; padding: 0 1rem; position: relative; z-index: 10; }
        .profile-card { background: white; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); overflow: hidden; }
        .profile-header { padding: 2rem; display: flex; gap: 2rem; align-items: flex-start; }
        .profile-avatar { width: 150px; height: 150px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 4rem; overflow: hidden; border: 6px solid white; box-shadow: 0 8px 24px rgba(0,0,0,0.15); flex-shrink: 0; margin-top: -80px; }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-info { flex: 1; padding-top: 1rem; }
        .profile-name { font-size: 2.5rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem; }
        .profile-role { display: inline-block; background: rgba(5,150,105,0.1); color: var(--primary); padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; font-size: 0.875rem; margin-bottom: 1rem; }
        .profile-location { color: var(--text-light); font-size: 1rem; margin-bottom: 1rem; }
        .profile-stats { display: flex; gap: 2rem; margin-top: 1.5rem; }
        .stat { text-align: center; }
        .stat-number { font-size: 2rem; font-weight: 800; color: var(--primary); display: block; }
        .stat-label { font-size: 0.875rem; color: var(--text-light); }
        .profile-actions { display: flex; gap: 1rem; padding-top: 1rem; }
        .btn-action { padding: 0.875rem 1.75rem; border-radius: 8px; font-weight: 700; text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn-secondary { background: white; color: var(--text); border: 2px solid var(--border); }
        .btn-secondary:hover { border-color: var(--primary); color: var(--primary); }
        .profile-content { padding: 2rem; }
        .section-title { font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--primary); }
        .about-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .about-item { background: var(--bg-alt); padding: 1.25rem; border-radius: 12px; border-left: 4px solid var(--primary); }
        .about-label { font-size: 0.8rem; color: var(--text-light); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
        .about-value { font-size: 1rem; color: var(--text); font-weight: 600; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .product-card { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: all 0.3s; cursor: pointer; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .product-image { height: 200px; background: var(--bg-alt); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: contain; }
        .product-placeholder { font-size: 3rem; color: var(--text-light); }
        .product-info { padding: 1.25rem; }
        .product-name { font-weight: 700; color: var(--text); margin-bottom: 0.5rem; }
        .product-price { color: var(--primary); font-weight: 800; font-size: 1.25rem; }
        .empty-products { text-align: center; padding: 4rem 2rem; }
        .empty-icon { font-size: 4rem; margin-bottom: 1rem; }
        @media (max-width: 768px) {
            .profile-banner { height: 180px; }
            .profile-container { margin-top: -60px; }
            .profile-header { flex-direction: column; align-items: center; text-align: center; padding: 1.5rem; }
            .profile-avatar { width: 120px; height: 120px; font-size: 3rem; margin-top: -60px; }
            .profile-name { font-size: 1.75rem; }
            .profile-stats { justify-content: center; }
            .profile-actions { flex-direction: column; width: 100%; }
            .btn-action { justify-content: center; }
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="profile-banner">
        <div class="banner-overlay"></div>
    </div>
    
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if ($user['profile_picture']): ?>
                        <img src="/curuzamuhinzi/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="<?= htmlspecialchars($user['first_name']) ?>">
                    <?php else: ?>
                        <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h1 class="profile-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
                    <span class="profile-role"><?= ucfirst(str_replace('_', ' ', $user['user_type'])) ?></span>
                    <div class="profile-location">📍 <?= htmlspecialchars($user['district'] . ', ' . $user['province']) ?></div>
                    
                    <div class="profile-stats">
                        <div class="stat">
                            <span class="stat-number"><?= $totalProducts ?></span>
                            <span class="stat-label">Products</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number"><?= number_format($totalViews) ?></span>
                            <span class="stat-label">Total Views</span>
                        </div>
                    </div>
                    
                    <?php if ($current_user && $current_user['id'] != $userId): ?>
                        <div class="profile-actions">
                            <a href="chat.php?user=<?= $userId ?>" class="btn-action btn-primary">💬 Send Message</a>
                            <?php if ($user['phone']): ?>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $user['phone']) ?>" class="btn-action btn-secondary" target="_blank">📱 WhatsApp</a>
                                <a href="tel:<?= htmlspecialchars($user['phone']) ?>" class="btn-action btn-secondary">📞 Call</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="profile-content">
                <h2 class="section-title">About</h2>
                <div class="about-grid">
                    <?php if ($user['phone']): ?>
                        <div class="about-item">
                            <div class="about-label">Phone</div>
                            <div class="about-value"><?= htmlspecialchars($user['phone']) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($user['email']): ?>
                        <div class="about-item">
                            <div class="about-label">Email</div>
                            <div class="about-value"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="about-item">
                        <div class="about-label">Location</div>
                        <div class="about-value"><?= htmlspecialchars($user['sector'] . ', ' . $user['district']) ?></div>
                    </div>
                    <div class="about-item">
                        <div class="about-label">Member Since</div>
                        <div class="about-value"><?= date('M Y', strtotime($user['created_at'])) ?></div>
                    </div>
                </div>
                
                <h2 class="section-title">Products (<?= $totalProducts ?>)</h2>
                <?php if (empty($products)): ?>
                    <div class="empty-products">
                        <div class="empty-icon">📦</div>
                        <p>No products listed yet</p>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card" onclick="location.href='product-detail.php?id=<?= $product['id'] ?>'">
                                <div class="product-image">
                                    <?php if ($product['main_image']): ?>
                                        <img src="/curuzamuhinzi/uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                                    <?php else: ?>
                                        <div class="product-placeholder">🌾</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                                    <div class="product-price"><?= number_format($product['price']) ?> RWF</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/bottom-nav.php'; ?>
</body>
</html>
