<?php
session_start();
require_once '../config/database.php';

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();

// Get admin info
$admin = $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);

// Get dashboard stats from database
$stats = [
    'total_users' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users"),
    'active_users' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'active'"),
    'active_farmers' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE user_type = 'farmer'"),
    'total_buyers' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE user_type = 'buyer'"),
    'total_suppliers' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE user_type = 'supplier'"),
    'cooperative_members' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE user_type = 'cooperative_member'"),
    'total_products' => (int)$db->fetchColumn("SELECT COUNT(*) FROM products"),
    'draft_products' => (int)$db->fetchColumn("SELECT COUNT(*) FROM products WHERE status = 'draft'"),
    'sold_out_products' => (int)$db->fetchColumn("SELECT COUNT(*) FROM products WHERE status = 'sold_out'"),
    'featured_products' => (int)$db->fetchColumn("SELECT COUNT(*) FROM products WHERE featured = 1")
];

try {
    
    // Get recent activity counts
    $recent_stats = [
        'new_users_today' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()") ?: 0,
        'new_products_today' => $db->fetchColumn("SELECT COUNT(*) FROM products WHERE DATE(created_at) = CURDATE()") ?: 0,
        'active_sessions' => 0, // user_sessions table may not exist
        'new_users_week' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)") ?: 0,
        'new_products_week' => $db->fetchColumn("SELECT COUNT(*) FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)") ?: 0
    ];
    
    // Get category breakdown
    $category_stats = $db->fetchAll("SELECT category, COUNT(*) as count FROM products WHERE status = 'active' GROUP BY category ORDER BY count DESC");
    
    // Get user type breakdown
    $user_type_stats = $db->fetchAll("SELECT user_type, COUNT(*) as count FROM users WHERE status = 'active' GROUP BY user_type ORDER BY count DESC");
    
    // Get location stats
    $location_stats = $db->fetchAll("SELECT province, COUNT(*) as count FROM users WHERE province IS NOT NULL GROUP BY province ORDER BY count DESC LIMIT 5");
    
    // Get recent users with profile completion
    $recent_users = $db->fetchAll("SELECT id, first_name, last_name, user_type, COALESCE(profile_completion_percentage, 0) as profile_completion_percentage, created_at, province, district FROM users ORDER BY created_at DESC LIMIT 5");
    
    // Get recent products with more details
    $recent_products = $db->fetchAll("SELECT p.id, p.product_name, p.category, p.price, p.unit, p.status, p.created_at, COALESCE(p.views_count, 0) as views_count, u.first_name, u.last_name, u.province FROM products p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5");
    
    // Get top performing products
    $top_products = $db->fetchAll("SELECT p.id, p.product_name, p.category, p.price, COALESCE(p.views_count, 0) as views_count, u.first_name, u.last_name FROM products p JOIN users u ON p.user_id = u.id WHERE p.status = 'active' ORDER BY COALESCE(p.views_count, 0) DESC LIMIT 5");
    
    // Get subscription stats
    $subscription_stats = $db->fetchAll("SELECT COALESCE(subscription_type, 'free') as subscription_type, COUNT(*) as count FROM users GROUP BY COALESCE(subscription_type, 'free')");
    
    // Get average product price by category
    $price_stats = $db->fetchAll("SELECT category, AVG(price) as avg_price, COUNT(*) as count FROM products WHERE status = 'active' AND price > 0 GROUP BY category ORDER BY avg_price DESC");
} catch (Exception $e) {
    // Fallback to zero values if tables don't exist yet
    $stats = [
        'total_users' => 0,
        'active_users' => 0,
        'active_farmers' => 0,
        'total_buyers' => 0,
        'total_suppliers' => 0,
        'cooperative_members' => 0,
        'total_products' => 0,
        'draft_products' => 0,
        'sold_out_products' => 0,
        'featured_products' => 0
    ];
    
    $recent_stats = [
        'new_users_today' => 0,
        'new_products_today' => 0,
        'active_sessions' => 0,
        'new_users_week' => 0,
        'new_products_week' => 0
    ];
    
    $category_stats = [];
    $user_type_stats = [];
    $location_stats = [];
    $recent_users = [];
    $recent_products = [];
    $top_products = [];
    $subscription_stats = [];
    $price_stats = [];
}

// Set page variables
$page_title = 'Dashboard';
$current_page = 'dashboard';

// Include header
include 'includes/header.php';
?>

<style>
    /* Main Content */
    .main-content {
        padding: 2rem;
        background: var(--bg-primary);
        min-height: calc(100vh - 64px);
    }
    
    /* Dashboard Styles */
    .page-header {
        margin-bottom: 2rem;
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    
    .page-subtitle {
        color: var(--text-secondary);
        font-size: 1rem;
    }
    
    .welcome-card {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-light));
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(22, 163, 74, 0.3);
    }
    
    .welcome-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .welcome-text {
        opacity: 0.9;
        font-size: 1rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: var(--bg-secondary);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-accent);
    }
    
    .stat-card.primary { --card-accent: var(--primary-green); }
    .stat-card.success { --card-accent: var(--success-color); }
    .stat-card.warning { --card-accent: var(--warning-color); }
    .stat-card.info { --card-accent: var(--info-color); }
    
    .stat-header {
        display: flex;
        justify-content: between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        background: rgba(22, 163, 74, 0.1);
        color: var(--primary-green);
        margin-bottom: 1rem;
    }
    
    .stat-value {
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.75rem;
    }
    
    .stat-change {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
    }
    
    .stat-change.positive {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }
    
    .stat-change.negative {
        background: rgba(239, 68, 68, 0.1);
        color: var(--error-color);
    }
    
    .stat-change.neutral {
        background: rgba(107, 114, 128, 0.1);
        color: var(--text-secondary);
    }
    
    .stat-change.warning {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning-color);
    }
    
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }
    
    .quick-actions {
        background: var(--bg-secondary);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        margin-bottom: 2rem;
    }
    
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1rem;
    }
    
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .action-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: var(--bg-primary);
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-primary);
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    
    .action-item:hover {
        background: var(--primary-green);
        color: white;
        border-color: var(--primary-green);
    }
    
    .action-icon {
        font-size: 1.25rem;
    }
    
    .action-content h4 {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .action-content p {
        font-size: 0.75rem;
        opacity: 0.7;
        margin: 0;
    }
    
    .system-status {
        background: var(--bg-secondary);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }
    
    .status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .status-item:last-child {
        border-bottom: none;
    }
    
    .status-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-badge.online {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }
    
    .status-badge.warning {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning-color);
    }
    

    
    /* Mobile Responsive */
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .main-content {
            padding: 1rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .actions-grid {
            grid-template-columns: 1fr;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .welcome-card {
            padding: 1.5rem;
        }
    }
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, <?= htmlspecialchars($admin['first_name']) ?>! Here's your platform overview.</p>

        </div>
        
        <!-- Welcome Card -->
        <div class="welcome-card">
            <h2 class="welcome-title">Good <?= date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening') ?>, <?= htmlspecialchars($admin['first_name']) ?>!</h2>
            <p class="welcome-text">You're managing the agricultural platform as admin. Here's today's overview.</p>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">👥</div>
                <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
                <div class="stat-label">Total Users</div>
                <div class="stat-change <?= $recent_stats['new_users_today'] > 0 ? 'positive' : 'neutral' ?>">
                    <span><?= $recent_stats['new_users_today'] > 0 ? '↗' : '→' ?></span>
                    <span><?= $recent_stats['new_users_today'] ?> new today</span>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?= number_format($stats['active_users']) ?></div>
                <div class="stat-label">Active Users</div>
                <div class="stat-change <?= $stats['active_users'] > 0 ? 'positive' : 'neutral' ?>">
                    <span><?= $stats['active_users'] > 0 ? '✓' : '→' ?></span>
                    <span><?= round(($stats['active_users'] / max($stats['total_users'], 1)) * 100) ?>% of users</span>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">🌱</div>
                <div class="stat-value"><?= number_format($stats['active_farmers']) ?></div>
                <div class="stat-label">Farmers</div>
                <div class="stat-change <?= $stats['active_farmers'] > 0 ? 'positive' : 'neutral' ?>">
                    <span><?= $stats['active_farmers'] > 0 ? '✓' : '→' ?></span>
                    <span><?= round(($stats['active_farmers'] / max($stats['total_users'], 1)) * 100) ?>% of users</span>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">📝</div>
                <div class="stat-value"><?= number_format($stats['total_products']) ?></div>
                <div class="stat-label">Active Products</div>
                <div class="stat-change <?= $recent_stats['new_products_today'] > 0 ? 'positive' : 'neutral' ?>">
                    <span><?= $recent_stats['new_products_today'] > 0 ? '↗' : '→' ?></span>
                    <span><?= $recent_stats['new_products_today'] ?> new today</span>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">🛒</div>
                <div class="stat-value"><?= number_format($stats['total_buyers']) ?></div>
                <div class="stat-label">Buyers</div>
                <div class="stat-change <?= $stats['total_buyers'] > 0 ? 'positive' : 'neutral' ?>">
                    <span><?= $stats['total_buyers'] > 0 ? '✓' : '→' ?></span>
                    <span><?= round(($stats['total_buyers'] / max($stats['total_users'], 1)) * 100) ?>% of users</span>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">🤝</div>
                <div class="stat-value"><?= number_format($stats['cooperative_members']) ?></div>
                <div class="stat-label">Cooperative Members</div>
                <div class="stat-change <?= $stats['cooperative_members'] > 0 ? 'positive' : 'neutral' ?>">
                    <span><?= $stats['cooperative_members'] > 0 ? '✓' : '→' ?></span>
                    <span><?= round(($stats['cooperative_members'] / max($stats['total_users'], 1)) * 100) ?>% of users</span>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">📋</div>
                <div class="stat-value"><?= number_format($stats['draft_products']) ?></div>
                <div class="stat-label">Draft Products</div>
                <div class="stat-change <?= $stats['draft_products'] > 0 ? 'warning' : 'neutral' ?>">
                    <span><?= $stats['draft_products'] > 0 ? '⚠' : '→' ?></span>
                    <span>Pending review</span>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">⭐</div>
                <div class="stat-value"><?= number_format($stats['featured_products']) ?></div>
                <div class="stat-label">Featured Products</div>
                <div class="stat-change <?= $stats['featured_products'] > 0 ? 'positive' : 'neutral' ?>">
                    <span><?= $stats['featured_products'] > 0 ? '✓' : '→' ?></span>
                    <span>Promoted items</span>
                </div>
            </div>
        </div>
        
        <!-- Analytics Overview -->
        <div class="content-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 2rem;">
            <!-- Category Breakdown -->
            <div class="system-status">
                <h2 class="section-title">Product Categories</h2>
                <?php if (!empty($category_stats)): ?>
                    <?php foreach ($category_stats as $category): ?>
                        <div class="status-item">
                            <div>
                                <span class="status-label"><?= ucfirst(str_replace('_', ' ', $category['category'])) ?></span>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);"><?= $category['count'] ?> products</div>
                            </div>
                            <span class="status-badge online"><?= round(($category['count'] / max($stats['total_products'], 1)) * 100) ?>%</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="status-item">
                        <span class="status-label">No categories yet</span>
                        <span class="status-badge warning">Empty</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Location Stats -->
            <div class="system-status">
                <h2 class="section-title">Top Provinces</h2>
                <?php if (!empty($location_stats)): ?>
                    <?php foreach ($location_stats as $location): ?>
                        <div class="status-item">
                            <div>
                                <span class="status-label"><?= htmlspecialchars($location['province']) ?></span>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);"><?= $location['count'] ?> users</div>
                            </div>
                            <span class="status-badge online"><?= round(($location['count'] / max($stats['total_users'], 1)) * 100) ?>%</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="status-item">
                        <span class="status-label">No location data</span>
                        <span class="status-badge warning">Empty</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2 class="section-title">Quick Actions</h2>
                <div class="actions-grid">
                    <a href="users.php" class="action-item">
                        <div class="action-icon">👥</div>
                        <div class="action-content">
                            <h4>Manage Users</h4>
                            <p><?= number_format($stats['total_users']) ?> total users</p>
                        </div>
                    </a>
                    
                    <a href="products.php" class="action-item">
                        <div class="action-icon">📝</div>
                        <div class="action-content">
                            <h4>Manage Products</h4>
                            <p><?= number_format($stats['total_products']) ?> active products</p>
                        </div>
                    </a>
                    
                    <a href="analytics.php" class="action-item">
                        <div class="action-icon">📈</div>
                        <div class="action-content">
                            <h4>View Analytics</h4>
                            <p>Platform insights & reports</p>
                        </div>
                    </a>
                    
                    <a href="../feed.php" class="action-item">
                        <div class="action-icon">🌾</div>
                        <div class="action-content">
                            <h4>View Feed</h4>
                            <p>Live platform activity</p>
                        </div>
                    </a>
                    
                    <a href="settings.php" class="action-item">
                        <div class="action-icon">⚙️</div>
                        <div class="action-content">
                            <h4>Platform Settings</h4>
                            <p>Configure system settings</p>
                        </div>
                    </a>
                    
                    <a href="reports.php" class="action-item">
                        <div class="action-icon">📊</div>
                        <div class="action-content">
                            <h4>Generate Reports</h4>
                            <p>Export data & analytics</p>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div>
                <!-- Recent Users -->
                <div class="system-status" style="margin-bottom: 1.5rem;">
                    <h2 class="section-title">Recent Users</h2>
                    <?php if (!empty($recent_users)): ?>
                        <?php foreach ($recent_users as $user): ?>
                            <div class="status-item">
                                <div>
                                    <span class="status-label"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                        <?= ucfirst(str_replace('_', ' ', $user['user_type'])) ?> • 
                                        <?= $user['profile_completion_percentage'] ?>% complete • 
                                        <?= htmlspecialchars($user['province'] ?: 'No location') ?>
                                    </div>
                                </div>
                                <span class="status-badge <?= $user['profile_completion_percentage'] >= 70 ? 'online' : 'warning' ?>"><?= date('M j', strtotime($user['created_at'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="status-item">
                            <span class="status-label">No users yet</span>
                            <span class="status-badge warning">Empty</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Products -->
                <div class="system-status" style="margin-bottom: 1.5rem;">
                    <h2 class="section-title">Recent Products</h2>
                    <?php if (!empty($recent_products)): ?>
                        <?php foreach ($recent_products as $product): ?>
                            <div class="status-item">
                                <div>
                                    <span class="status-label"><?= htmlspecialchars($product['product_name']) ?></span>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">by <?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?> • <?= number_format($product['price']) ?> RWF/<?= $product['unit'] ?> • <?= $product['views_count'] ?> views</div>
                                </div>
                                <span class="status-badge <?= $product['status'] === 'active' ? 'online' : 'warning' ?>"><?= ucfirst($product['status']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="status-item">
                            <span class="status-label">No products yet</span>
                            <span class="status-badge warning">Empty</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Top Performing Products -->
                <div class="system-status">
                    <h2 class="section-title">Top Performing Products</h2>
                    <?php if (!empty($top_products)): ?>
                        <?php foreach ($top_products as $product): ?>
                            <div class="status-item">
                                <div>
                                    <span class="status-label"><?= htmlspecialchars($product['product_name']) ?></span>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">by <?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?> • <?= number_format($product['price']) ?> RWF</div>
                                </div>
                                <span class="status-badge online"><?= $product['views_count'] ?> views</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="status-item">
                            <span class="status-label">No product data yet</span>
                            <span class="status-badge warning">Empty</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- System Health & Subscription Overview -->
        <div class="content-grid" style="margin-top: 2rem;">
            <!-- System Health -->
            <div class="system-status">
                <h2 class="section-title">System Health</h2>
                <div class="status-item">
                    <span class="status-label">Database Connection</span>
                    <span class="status-badge online">Online</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Active Sessions</span>
                    <span class="status-badge warning">Not Available</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Weekly Growth</span>
                    <span class="status-badge <?= $recent_stats['new_users_week'] > 0 ? 'online' : 'warning' ?>"><?= $recent_stats['new_users_week'] ?> new users</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Product Activity</span>
                    <span class="status-badge <?= $recent_stats['new_products_week'] > 0 ? 'online' : 'warning' ?>"><?= $recent_stats['new_products_week'] ?> new products</span>
                </div>
            </div>
            
            <!-- Subscription Overview -->
            <div class="system-status">
                <h2 class="section-title">Subscription Overview</h2>
                <?php if (!empty($subscription_stats)): ?>
                    <?php foreach ($subscription_stats as $subscription): ?>
                        <div class="status-item">
                            <div>
                                <span class="status-label"><?= ucfirst($subscription['subscription_type']) ?> Plan</span>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);"><?= $subscription['count'] ?> users</div>
                            </div>
                            <span class="status-badge <?= $subscription['subscription_type'] === 'premium' ? 'online' : ($subscription['subscription_type'] === 'basic' ? 'warning' : 'neutral') ?>">
                                <?= round(($subscription['count'] / max($stats['total_users'], 1)) * 100) ?>%
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="status-item">
                        <span class="status-label">No subscription data</span>
                        <span class="status-badge warning">Empty</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Price Analytics -->
        <?php if (!empty($price_stats)): ?>
        <div class="system-status" style="margin-top: 2rem;">
            <h2 class="section-title">Average Product Prices by Category</h2>
            <?php foreach ($price_stats as $price): ?>
                <div class="status-item">
                    <div>
                        <span class="status-label"><?= ucfirst(str_replace('_', ' ', $price['category'])) ?></span>
                        <div style="font-size: 0.75rem; color: var(--text-secondary);"><?= $price['count'] ?> products</div>
                    </div>
                    <span class="status-badge online"><?= number_format($price['avg_price']) ?> RWF</span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>