<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$admin = $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);

// Handle user actions
if ($_POST['action'] ?? false) {
    $user_id = $_POST['user_id'] ?? 0;
    $action = $_POST['action'];
    
    try {
        switch ($action) {
            case 'activate':
                $db->query("UPDATE users SET status = 'active' WHERE id = ?", [$user_id]);
                $_SESSION['success'] = "User activated successfully";
                break;
            case 'suspend':
                $db->query("UPDATE users SET status = 'suspended' WHERE id = ?", [$user_id]);
                $_SESSION['success'] = "User suspended successfully";
                break;
            case 'delete':
                $db->query("DELETE FROM users WHERE id = ?", [$user_id]);
                $_SESSION['success'] = "User deleted successfully";
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Action failed: " . $e->getMessage();
    }
    
    header('Location: users.php');
    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$user_type = $_GET['user_type'] ?? '';
$status = $_GET['status'] ?? '';
$province = $_GET['province'] ?? '';

// Build query
$where = "1=1";
$params = [];

if ($search) {
    $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($user_type) {
    $where .= " AND user_type = ?";
    $params[] = $user_type;
}

if ($status) {
    $where .= " AND status = ?";
    $params[] = $status;
}

if ($province) {
    $where .= " AND province = ?";
    $params[] = $province;
}

try {
    $users = $db->fetchAll("SELECT * FROM users WHERE $where ORDER BY created_at DESC LIMIT 100", $params);
    $provinces = $db->fetchAll("SELECT DISTINCT province FROM users WHERE province IS NOT NULL ORDER BY province");
    
    $stats = [
        'total' => $db->fetchColumn("SELECT COUNT(*) FROM users"),
        'active' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'active'"),
        'suspended' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'suspended'"),
        'farmers' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE user_type = 'farmer'"),
    ];
} catch (Exception $e) {
    $users = [];
    $provinces = [];
    $stats = ['total' => 0, 'active' => 0, 'suspended' => 0, 'farmers' => 0];
}

$page_title = 'Users';
$current_page = 'users';
include 'includes/header.php';
?>

<style>
    :root {
        --bg-primary: #ffffff;
        --bg-secondary: #f8fafc;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --border-color: #e5e7eb;
        --primary-green: #16a34a;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --info-color: #3b82f6;
        --error-color: #ef4444;
    }
    
    .main-content {
        padding: 2rem;
        background: var(--bg-primary);
        min-height: calc(100vh - 64px);
    }
    
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
    
    .alert {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    
    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        color: var(--error-color);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    
    .filters-card {
        background: var(--bg-secondary);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
    }
    
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .filter-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
    }
    
    .filter-input {
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.875rem;
        background: var(--bg-primary);
    }
    
    .filter-input:focus {
        outline: none;
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }
    
    .btn-filter {
        padding: 0.75rem 1.5rem;
        background: var(--primary-green);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-filter:hover {
        background: var(--primary-dark);
    }
    
    .users-table {
        background: var(--bg-secondary);
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }
    
    .table-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .table-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .table-count {
        font-size: 0.875rem;
        color: var(--text-secondary);
        background: rgba(22, 163, 74, 0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table th {
        background: var(--bg-primary);
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
        font-size: 0.875rem;
    }
    
    .data-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.875rem;
    }
    
    .data-table tr:hover {
        background: rgba(22, 163, 74, 0.05);
    }
    
    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .user-details h4 {
        margin: 0 0 0.25rem 0;
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .user-details p {
        margin: 0;
        font-size: 0.8rem;
        color: var(--text-secondary);
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-active {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }
    
    .status-suspended {
        background: rgba(239, 68, 68, 0.1);
        color: var(--error-color);
    }
    
    .status-banned {
        background: rgba(107, 114, 128, 0.1);
        color: var(--text-secondary);
    }
    
    .user-type-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 500;
        background: rgba(22, 163, 74, 0.1);
        color: var(--primary-green);
    }
    
    .actions-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .actions-btn {
        background: none;
        border: 1px solid var(--border-color);
        padding: 0.5rem;
        border-radius: 6px;
        cursor: pointer;
        color: var(--text-secondary);
    }
    
    .actions-menu {
        position: absolute;
        right: 0;
        top: 100%;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        min-width: 120px;
        z-index: 10;
        display: none;
    }
    
    .actions-menu.show {
        display: block;
    }
    
    .action-item {
        padding: 0.5rem 1rem;
        cursor: pointer;
        font-size: 0.875rem;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        color: var(--text-primary);
    }
    
    .action-item:hover {
        background: rgba(22, 163, 74, 0.1);
    }
    
    .action-item.danger {
        color: var(--error-color);
    }
    
    .action-item.danger:hover {
        background: rgba(239, 68, 68, 0.1);
    }
    
    @media (max-width: 768px) {
        .filters-grid {
            grid-template-columns: 1fr;
        }
        
        .table-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
        
        .data-table th,
        .data-table td {
            padding: 0.75rem 0.5rem;
        }
        
        .user-info {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }
    }
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">Manage platform users, permissions, and account status</p>
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
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">👥</div>
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?= number_format($stats['active']) ?></div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">⏸️</div>
                <div class="stat-value"><?= number_format($stats['suspended']) ?></div>
                <div class="stat-label">Suspended</div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">🌱</div>
                <div class="stat-value"><?= number_format($stats['farmers']) ?></div>
                <div class="stat-label">Farmers</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">Search Users</label>
                    <input type="text" name="search" class="filter-input" placeholder="Name or email..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="filter-group">
                    <label class="filter-label">User Type</label>
                    <select name="user_type" class="filter-input">
                        <option value="">All Types</option>
                        <option value="farmer" <?= $user_type === 'farmer' ? 'selected' : '' ?>>Farmer</option>
                        <option value="buyer" <?= $user_type === 'buyer' ? 'selected' : '' ?>>Buyer</option>
                        <option value="supplier" <?= $user_type === 'supplier' ? 'selected' : '' ?>>Supplier</option>
                        <option value="cooperative_member" <?= $user_type === 'cooperative_member' ? 'selected' : '' ?>>Cooperative Member</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select name="status" class="filter-input">
                        <option value="">All Status</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="banned" <?= $status === 'banned' ? 'selected' : '' ?>>Banned</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Province</label>
                    <select name="province" class="filter-input">
                        <option value="">All Provinces</option>
                        <?php foreach ($provinces as $prov): ?>
                            <option value="<?= htmlspecialchars($prov['province']) ?>" <?= $province === $prov['province'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prov['province']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn-filter">Filter</button>
                </div>
            </form>
        </div>
        
        <!-- Users Table -->
        <div class="users-table">
            <div class="table-header">
                <h3 class="table-title">Users</h3>
                <span class="table-count"><?= count($users) ?> users</span>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Profile</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                                            </div>
                                            <div class="user-details">
                                                <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                                                <p><?= htmlspecialchars($user['email']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="user-type-badge">
                                            <?= ucfirst(str_replace('_', ' ', $user['user_type'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($user['province'] ?: 'Not set') ?><br>
                                        <small><?= htmlspecialchars($user['district'] ?: '') ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $user['status'] ?>">
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                    <td><?= $user['profile_completion_percentage'] ?>%</td>
                                    <td>
                                        <div class="actions-dropdown">
                                            <button class="actions-btn" onclick="toggleActions(this)">⋮</button>
                                            <div class="actions-menu">
                                                <?php if ($user['status'] !== 'active'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <input type="hidden" name="action" value="activate">
                                                        <button type="submit" class="action-item">Activate</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($user['status'] !== 'suspended'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <input type="hidden" name="action" value="suspend">
                                                        <button type="submit" class="action-item danger">Suspend</button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user permanently?')">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="action-item danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                    No users found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
function toggleActions(btn) {
    const menu = btn.nextElementSibling;
    const isVisible = menu.classList.contains('show');
    
    // Close all menus
    document.querySelectorAll('.actions-menu').forEach(m => m.classList.remove('show'));
    
    // Toggle current menu
    if (!isVisible) {
        menu.classList.add('show');
    }
}

// Close menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.actions-dropdown')) {
        document.querySelectorAll('.actions-menu').forEach(m => m.classList.remove('show'));
    }
});
</script>

</body>
</html>