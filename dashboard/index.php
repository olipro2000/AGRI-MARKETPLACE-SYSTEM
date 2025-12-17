<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$db = new Database();

// Get user information
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
if (!$user) {
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

// Redirect buyers to profile page - they don't need dashboard
if ($user['user_type'] === 'buyer') {
    header('Location: profile.php');
    exit;
}

// Update last login
$db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$_SESSION['user_id']]);

// Get user's products count (with error handling)
try {
    $products_count = $db->fetchColumn("SELECT COUNT(*) FROM products WHERE user_id = ?", [$_SESSION['user_id']]);
    $active_products = $db->fetchColumn("SELECT COUNT(*) FROM products WHERE user_id = ? AND status = 'active'", [$_SESSION['user_id']]);
    $recent_products = $db->fetchAll("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$_SESSION['user_id']]);
} catch (PDOException $e) {
    // Products table doesn't exist yet
    $products_count = 0;
    $active_products = 0;
    $recent_products = [];
}

// Handle product deletion
if (isset($_GET['delete_product']) && is_numeric($_GET['delete_product'])) {
    try {
        $db->query("DELETE FROM products WHERE id = ? AND user_id = ?", [$_GET['delete_product'], $_SESSION['user_id']]);
        header('Location: index.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        // Handle error silently
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --primary-light: #10b981;
            --accent: #f59e0b;
            --bg: #fefefe;
            --bg-alt: #f8fafc;
            --text: #0f172a;
            --text-light: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --error: #ef4444;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-alt);
            color: var(--text);
            line-height: 1.6;
        }
        
        .dashboard {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .welcome-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .welcome-subtitle {
            opacity: 0.9;
            font-size: 1.125rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            background: rgba(5, 150, 105, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text);
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .actions-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .action-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text);
        }
        
        .card-content {
            padding: 1.5rem;
        }
        
        .quick-actions {
            display: grid;
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--bg-alt);
            border: 2px solid var(--border);
            border-radius: 12px;
            text-decoration: none;
            color: var(--text);
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            border-color: var(--primary);
            background: rgba(5, 150, 105, 0.05);
            transform: translateY(-2px);
        }
        
        .action-icon {
            width: 32px;
            height: 32px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
        }
        
        .products-list {
            display: grid;
            gap: 1rem;
        }
        
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--bg-alt);
            border-radius: 12px;
            border: 1px solid var(--border);
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-name {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.25rem;
        }
        
        .product-details {
            font-size: 0.875rem;
            color: var(--text-light);
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-edit {
            background: var(--accent);
            color: white;
        }
        
        .btn-delete {
            background: var(--error);
            color: white;
        }
        
        .btn-sm:hover {
            transform: translateY(-1px);
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-light);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .welcome-title {
                font-size: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .actions-section {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .product-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .product-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <header class="header">
            <div class="logo">
                🌱 Curuza Muhinzi
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <span>👤 <?= htmlspecialchars($user['first_name']) ?></span>
                    <span>(<?= ucfirst(str_replace('_', ' ', $user['user_type'])) ?>)</span>
                </div>
                <a href="profile.php" class="btn-sm btn-edit">Profile</a>
                <a href="../auth/login.php?logout=1" class="btn-sm btn-delete">Logout</a>
            </div>
        </header>
        
        <main class="main-content">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">✅ Product deleted successfully!</div>
            <?php endif; ?>
            
            <div class="welcome-section">
                <h1 class="welcome-title">Welcome back, <?= htmlspecialchars($user['first_name']) ?>! 👋</h1>
                <p class="welcome-subtitle">Manage your products and grow your agricultural business</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">📦</div>
                    </div>
                    <div class="stat-value"><?= $products_count ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">✅</div>
                    </div>
                    <div class="stat-value"><?= $active_products ?></div>
                    <div class="stat-label">Active Listings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">📊</div>
                    </div>
                    <div class="stat-value"><?= $user['profile_completion_percentage'] ?>%</div>
                    <div class="stat-label">Profile Complete</div>
                </div>
            </div>
            
            <div class="actions-section">
                <div class="action-card">
                    <div class="card-header">
                        <h2 class="card-title">Quick Actions</h2>
                    </div>
                    <div class="card-content">
                        <div class="quick-actions">
                            <a href="../add-product.php" class="action-btn">
                                <div class="action-icon">➕</div>
                                <span>Add New Product</span>
                            </a>
                            <a href="profile.php" class="action-btn">
                                <div class="action-icon">👤</div>
                                <span>Update Profile</span>
                            </a>
                            <a href="../products.php" class="action-btn">
                                <div class="action-icon">📋</div>
                                <span>Manage Products</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="action-card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Products</h2>
                        <a href="../products.php" style="color: var(--primary); font-size: 0.875rem; text-decoration: none;">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (empty($recent_products)): ?>
                            <div class="empty-state">
                                <p>No products yet. <a href="../add-product.php" style="color: var(--primary);">Add your first product</a></p>
                            </div>
                        <?php else: ?>
                            <div class="products-list">
                                <?php foreach ($recent_products as $product): ?>
                                    <div class="product-item">
                                        <div class="product-info">
                                            <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                                            <div class="product-details">
                                                <?= number_format($product['price']) ?> RWF/<?= $product['unit'] ?> • 
                                                <?= ucfirst($product['status']) ?>
                                            </div>
                                        </div>
                                        <div class="product-actions">
                                            <a href="../edit-product.php?id=<?= $product['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                            <a href="?delete_product=<?= $product['id'] ?>" class="btn-sm btn-delete" 
                                               onclick="return confirm('Delete this product?')">Delete</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>