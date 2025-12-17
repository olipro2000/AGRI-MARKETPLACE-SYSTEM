<?php
session_start();
require_once 'config/database.php';

$db = new Database();
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $current_user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

// Get search parameters
$query = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$province = $_GET['province'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build SQL query
$sql = "SELECT p.*, u.first_name, u.last_name, u.province, u.district 
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.status = 'active'";
$params = [];

if ($query) {
    $sql .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$query%";
    $params[] = "%$query%";
}

if ($category) {
    $sql .= " AND p.category = ?";
    $params[] = $category;
}

if ($province) {
    $sql .= " AND p.province = ?";
    $params[] = $province;
}

if ($minPrice) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

// Sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'popular':
        $sql .= " ORDER BY p.views_count DESC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

$products = $db->query($sql, $params);

// Get provinces for filter
$provinces = ['Kigali City', 'Eastern', 'Western', 'Northern', 'Southern'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
    <style>
        .search-container { max-width: 1400px; margin: 2rem auto; padding: 0 1rem; }
        .search-header { background: white; border-radius: 16px; padding: 2rem; margin-bottom: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .search-header h1 { font-size: 2rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem; }
        .search-stats { color: var(--text-light); font-size: 0.9rem; }
        .search-layout { display: grid; grid-template-columns: 280px 1fr; gap: 1.5rem; }
        .filters { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.08); height: fit-content; position: sticky; top: 90px; }
        .filter-group { margin-bottom: 1.5rem; }
        .filter-group:last-child { margin-bottom: 0; }
        .filter-label { font-weight: 700; color: var(--text); margin-bottom: 0.75rem; display: block; font-size: 0.9rem; }
        .filter-input, .filter-select { width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: 8px; font-size: 0.875rem; outline: none; transition: all 0.2s; }
        .filter-input:focus, .filter-select:focus { border-color: var(--primary); }
        .price-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
        .btn-filter { width: 100%; background: var(--primary); color: white; border: none; padding: 0.875rem; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .btn-filter:hover { background: var(--primary-dark); }
        .btn-clear { width: 100%; background: white; color: var(--text); border: 2px solid var(--border); padding: 0.75rem; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 0.5rem; transition: all 0.2s; }
        .btn-clear:hover { border-color: var(--primary); color: var(--primary); }
        .results { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .results-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid var(--border); }
        .results-count { font-weight: 700; color: var(--text); }
        .sort-select { padding: 0.5rem 1rem; border: 2px solid var(--border); border-radius: 8px; font-weight: 600; cursor: pointer; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .product-card { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: all 0.3s; cursor: pointer; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .product-image { height: 200px; background: var(--bg-alt); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-placeholder { font-size: 3rem; color: var(--text-light); }
        .product-info { padding: 1.25rem; }
        .product-name { font-weight: 700; color: var(--text); margin-bottom: 0.5rem; font-size: 1rem; }
        .product-price { color: var(--primary); font-weight: 800; font-size: 1.25rem; margin-bottom: 0.5rem; }
        .product-meta { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--text-light); }
        .empty-results { text-align: center; padding: 4rem 2rem; }
        .empty-icon { font-size: 4rem; margin-bottom: 1rem; }
        .empty-results h3 { font-size: 1.25rem; font-weight: 700; color: var(--text); margin-bottom: 0.5rem; }
        .empty-results p { color: var(--text-light); }
        @media (max-width: 768px) {
            .search-layout { grid-template-columns: 1fr; }
            .filters { position: static; }
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; }
            .product-image { height: 150px; }
            .product-info { padding: 1rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="search-container">
        <div class="search-header">
            <h1>🔍 Search Results</h1>
            <div class="search-stats">
                <?php if ($query): ?>
                    Showing results for "<strong><?= htmlspecialchars($query) ?></strong>"
                <?php else: ?>
                    Browse all products
                <?php endif; ?>
            </div>
        </div>
        
        <div class="search-layout">
            <aside class="filters">
                <form method="GET" action="search.php">
                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <input type="text" name="q" class="filter-input" placeholder="Product name..." value="<?= htmlspecialchars($query) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Category</label>
                        <select name="category" class="filter-select">
                            <option value="">All Categories</option>
                            <option value="finished_crops" <?= $category === 'finished_crops' ? 'selected' : '' ?>>Finished Crops</option>
                            <option value="seeds" <?= $category === 'seeds' ? 'selected' : '' ?>>Seeds</option>
                            <option value="livestock" <?= $category === 'livestock' ? 'selected' : '' ?>>Livestock</option>
                            <option value="equipment" <?= $category === 'equipment' ? 'selected' : '' ?>>Equipment</option>
                            <option value="tools" <?= $category === 'tools' ? 'selected' : '' ?>>Tools</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Location</label>
                        <select name="province" class="filter-select">
                            <option value="">All Provinces</option>
                            <?php foreach ($provinces as $prov): ?>
                                <option value="<?= $prov ?>" <?= $province === $prov ? 'selected' : '' ?>><?= $prov ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Price Range (RWF)</label>
                        <div class="price-inputs">
                            <input type="number" name="min_price" class="filter-input" placeholder="Min" value="<?= htmlspecialchars($minPrice) ?>">
                            <input type="number" name="max_price" class="filter-input" placeholder="Max" value="<?= htmlspecialchars($maxPrice) ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-filter">Apply Filters</button>
                    <a href="search.php" class="btn-clear" style="display: block; text-align: center; text-decoration: none;">Clear All</a>
                </form>
            </aside>
            
            <main class="results">
                <div class="results-header">
                    <span class="results-count"><?= count($products) ?> Products Found</span>
                    <select class="sort-select" onchange="location.href='?<?= http_build_query(array_merge($_GET, ['sort' => ''])) ?>' + this.value">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                    </select>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="empty-results">
                        <div class="empty-icon">🔍</div>
                        <h3>No products found</h3>
                        <p>Try adjusting your filters or search terms</p>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card" onclick="location.href='product-detail.php?id=<?= $product['id'] ?>'">
                                <div class="product-image">
                                    <?php if ($product['main_image'] && file_exists('uploads/products/' . $product['main_image'])): ?>
                                        <img src="uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                                    <?php else: ?>
                                        <div class="product-placeholder">🌾</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                                    <div class="product-price"><?= number_format($product['price']) ?> RWF</div>
                                    <div class="product-meta">
                                        <span>📍 <?= htmlspecialchars($product['district']) ?></span>
                                        <span>👁️ <?= $product['views_count'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <?php include 'includes/bottom-nav.php'; ?>
</body>
</html>
