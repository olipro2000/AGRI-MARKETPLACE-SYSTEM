<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$admin = $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);

// Handle member actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $memberId = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'suspend') {
        $db->query("UPDATE users SET status = 'suspended' WHERE id = ? AND user_type = 'cooperative_member'", [$memberId]);
    } elseif ($action === 'activate') {
        $db->query("UPDATE users SET status = 'active' WHERE id = ? AND user_type = 'cooperative_member'", [$memberId]);
    }
    
    header('Location: cooperatives.php');
    exit;
}

try {
    $cooperatives = $db->fetchAll("
        SELECT u.*, COUNT(p.id) as product_count
        FROM users u 
        LEFT JOIN products p ON u.id = p.user_id
        WHERE u.user_type = 'cooperative_member'
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    
    $stats = [
        'total_members' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE user_type = 'cooperative_member'"),
        'active_members' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE user_type = 'cooperative_member' AND status = 'active'"),
        'total_products' => $db->fetchColumn("SELECT COUNT(*) FROM products p JOIN users u ON p.user_id = u.id WHERE u.user_type = 'cooperative_member'"),
        'provinces' => $db->fetchColumn("SELECT COUNT(DISTINCT province) FROM users WHERE user_type = 'cooperative_member' AND province IS NOT NULL"),
    ];
} catch (Exception $e) {
    $cooperatives = [];
    $stats = ['total_members' => 0, 'active_members' => 0, 'total_products' => 0, 'provinces' => 0];
}

$page_title = 'Cooperatives';
$current_page = 'cooperatives';
include 'includes/header.php';
?>

<style>
    .member-card { background: var(--bg-secondary); border-radius: 12px; border: 1px solid var(--border-color); padding: 1.5rem; margin-bottom: 1rem; transition: all 0.2s; }
    .member-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .member-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .member-avatar { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-green), var(--primary-dark)); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; overflow: hidden; }
    .member-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .member-info { flex: 1; }
    .member-name { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem; }
    .member-email { font-size: 0.875rem; color: var(--text-secondary); }
    .member-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1rem; background: rgba(22,163,74,0.05); border-radius: 8px; margin-bottom: 1rem; }
    .detail-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .detail-label { font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; }
    .detail-value { font-size: 0.9rem; color: var(--text-primary); font-weight: 600; }
    .member-actions { display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color); }
    .filter-bar { background: white; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
    .filter-input { padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.875rem; }
    @media (max-width: 768px) { .member-details { grid-template-columns: 1fr; } }
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Cooperative Management</h1>
            <p class="page-subtitle">Manage agricultural cooperatives and their members</p>
        </div>
        
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🤝</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['total_members']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Total Members</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['active_members']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Active Members</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📦</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['total_products']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Products Listed</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📍</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['provinces']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Provinces Covered</div>
            </div>
        </div>
        
        <div class="filter-bar">
            <input type="text" class="filter-input" placeholder="Search members..." id="searchInput" onkeyup="filterMembers()">
            <select class="filter-input" id="statusFilter" onchange="filterMembers()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
            <select class="filter-input" id="provinceFilter" onchange="filterMembers()">
                <option value="">All Provinces</option>
                <?php 
                $provinces = $db->query("SELECT DISTINCT province FROM users WHERE user_type = 'cooperative_member' AND province IS NOT NULL ORDER BY province")->fetchAll();
                foreach ($provinces as $prov): 
                ?>
                    <option value="<?= htmlspecialchars($prov['province']) ?>"><?= htmlspecialchars($prov['province']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Cooperative Members</h3>
                <span class="table-count"><?= count($cooperatives) ?> members</span>
            </div>
            <div class="card-content">
                <?php if (empty($cooperatives)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🤝</div>
                        <h3>No cooperative members yet</h3>
                        <p>Cooperative members will appear here once they register.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($cooperatives as $member): ?>
                        <div class="member-card" data-name="<?= strtolower($member['first_name'] . ' ' . $member['last_name']) ?>" data-status="<?= $member['status'] ?>" data-province="<?= $member['province'] ?>">
                            <div class="member-header">
                                <div class="member-avatar">
                                    <?php if ($member['profile_picture'] && file_exists('../uploads/profiles/' . $member['profile_picture'])): ?>
                                        <img src="../uploads/profiles/<?= htmlspecialchars($member['profile_picture']) ?>" alt="<?= htmlspecialchars($member['first_name']) ?>">
                                    <?php else: ?>
                                        <?= strtoupper(substr($member['first_name'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="member-info">
                                    <div class="member-name"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></div>
                                    <div class="member-email"><?= htmlspecialchars($member['email']) ?></div>
                                </div>
                                <span class="status-badge status-<?= $member['status'] ?>"><?= ucfirst($member['status']) ?></span>
                            </div>
                            
                            <div class="member-details">
                                <div class="detail-item">
                                    <span class="detail-label">Phone</span>
                                    <span class="detail-value"><?= htmlspecialchars($member['phone'] ?: 'Not provided') ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Location</span>
                                    <span class="detail-value"><?= htmlspecialchars($member['district'] . ', ' . $member['province']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Farm Size</span>
                                    <span class="detail-value"><?= htmlspecialchars($member['farm_size'] ?: 'Not specified') ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Products</span>
                                    <span class="detail-value"><?= $member['product_count'] ?> listed</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Member Since</span>
                                    <span class="detail-value"><?= date('M j, Y', strtotime($member['created_at'])) ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Last Login</span>
                                    <span class="detail-value"><?= $member['last_login'] ? date('M j, Y', strtotime($member['last_login'])) : 'Never' ?></span>
                                </div>
                            </div>
                            
                            <div class="member-actions">
                                <a href="../user-profile.php?id=<?= $member['id'] ?>" class="btn-action btn-view" target="_blank">View Profile</a>
                                <?php if ($member['status'] === 'active'): ?>
                                    <button class="btn-action btn-cancel" onclick="if(confirm('Suspend this member?')) location.href='?action=suspend&id=<?= $member['id'] ?>'">Suspend</button>
                                <?php else: ?>
                                    <button class="btn-action btn-approve" onclick="if(confirm('Activate this member?')) location.href='?action=activate&id=<?= $member['id'] ?>'">Activate</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
function filterMembers() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const province = document.getElementById('provinceFilter').value;
    const cards = document.querySelectorAll('.member-card');
    
    cards.forEach(card => {
        const name = card.dataset.name;
        const cardStatus = card.dataset.status;
        const cardProvince = card.dataset.province;
        
        const matchSearch = name.includes(search);
        const matchStatus = !status || cardStatus === status;
        const matchProvince = !province || cardProvince === province;
        
        card.style.display = matchSearch && matchStatus && matchProvince ? 'block' : 'none';
    });
}
</script>

</body>
</html>