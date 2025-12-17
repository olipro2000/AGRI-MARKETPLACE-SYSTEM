<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once 'config/database.php';

if (!isset($_GET['user']) || empty($_GET['user'])) {
    header('Location: feed.php');
    exit;
}

$user_id = $_GET['user'];
$db = new Database();

// Get user details
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$user_id]);
if (!$user) {
    header('Location: feed.php');
    exit;
}

// Check verification status
$isVerified = false;
try {
    $verification = $db->fetch(
        "SELECT us.status FROM user_subscriptions us 
         WHERE us.user_id = ? AND us.status = 'active' AND us.expires_at > NOW() 
         ORDER BY us.created_at DESC LIMIT 1", 
        [$user_id]
    );
    $isVerified = !empty($verification);
} catch (Exception $e) {
    $isVerified = false;
}

// Get user's products if they're a seller
$products = [];
if ($user['user_type'] !== 'buyer') {
    $products = $db->fetchAll("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC LIMIT 12", [$user_id]);
}

// Get user stats
$totalProducts = count($products);
$joinYear = date('Y', strtotime($user['created_at'] ?? 'now'));
$memberSince = date('F Y', strtotime($user['created_at'] ?? 'now'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #fff;
            margin: 0;
            padding-top: 0;
            color: #000;
        }
        
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            min-height: 100vh;
            border-left: 1px solid #eff3f4;
            border-right: 1px solid #eff3f4;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 50%, #15803d 100%);
            height: 200px;
            width: 100%;
            position: relative;
        }
        
        .profile-content {
            position: relative;
            padding: 0 16px;
            padding-top: 12px;
        }
        
        .user-avatar {
            width: 134px;
            height: 134px;
            border-radius: 50%;
            border: 4px solid white;
            background: #f0f0f0;
            position: absolute;
            top: -67px;
            left: 16px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .verification-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #22c55e;
            border: 3px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .verification-badge svg {
            width: 20px;
            height: 20px;
            fill: white;
        }
        
        .profile-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 12px;
            margin-bottom: 16px;
        }
        
        .action-btn {
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            border: 1px solid #cfd9de;
            background: transparent;
            color: #0f1419;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background: rgba(15,20,25,0.1);
        }
        
        .action-btn.primary {
            background: #1d9bf0;
            border-color: #1d9bf0;
            color: #fff;
        }
        
        .action-btn.primary:hover {
            background: #1a8cd8;
        }
        
        .profile-details {
            margin-top: 4px;
            padding-bottom: 16px;
        }
        
        .profile-name {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .verified-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #22c55e;
            margin-left: 4px;
        }
        
        .verified-icon svg {
            width: 12px;
            height: 12px;
            fill: white;
        }
        
        .profile-username {
            color: #536471;
            font-size: 15px;
            margin: 0;
        }
        
        .profile-bio {
            margin: 12px 0;
            font-size: 15px;
            line-height: 1.3;
        }
        
        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            color: #536471;
            font-size: 15px;
            margin: 12px 0;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .profile-stats {
            display: flex;
            gap: 20px;
            margin: 12px 0;
        }
        
        .stat-item {
            color: #536471;
            font-size: 15px;
        }
        
        .stat-number {
            color: #0f1419;
            font-weight: 700;
        }
        
        .profile-nav {
            border-bottom: 1px solid #eff3f4;
            display: flex;
        }
        
        .nav-tab {
            flex: 1;
            text-align: center;
            padding: 16px;
            color: #536471;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
            position: relative;
        }
        
        .nav-tab.active {
            color: #0f1419;
            border-bottom-color: #1d9bf0;
        }
        
        .nav-tab:hover {
            background: rgba(15,20,25,0.03);
        }
        
        .tab-content {
            min-height: 400px;
            position: relative;
            overflow: hidden;
        }
        
        .tab-panel {
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        
        .tab-panel.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .product-item {
            border-bottom: 1px solid #eff3f4;
            padding: 12px 16px;
            transition: background 0.2s;
            cursor: pointer;
        }
        
        .product-item:hover {
            background: rgba(15,20,25,0.03);
        }
        
        .product-header {
            display: flex;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .product-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f7f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            overflow: hidden;
        }
        
        .product-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-meta {
            flex: 1;
        }
        
        .product-author {
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 700;
            font-size: 15px;
        }
        
        .product-time {
            color: #536471;
            font-size: 15px;
        }
        
        .product-content {
            margin-left: 52px;
        }
        
        .product-name {
            font-size: 15px;
            margin: 0 0 8px 0;
            font-weight: 400;
        }
        
        .product-price {
            color: #22c55e;
            font-weight: 700;
            font-size: 16px;
            margin: 4px 0;
        }
        
        .product-category {
            color: #1d9bf0;
            font-size: 14px;
        }
        
        .product-image {
            margin-top: 12px;
            border-radius: 16px;
            overflow: hidden;
            max-width: 100%;
        }
        
        .product-image img {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: cover;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #536471;
        }
        
        .empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-text {
            font-size: 31px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #0f1419;
        }
        
        .empty-subtext {
            font-size: 15px;
            line-height: 1.3;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .about-section {
            padding: 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 400px;
        }
        
        .about-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: none;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .about-title {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 20px 0;
            color: #1e293b;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .about-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            margin: 8px 0;
            background: rgba(255,255,255,0.7);
            border-radius: 16px;
            border: 1px solid rgba(34,197,94,0.1);
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .about-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34,197,94,0.15);
            border-color: rgba(34,197,94,0.3);
        }
        
        .about-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(34,197,94,0.3);
        }
        
        .about-text {
            flex: 1;
            color: #1e293b;
            font-size: 15px;
            min-width: 0;
            word-wrap: break-word;
        }
        
        .about-label {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 4px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .about-text > div:last-child {
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.4;
        }
        
        @media (max-width: 480px) {
            .profile-container {
                max-width: 100%;
            }
            
            .profile-header {
                height: 150px;
            }
            
            .user-avatar {
                width: 100px;
                height: 100px;
                top: -50px;
                font-size: 40px;
            }
            
            .profile-actions {
                margin-top: 60px;
            }
            
            .about-section {
                padding: 0;
            }
            
            .about-card {
                border-radius: 0;
                border-left: none;
                border-right: none;
                padding: 16px 12px;
            }
            
            .about-title {
                font-size: 18px;
                margin-bottom: 12px;
            }
            
        }
        
        @media (max-width: 480px) {
            .about-card {
                width: 100% !important;
                display: block !important;
            }
            
            .about-title {
                width: 100% !important;
                display: block !important;
                text-align: left !important;
                margin-bottom: 16px !important;
            }
            
            .about-item {
                width: 100% !important;
                display: block !important;
                flex: none !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                padding: 12px 0 !important;
                border-bottom: 1px solid #f0f0f0 !important;
                margin: 0 !important;
            }
            
            .about-icon {
                display: inline-block !important;
                width: 24px !important;
                height: 24px !important;
                margin-right: 8px !important;
                margin-bottom: 4px !important;
                flex-shrink: initial !important;
                background: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                font-size: 16px !important;
            }
            
            .about-text {
                width: 100% !important;
                display: block !important;
                flex: none !important;
                font-size: 14px !important;
                line-height: 1.4 !important;
                margin: 0 !important;
            }
            
            .about-label {
                width: 100% !important;
                font-weight: 600 !important;
                color: #333 !important;
                margin-bottom: 4px !important;
                display: block !important;
            }
            
            .about-text > div:last-child {
                width: 100% !important;
                color: #666 !important;
                font-size: 14px !important;
                display: block !important;
            }
            
            .about-label {
                font-size: 12px;
                margin-bottom: 2px;
                display: block;
            }
        }
        
        .image-viewer {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        
        .image-viewer-content {
            position: relative;
            max-width: 500px;
            max-height: 500px;
        }
        
        .image-viewer img {
            width: 100%;
            height: auto;
            max-width: 500px;
            max-height: 500px;
            object-fit: contain;
            border-radius: 12px;
        }
        
        .close-viewer {
            position: absolute;
            top: -40px;
            right: 0;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 2rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>
    
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header"></div>
        
        <div class="profile-content">
            <div class="user-avatar" onclick="viewProfileImage()">
                <?php if ($user['profile_picture'] && file_exists('uploads/profiles/' . $user['profile_picture'])): ?>
                    <img src="uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile" id="profileImage">
                <?php else: ?>
                    <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
                
                <!-- Profile Actions -->
                <div class="profile-actions">
                    <?php if (isset($user['phone']) && $user['phone']): ?>
                        <a href="tel:<?= htmlspecialchars($user['phone']) ?>" class="action-btn">Call</a>
                    <?php endif; ?>
                    <?php if (isset($user['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="action-btn primary">Message</a>
                    <?php endif; ?>
                </div>
                
                <!-- Profile Details -->
                <div class="profile-details">
                    <h1 class="profile-name">
                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                        <?php if ($isVerified): ?>
                            <span class="verified-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </h1>
                    <p class="profile-username">@<?= strtolower(str_replace(' ', '', $user['first_name'] . $user['last_name'])) ?></p>
                    
                    <div class="profile-bio">
                        <?= ucfirst(str_replace('_', ' ', $user['user_type'])) ?> on Curuza Muhinzi 🌾
                        <?php if ($isVerified): ?>
                            <br>✅ Verified Agricultural Supplier
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-meta">
                        <div class="meta-item">
                            📍 <?php 
                                $location_parts = [];
                                if (isset($user['district']) && $user['district']) $location_parts[] = $user['district'];
                                if (isset($user['province']) && $user['province']) $location_parts[] = $user['province'];
                                if (empty($location_parts) && isset($user['location'])) $location_parts[] = $user['location'];
                                if (empty($location_parts)) $location_parts[] = 'Rwanda';
                                echo htmlspecialchars(implode(', ', $location_parts));
                            ?>
                        </div>
                        <div class="meta-item">
                            📅 Joined <?= $memberSince ?>
                        </div>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?= $totalProducts ?></span> Products
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $joinYear ?></span> Member Since
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Navigation -->
        <div class="profile-nav">
            <a href="#" class="nav-tab active" onclick="showTab('products', event)">Products</a>
            <a href="#" class="nav-tab" onclick="showTab('about', event)">About</a>
        </div>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Products Tab -->
            <div id="products-tab" class="tab-panel active">
                <?php if ($user['user_type'] !== 'buyer' && !empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-item" onclick="viewProduct(<?= $product['id'] ?>)">
                            <div class="product-header">
                                <div class="product-avatar">
                                    <?php if ($user['profile_picture'] && file_exists('uploads/profiles/' . $user['profile_picture'])): ?>
                                        <img src="uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile">
                                    <?php else: ?>
                                        <span><?= strtoupper(substr($user['first_name'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-meta">
                                    <div class="product-author">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                        <?php if ($isVerified): ?>
                                            <span class="verified-icon">
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                </svg>
                                            </span>
                                        <?php endif; ?>
                                        <span class="product-time">· <?= date('M j', strtotime($product['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="product-content">
                                <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                                <div class="product-price"><?= number_format($product['price']) ?> RWF</div>
                                <div class="product-category">#<?= str_replace('_', '', $product['category']) ?></div>
                                
                                <?php if ($product['main_image'] && file_exists('uploads/products/' . $product['main_image'])): ?>
                                    <div class="product-image">
                                        <img src="uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🌾</div>
                        <div class="empty-text">
                            <?php if ($user['user_type'] === 'buyer'): ?>
                                This is a buyer account
                            <?php else: ?>
                                No products yet
                            <?php endif; ?>
                        </div>
                        <div class="empty-subtext">
                            <?php if ($user['user_type'] === 'buyer'): ?>
                                <?= htmlspecialchars($user['first_name']) ?> uses Curuza Muhinzi to discover and buy agricultural products.
                            <?php else: ?>
                                When <?= htmlspecialchars($user['first_name']) ?> posts products, they'll show up here.
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- About Tab -->
            <div id="about-tab" class="tab-panel">
                <div class="about-section">
                    <div class="about-card">
                        <h3 class="about-title">Contact Information</h3>
                        <?php if (isset($user['phone']) && $user['phone']): ?>
                            <div class="about-item">
                                <div class="about-icon">📞</div>
                                <div class="about-text">
                                    <div class="about-label">Phone</div>
                                    <div><?= htmlspecialchars($user['phone']) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($user['email'])): ?>
                            <div class="about-item">
                                <div class="about-icon">✉️</div>
                                <div class="about-text">
                                    <div class="about-label">Email</div>
                                    <div><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="about-item">
                            <div class="about-icon">📍</div>
                            <div class="about-text">
                                <div class="about-label">Location</div>
                                <div><?php 
                                    $location_parts = [];
                                    if (isset($user['district']) && $user['district']) $location_parts[] = $user['district'];
                                    if (isset($user['province']) && $user['province']) $location_parts[] = $user['province'];
                                    if (empty($location_parts) && isset($user['location'])) $location_parts[] = $user['location'];
                                    if (empty($location_parts)) $location_parts[] = 'Rwanda';
                                    echo htmlspecialchars(implode(', ', $location_parts));
                                ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="about-card">
                        <h3 class="about-title">Account Details</h3>
                        <div class="about-item">
                            <div class="about-icon">👤</div>
                            <div class="about-text">
                                <div class="about-label">User Type</div>
                                <div><?= ucfirst(str_replace('_', ' ', $user['user_type'])) ?></div>
                            </div>
                        </div>
                        <div class="about-item">
                            <div class="about-icon">📅</div>
                            <div class="about-text">
                                <div class="about-label">Member Since</div>
                                <div><?= $memberSince ?></div>
                            </div>
                        </div>
                        <div class="about-item">
                            <div class="about-icon"><?= $isVerified ? '✅' : '❌' ?></div>
                            <div class="about-text">
                                <div class="about-label">Verification Status</div>
                                <div><?= $isVerified ? 'Verified Agricultural Supplier' : 'Unverified Account' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <!-- Profile Image Viewer -->
    <div class="image-viewer" id="imageViewer" onclick="closeImageViewer()">
        <div class="image-viewer-content">
            <img id="viewerImage" src="" alt="Profile">
            <button class="close-viewer" onclick="closeImageViewer()">×</button>
        </div>
    </div>
    
    <script>
        function viewProfileImage() {
            const profileImg = document.getElementById('profileImage');
            if (profileImg && profileImg.src) {
                const viewerImg = document.getElementById('viewerImage');
                viewerImg.src = profileImg.src;
                document.getElementById('imageViewer').style.display = 'flex';
            }
        }
        
        function closeImageViewer() {
            document.getElementById('imageViewer').style.display = 'none';
        }
        
        function viewProduct(productId) {
            window.location.href = 'product.php?id=' + productId;
        }
        
        function showTab(tabName, event) {
            event.preventDefault();
            
            // Remove active class from all panels
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.remove('active');
            });
            
            // Remove active class from all nav tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab immediately
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked nav tab
            event.target.classList.add('active');
        }
    </script>
    
    <?php 
    // Set current_user for bottom nav
    if (!isset($current_user) && isset($_SESSION['user_id'])) {
        $current_user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }
    include 'includes/bottom-nav.php'; 
    ?>
</body>
</html>