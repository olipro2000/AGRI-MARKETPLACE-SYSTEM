<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$admin = $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);

// Get analytics data
try {
    // User growth data (last 30 days)
    $user_growth = $db->fetchAll("SELECT DATE(created_at) as date, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date");
    
    // Product growth data
    $product_growth = $db->fetchAll("SELECT DATE(created_at) as date, COUNT(*) as count FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date");
    
    // Category distribution
    $category_data = $db->fetchAll("SELECT category, COUNT(*) as count FROM products GROUP BY category ORDER BY count DESC");
    
    // User type distribution
    $user_type_data = $db->fetchAll("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type ORDER BY count DESC");
    
    // Province distribution
    $province_data = $db->fetchAll("SELECT province, COUNT(*) as count FROM users WHERE province IS NOT NULL GROUP BY province ORDER BY count DESC LIMIT 10");
    
    // Monthly stats
    $monthly_stats = $db->fetchAll("SELECT MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as users, (SELECT COUNT(*) FROM products WHERE MONTH(created_at) = MONTH(u.created_at) AND YEAR(created_at) = YEAR(u.created_at)) as products FROM users u WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY year, month");
    
} catch (Exception $e) {
    $user_growth = [];
    $product_growth = [];
    $category_data = [];
    $user_type_data = [];
    $province_data = [];
    $monthly_stats = [];
}

$page_title = 'Analytics';
$current_page = 'analytics';
include 'includes/header.php';
?>

<style>
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .chart-card {
        background: var(--bg-secondary);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .chart-period {
        font-size: 0.8rem;
        color: var(--text-secondary);
        background: rgba(22, 163, 74, 0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
    }
    
    .data-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .data-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .data-item:last-child {
        border-bottom: none;
    }
    
    .data-label {
        font-weight: 500;
        color: var(--text-primary);
    }
    
    .data-value {
        font-weight: 600;
        color: var(--primary-green);
    }
    
    .progress-bar {
        width: 100%;
        height: 6px;
        background: rgba(22, 163, 74, 0.1);
        border-radius: 3px;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    
    .progress-fill {
        height: 100%;
        background: var(--primary-green);
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .summary-card {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-dark));
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
    }
    
    .summary-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .summary-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    @media (max-width: 768px) {
        .analytics-grid {
            grid-template-columns: 1fr;
        }
        
        .summary-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Analytics & Insights</h1>
            <p class="page-subtitle">Comprehensive platform analytics and performance metrics</p>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-value"><?= count($user_growth) ?></div>
                <div class="summary-label">Active Days</div>
            </div>
            <div class="summary-card">
                <div class="summary-value"><?= array_sum(array_column($user_growth, 'count')) ?></div>
                <div class="summary-label">New Users (30d)</div>
            </div>
            <div class="summary-card">
                <div class="summary-value"><?= array_sum(array_column($product_growth, 'count')) ?></div>
                <div class="summary-label">New Products (30d)</div>
            </div>
            <div class="summary-card">
                <div class="summary-value"><?= count($category_data) ?></div>
                <div class="summary-label">Active Categories</div>
            </div>
        </div>
        
        <!-- Analytics Grid -->
        <div class="analytics-grid">
            <!-- Category Distribution -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Product Categories</h3>
                    <span class="chart-period">All Time</span>
                </div>
                <div class="data-list">
                    <?php if (!empty($category_data)): ?>
                        <?php $max_count = max(array_column($category_data, 'count')); ?>
                        <?php foreach ($category_data as $category): ?>
                            <div class="data-item">
                                <div>
                                    <div class="data-label"><?= ucfirst(str_replace('_', ' ', $category['category'])) ?></div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= ($category['count'] / $max_count) * 100 ?>%"></div>
                                    </div>
                                </div>
                                <div class="data-value"><?= $category['count'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="data-item">
                            <div class="data-label">No data available</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- User Types -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">User Distribution</h3>
                    <span class="chart-period">All Time</span>
                </div>
                <div class="data-list">
                    <?php if (!empty($user_type_data)): ?>
                        <?php $max_users = max(array_column($user_type_data, 'count')); ?>
                        <?php foreach ($user_type_data as $user_type): ?>
                            <div class="data-item">
                                <div>
                                    <div class="data-label"><?= ucfirst(str_replace('_', ' ', $user_type['user_type'])) ?></div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= ($user_type['count'] / $max_users) * 100 ?>%"></div>
                                    </div>
                                </div>
                                <div class="data-value"><?= $user_type['count'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="data-item">
                            <div class="data-label">No data available</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Geographic Distribution -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Top Provinces</h3>
                    <span class="chart-period">By Users</span>
                </div>
                <div class="data-list">
                    <?php if (!empty($province_data)): ?>
                        <?php $max_province = max(array_column($province_data, 'count')); ?>
                        <?php foreach ($province_data as $province): ?>
                            <div class="data-item">
                                <div>
                                    <div class="data-label"><?= htmlspecialchars($province['province']) ?></div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= ($province['count'] / $max_province) * 100 ?>%"></div>
                                    </div>
                                </div>
                                <div class="data-value"><?= $province['count'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="data-item">
                            <div class="data-label">No location data</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Growth -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Daily User Growth</h3>
                    <span class="chart-period">Last 30 Days</span>
                </div>
                <div class="data-list">
                    <?php if (!empty($user_growth)): ?>
                        <?php foreach (array_reverse(array_slice($user_growth, -10)) as $day): ?>
                            <div class="data-item">
                                <div class="data-label"><?= date('M j', strtotime($day['date'])) ?></div>
                                <div class="data-value"><?= $day['count'] ?> users</div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="data-item">
                            <div class="data-label">No growth data</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Monthly Overview -->
        <?php if (!empty($monthly_stats)): ?>
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Monthly Overview</h3>
                <span class="chart-period">Last 12 Months</span>
            </div>
            <div class="data-list">
                <?php foreach (array_reverse($monthly_stats) as $month): ?>
                    <div class="data-item">
                        <div>
                            <div class="data-label"><?= date('F Y', mktime(0, 0, 0, $month['month'], 1, $month['year'])) ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);"><?= $month['products'] ?> products added</div>
                        </div>
                        <div class="data-value"><?= $month['users'] ?> users</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>