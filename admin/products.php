<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$admin = $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);

// Handle product actions
if ($_POST['action'] ?? false) {
    $product_id = $_POST['product_id'] ?? 0;
    $action = $_POST['action'];
    
    try {
        switch ($action) {
            case 'approve':
                $db->query("UPDATE products SET status = 'active' WHERE id = ?", [$product_id]);
                $_SESSION['success'] = "Product approved successfully";
                break;
            case 'feature':
                $db->query("UPDATE products SET featured = 1 WHERE id = ?", [$product_id]);
                $_SESSION['success'] = "Product featured successfully";
                break;
            case 'unfeature':
                $db->query("UPDATE products SET featured = 0 WHERE id = ?", [$product_id]);
                $_SESSION['success'] = "Product unfeatured successfully";
                break;
            case 'delete':
                $db->query("DELETE FROM products WHERE id = ?", [$product_id]);
                $_SESSION['success'] = "Product deleted successfully";
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Action failed: " . $e->getMessage();
    }
    
    header('Location: products.php');
    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$featured = $_GET['featured'] ?? '';

// Build query
$where = "1=1";
$params = [];

if ($search) {
    $where .= " AND p.product_name LIKE ?";
    $params[] = "%$search%";
}

if ($category) {
    $where .= " AND p.category = ?";
    $params[] = $category;
}

if ($status) {
    $where .= " AND p.status = ?";
    $params[] = $status;
}

if ($featured !== '') {
    $where .= " AND p.featured = ?";
    $params[] = $featured;
}

try {
    $products = $db->fetchAll("
        SELECT p.*, u.first_name, u.last_name, u.province, u.district 
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE $where 
        ORDER BY p.created_at DESC 
        LIMIT 100
    ", $params);
    
    $categories = $db->fetchAll("SELECT DISTINCT category FROM products ORDER BY category");
    
    $stats = [
        'total' => $db->fetchColumn("SELECT COUNT(*) FROM products"),
        'active' => $db->fetchColumn("SELECT COUNT(*) FROM products WHERE status = 'active'"),
        'draft' => $db->fetchColumn("SELECT COUNT(*) FROM products WHERE status = 'draft'"),
        'featured' => $db->fetchColumn("SELECT COUNT(*) FROM products WHERE featured = 1"),
    ];
} catch (Exception $e) {
    $products = [];
    $categories = [];
    $stats = ['total' => 0, 'active' => 0, 'draft' => 0, 'featured' => 0];
}

$page_title = 'Products';
$current_page = 'products';
include 'includes/header.php';
?>

<style>
    .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; }
    .alert-success { background: rgba(16,185,129,0.1); color: #059669; border: 1px solid rgba(16,185,129,0.3); }
    .alert-error { background: rgba(239,68,68,0.1); color: #dc2626; border: 1px solid rgba(239,68,68,0.3); }
    .product-card {
        background: var(--bg-secondary);
        border-radius: 12px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        transition: all 0.2s ease;
    }
    
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .product-image {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: var(--text-secondary);
        position: relative;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .badge-featured {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: white;
    }
    
    .badge-draft {
        background: rgba(107, 114, 128, 0.1);
        color: var(--text-secondary);
    }
    
    .badge-active {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }
    
    .product-content {
        padding: 1.5rem;
    }
    
    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .product-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-green);
        margin-bottom: 0.75rem;
    }
    
    .product-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-secondary);
    }
    
    .product-seller {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: rgba(22, 163, 74, 0.05);
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .seller-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .seller-info h5 {
        margin: 0 0 0.25rem 0;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }
    
    .seller-info p {
        margin: 0;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    
    .product-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .btn-action {
        flex: 1;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        min-width: 80px;
    }
    
    .btn-approve {
        background: var(--success-color);
        color: white;
    }
    
    .btn-feature {
        background: #f59e0b;
        color: white;
    }
    
    .btn-delete {
        background: var(--error-color);
        color: white;
    }
    
    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .view-toggle {
        display: flex;
        gap: 0.5rem;
        background: var(--bg-primary);
        padding: 0.25rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }
    
    .view-btn {
        padding: 0.5rem 1rem;
        border: none;
        background: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.875rem;
        color: var(--text-secondary);
        transition: all 0.2s ease;
    }
    
    .view-btn.active {
        background: var(--primary-green);
        color: white;
    }
    
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .product-actions {
            flex-direction: column;
        }
        
        .btn-action {
            flex: none;
        }
    }
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Product Management</h1>
            <p class="page-subtitle">Review, moderate, and manage marketplace products</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ❌ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📦</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['total']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Total Products</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['active']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Active Products</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📝</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['draft']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Pending Review</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">⭐</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['featured']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Featured</div>
            </div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 2rem;">
            <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: end;">
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Search Products</label>
                    <input type="text" name="search" placeholder="Product name..." value="<?= htmlspecialchars($search) ?>" style="width: 100%; padding: 0.625rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Category</label>
                    <select name="category" style="width: 100%; padding: 0.625rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $cat['category'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Status</label>
                    <select name="status" style="width: 100%; padding: 0.625rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem;">
                        <option value="">All Status</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="sold_out" <?= $status === 'sold_out' ? 'selected' : '' ?>>Sold Out</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Featured</label>
                    <select name="featured" style="width: 100%; padding: 0.625rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem;">
                        <option value="">All Products</option>
                        <option value="1" <?= $featured === '1' ? 'selected' : '' ?>>Featured Only</option>
                        <option value="0" <?= $featured === '0' ? 'selected' : '' ?>>Not Featured</option>
                    </select>
                </div>
                <button type="submit" style="padding: 0.625rem 1.5rem; background: #16a34a; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.875rem;">Filter</button>
            </form>
        </div>
        
        <!-- Products Grid -->
        <div class="products-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['main_image'] && file_exists('../uploads/products/' . $product['main_image'])): ?>
                                <img src="../uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            <?php else: ?>
                                🌾
                            <?php endif; ?>
                            
                            <?php if ($product['featured']): ?>
                                <div class="product-badge badge-featured">Featured</div>
                            <?php else: ?>
                                <div class="product-badge badge-<?= $product['status'] ?>"><?= ucfirst($product['status']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-content">
                            <h3 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h3>
                            <div class="product-price"><?= number_format($product['price']) ?> RWF/<?= $product['unit'] ?></div>
                            
                            <div class="product-meta">
                                <div class="meta-item">
                                    <span>📂</span>
                                    <span><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></span>
                                </div>
                                <div class="meta-item">
                                    <span>📍</span>
                                    <span><?= htmlspecialchars($product['province'] . ', ' . $product['district']) ?></span>
                                </div>
                                <div class="meta-item">
                                    <span>👁️</span>
                                    <span><?= $product['views_count'] ?> views</span>
                                </div>
                                <div class="meta-item">
                                    <span>📅</span>
                                    <span><?= date('M j, Y', strtotime($product['created_at'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="product-seller">
                                <div class="seller-avatar">
                                    <?= strtoupper(substr($product['first_name'], 0, 1)) ?>
                                </div>
                                <div class="seller-info">
                                    <h5><?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?></h5>
                                    <p><?= htmlspecialchars($product['province']) ?></p>
                                </div>
                            </div>
                            
                            <div class="product-actions">
                                <?php if ($product['status'] === 'draft'): ?>
                                    <form method="POST" style="flex: 1;">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn-action btn-approve">Approve</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (!$product['featured']): ?>
                                    <form method="POST" style="flex: 1;">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="action" value="feature">
                                        <button type="submit" class="btn-action btn-feature">Feature</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="flex: 1;">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="action" value="unfeature">
                                        <button type="submit" class="btn-action btn-feature">Unfeature</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" style="flex: 1;" onsubmit="return confirm('Delete this product?')">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-action btn-delete">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-secondary);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters or check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>