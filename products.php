<?php
session_start();
require_once 'includes/header.php';

// Database connection
$host = 'localhost';
$dbname = 'curuzamuhinzi';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$province = $_GET['province'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query
$sql = "SELECT p.*, u.first_name, u.last_name FROM products p JOIN users u ON p.user_id = u.id WHERE p.status = 'active'";
$params = [];

if ($search) {
    $sql .= " AND (p.product_name LIKE :search OR p.description LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($category) {
    $sql .= " AND p.category = :category";
    $params[':category'] = $category;
}
if ($province) {
    $sql .= " AND p.province = :province";
    $params[':province'] = $province;
}
if ($min_price) {
    $sql .= " AND p.price >= :min_price";
    $params[':min_price'] = $min_price;
}
if ($max_price) {
    $sql .= " AND p.price <= :max_price";
    $params[':max_price'] = $max_price;
}

// Sorting
switch($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.featured DESC, p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.featured DESC, p.price DESC";
        break;
    case 'popular':
        $sql .= " ORDER BY p.featured DESC, p.views_count DESC";
        break;
    default:
        $sql .= " ORDER BY p.featured DESC, p.created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories and provinces for filters
$categories = $pdo->query("SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$provinces = $pdo->query("SELECT DISTINCT province FROM products WHERE status = 'active' AND province IS NOT NULL ORDER BY province")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Curuzamuhinzi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding-top: 80px; padding-bottom: 80px; }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #27ae60; font-size: 2rem; margin-bottom: 5px; }
        
        .filters-bar { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .filters-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 0.85rem; color: #666; margin-bottom: 5px; font-weight: 600; }
        .filter-group input, .filter-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 0.95rem; }
        .filter-group.price { flex: 0.8; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 0.95rem; font-weight: 600; }
        .btn-primary { background: #27ae60; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        
        .results-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .results-count { color: #666; font-size: 0.95rem; }
        
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .product-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; color: inherit; display: block; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .product-header { background: #27ae60; color: white; padding: 15px; position: relative; }
        .featured { background: #f39c12; padding: 5px 10px; border-radius: 15px; font-size: 0.75rem; position: absolute; top: 10px; right: 10px; }
        .product-name { font-size: 1.1rem; font-weight: bold; margin-bottom: 5px; }
        .category { background: rgba(255,255,255,0.2); padding: 3px 8px; border-radius: 10px; font-size: 0.8rem; display: inline-block; }
        .images { height: 200px; background: #eee; display: flex; align-items: center; justify-content: center; color: #999; }
        .images img { width: 100%; height: 100%; object-fit: cover; }
        .product-body { padding: 15px; }
        .price-section { background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 12px; border-left: 4px solid #27ae60; }
        .price { font-size: 1.4rem; font-weight: bold; color: #27ae60; }
        .unit { color: #666; font-size: 0.85rem; margin-top: 3px; }
        .badges { margin-bottom: 10px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; margin-right: 5px; }
        .badge.organic { background: #d5f4e6; color: #27ae60; }
        .badge.premium { background: #fef9e7; color: #f39c12; }
        .badge.standard { background: #eaf2f8; color: #3498db; }
        .badge.basic { background: #fadbd8; color: #e74c3c; }
        .description { color: #555; font-size: 0.9rem; line-height: 1.4; margin-bottom: 12px; }
        .location { color: #666; font-size: 0.85rem; margin-bottom: 10px; }
        .location i { margin-right: 5px; }
        .meta { display: flex; justify-content: space-between; padding-top: 10px; border-top: 1px solid #eee; font-size: 0.8rem; color: #666; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .empty-state h3 { font-size: 1.5rem; margin-bottom: 10px; }
        
        @media (max-width: 768px) {
            body { padding-top: 70px; }
            .filters-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .products-grid { grid-template-columns: 1fr; }
            .results-bar { flex-direction: column; gap: 10px; align-items: start; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌾 Browse Products</h1>
            <p>Fresh agricultural products from Rwandan farmers</p>
        </div>

        <form method="GET" class="filters-bar">
            <div class="filters-row">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="filter-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $cat)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Location</label>
                    <select name="province">
                        <option value="">All Provinces</option>
                        <?php foreach($provinces as $prov): ?>
                            <option value="<?= $prov ?>" <?= $province === $prov ? 'selected' : '' ?>><?= $prov ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group price">
                    <label>Min Price</label>
                    <input type="number" name="min_price" placeholder="0" value="<?= htmlspecialchars($min_price) ?>">
                </div>
                <div class="filter-group price">
                    <label>Max Price</label>
                    <input type="number" name="max_price" placeholder="Any" value="<?= htmlspecialchars($max_price) ?>">
                </div>
                <div class="filter-group">
                    <label>Sort By</label>
                    <select name="sort">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="products.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <div class="results-bar">
            <div class="results-count"><?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?> found</div>
        </div>

        <div class="products-grid">
            <?php foreach($products as $product): ?>
            <a href="product-detail.php?id=<?= $product['id'] ?>" class="product-card">
                <div class="product-header">
                    <?php if($product['featured']): ?>
                        <div class="featured">⭐ Featured</div>
                    <?php endif; ?>
                    <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                    <div class="category"><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></div>
                </div>

                <div class="images">
                    <?php if($product['main_image']): ?>
                        <img src="/curuzamuhinzi/uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                    <?php else: ?>
                        <div>📦 No Image</div>
                    <?php endif; ?>
                </div>

                <div class="product-body">
                    <div class="price-section">
                        <div class="price"><?= number_format($product['price']) ?> RWF</div>
                        <div class="unit">per <?= $product['unit'] ?> • <?= $product['quantity_available'] ?> available</div>
                    </div>

                    <div class="badges">
                        <?php if($product['organic_certified']): ?>
                            <span class="badge organic">🌱 Organic</span>
                        <?php endif; ?>
                        <span class="badge <?= $product['quality_grade'] ?>"><?= ucfirst($product['quality_grade']) ?></span>
                    </div>

                    <?php if($product['description']): ?>
                    <div class="description">
                        <?= htmlspecialchars(substr($product['description'], 0, 100)) ?><?= strlen($product['description']) > 100 ? '...' : '' ?>
                    </div>
                    <?php endif; ?>

                    <?php if($product['province']): ?>
                    <div class="location">
                        📍 <?= $product['province'] ?><?= $product['district'] ? ', '.$product['district'] : '' ?>
                    </div>
                    <?php endif; ?>

                    <div class="meta">
                        <span>👤 <?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?></span>
                        <span>👁️ <?= $product['views_count'] ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if(empty($products)): ?>
        <div class="empty-state">
            <h3>No products found</h3>
            <p>Try adjusting your filters or search terms</p>
            <a href="products.php" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">View All Products</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>