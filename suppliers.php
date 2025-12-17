<?php
session_start();
require_once 'config/database.php';

$db = new Database();

// Get filters
$search = $_GET['search'] ?? '';
$province = $_GET['province'] ?? '';
$plan = $_GET['plan'] ?? '';

// Build query
$where = "us.status = 'active' AND us.expires_at > NOW()";
$params = [];

if ($search) {
    $where .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.business_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($province) {
    $where .= " AND u.province = ?";
    $params[] = $province;
}

if ($plan) {
    $where .= " AND us.plan_id = ?";
    $params[] = $plan;
}

try {
    $suppliers = $db->fetchAll("
        SELECT u.*, sp.name as plan_name, sp.price as plan_price, us.expires_at,
               (SELECT COUNT(*) FROM products WHERE user_id = u.id AND status = 'active') as product_count
        FROM users u
        JOIN user_subscriptions us ON u.id = us.user_id
        LEFT JOIN subscription_plans sp ON us.plan_id = sp.id
        WHERE $where
        ORDER BY us.created_at DESC
    ", $params);
    
    $provinces = $db->fetchAll("SELECT DISTINCT province FROM users WHERE province IS NOT NULL ORDER BY province");
    $plans = $db->fetchAll("SELECT * FROM subscription_plans ORDER BY price ASC");
} catch (Exception $e) {
    $suppliers = [];
    $provinces = [];
    $plans = [];
}

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
    <title>Verified Suppliers - Curuzamuhinzi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding-top: 80px; padding-bottom: 80px; }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }
        
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #27ae60; font-size: 2rem; margin-bottom: 10px; }
        .header p { color: #666; font-size: 1rem; }
        
        .filters-bar { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .filters-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: end; }
        .filter-group { flex: 1; min-width: 200px; }
        .filter-group label { display: block; font-size: 0.85rem; color: #666; margin-bottom: 5px; font-weight: 600; }
        .filter-group input, .filter-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 0.95rem; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 0.95rem; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: #27ae60; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        
        .results-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .results-count { color: #666; font-size: 0.95rem; }
        
        .suppliers-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
        .supplier-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
        .supplier-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        
        .supplier-header { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; padding: 20px; position: relative; }
        .verified-badge { position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.3); padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 5px; }
        
        .supplier-avatar { width: 80px; height: 80px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: #27ae60; margin: 0 auto 15px; overflow: hidden; border: 4px solid rgba(255,255,255,0.3); }
        .supplier-avatar img { width: 100%; height: 100%; object-fit: cover; }
        
        .supplier-name { font-size: 1.3rem; font-weight: 700; text-align: center; margin-bottom: 5px; }
        .supplier-plan { text-align: center; font-size: 0.85rem; opacity: 0.9; }
        
        .supplier-body { padding: 20px; }
        .info-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; }
        .info-row:last-child { border-bottom: none; }
        .info-icon { font-size: 1.2rem; }
        .info-label { color: #666; flex: 1; }
        .info-value { font-weight: 600; color: #333; }
        
        .supplier-actions { display: flex; gap: 10px; margin-top: 15px; }
        .btn-action { flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-align: center; text-decoration: none; display: block; }
        .btn-view { background: #27ae60; color: white; }
        .btn-view:hover { background: #219a52; }
        .btn-contact { background: #3498db; color: white; }
        .btn-contact:hover { background: #2980b9; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .empty-state h3 { font-size: 1.5rem; margin-bottom: 10px; }
        
        @media (max-width: 768px) {
            body { padding-top: 70px; }
            .filters-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .suppliers-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="header">
            <h1>🛡️ Verified Suppliers</h1>
            <p>Connect with trusted agricultural suppliers across Rwanda</p>
        </div>
        
        <form method="GET" class="filters-bar">
            <div class="filters-row">
                <div class="filter-group">
                    <label>Search Suppliers</label>
                    <input type="text" name="search" placeholder="Name or business..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="filter-group">
                    <label>Province</label>
                    <select name="province">
                        <option value="">All Provinces</option>
                        <?php foreach($provinces as $prov): ?>
                            <option value="<?= $prov['province'] ?>" <?= $province === $prov['province'] ? 'selected' : '' ?>><?= $prov['province'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Subscription Plan</label>
                    <select name="plan">
                        <option value="">All Plans</option>
                        <?php foreach($plans as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $plan == $p['id'] ? 'selected' : '' ?>><?= $p['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="suppliers.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
        
        <div class="results-bar">
            <div class="results-count"><?= count($suppliers) ?> verified supplier<?= count($suppliers) !== 1 ? 's' : '' ?> found</div>
        </div>
        
        <div class="suppliers-grid">
            <?php foreach($suppliers as $supplier): ?>
            <div class="supplier-card">
                <div class="supplier-header">
                    <div class="verified-badge">
                        ✓ Verified
                    </div>
                    <div class="supplier-avatar">
                        <?php if($supplier['profile_picture']): ?>
                            <img src="/curuzamuhinzi/uploads/profiles/<?= htmlspecialchars($supplier['profile_picture']) ?>" alt="<?= htmlspecialchars($supplier['first_name']) ?>">
                        <?php else: ?>
                            <?= strtoupper(substr($supplier['first_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="supplier-name"><?= htmlspecialchars($supplier['first_name'] . ' ' . $supplier['last_name']) ?></div>
                    <div class="supplier-plan"><?= htmlspecialchars($supplier['plan_name']) ?> Member</div>
                </div>
                
                <div class="supplier-body">
                    <?php if(!empty($supplier['business_name'])): ?>
                    <div class="info-row">
                        <span class="info-icon">🏢</span>
                        <span class="info-label">Business</span>
                        <span class="info-value"><?= htmlspecialchars($supplier['business_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-row">
                        <span class="info-icon">📍</span>
                        <span class="info-label">Location</span>
                        <span class="info-value"><?= htmlspecialchars($supplier['district'] . ', ' . $supplier['province']) ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-icon">📦</span>
                        <span class="info-label">Products</span>
                        <span class="info-value"><?= $supplier['product_count'] ?> active</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-icon">👤</span>
                        <span class="info-label">Type</span>
                        <span class="info-value"><?= ucfirst(str_replace('_', ' ', $supplier['user_type'])) ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-icon">📅</span>
                        <span class="info-label">Member Since</span>
                        <span class="info-value"><?= date('M Y', strtotime($supplier['created_at'])) ?></span>
                    </div>
                    
                    <div class="supplier-actions">
                        <a href="user-profile.php?id=<?= $supplier['id'] ?>" class="btn-action btn-view">View Profile</a>
                        <a href="chat.php?user=<?= $supplier['id'] ?>" class="btn-action btn-contact">Message</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(empty($suppliers)): ?>
        <div class="empty-state">
            <h3>No verified suppliers found</h3>
            <p>Try adjusting your filters or check back later</p>
            <a href="suppliers.php" class="btn btn-primary" style="margin-top: 20px;">View All Suppliers</a>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/bottom-nav.php'; ?>
</body>
</html>
