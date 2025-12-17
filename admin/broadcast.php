<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$admin = $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);

// Handle broadcast
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $userType = $_POST['user_type'] ?? 'all';
    
    if ($title && $message) {
        $sql = "SELECT id FROM users WHERE status = 'active'";
        $params = [];
        
        if ($userType !== 'all') {
            $sql .= " AND user_type = ?";
            $params[] = $userType;
        }
        
        $users = $db->query($sql, $params)->fetchAll();
        
        foreach ($users as $user) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'info')",
                [$user['id'], $title, $message]
            );
        }
        
        $_SESSION['success'] = "Message sent to " . count($users) . " users";
        header('Location: broadcast.php');
        exit;
    }
}

$stats = [
    'total_users' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'active'"),
    'farmers' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE user_type = 'farmer' AND status = 'active'"),
    'buyers' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE user_type = 'buyer' AND status = 'active'"),
    'cooperatives' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE user_type = 'cooperative_member' AND status = 'active'"),
];

$page_title = 'Broadcast Message';
$current_page = 'broadcast';
include 'includes/header.php';
?>

<style>
    .broadcast-form { background: white; border-radius: 12px; padding: 2rem; max-width: 800px; margin: 0 auto; }
    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem; }
    .form-input, .form-select, .form-textarea { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.875rem; }
    .form-textarea { min-height: 150px; resize: vertical; font-family: inherit; }
    .btn-send { background: var(--primary-green); color: white; border: none; padding: 1rem 2rem; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; }
    .btn-send:hover { background: var(--primary-dark); }
    .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
    .alert-success { background: rgba(16,185,129,0.1); color: #059669; border: 1px solid rgba(16,185,129,0.3); }
    .recipient-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .recipient-card { background: rgba(22,163,74,0.05); padding: 1rem; border-radius: 8px; text-align: center; border: 1px solid var(--border-color); }
    .recipient-count { font-size: 1.5rem; font-weight: 700; color: var(--primary-green); }
    .recipient-label { font-size: 0.875rem; color: var(--text-secondary); }
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">📢 Broadcast Message</h1>
            <p class="page-subtitle">Send notifications to all users or specific groups</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <div class="recipient-stats">
            <div class="recipient-card">
                <div class="recipient-count"><?= number_format($stats['total_users']) ?></div>
                <div class="recipient-label">Total Users</div>
            </div>
            <div class="recipient-card">
                <div class="recipient-count"><?= number_format($stats['farmers']) ?></div>
                <div class="recipient-label">Farmers</div>
            </div>
            <div class="recipient-card">
                <div class="recipient-count"><?= number_format($stats['buyers']) ?></div>
                <div class="recipient-label">Buyers</div>
            </div>
            <div class="recipient-card">
                <div class="recipient-count"><?= number_format($stats['cooperatives']) ?></div>
                <div class="recipient-label">Cooperatives</div>
            </div>
        </div>
        
        <div class="broadcast-form">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Recipients</label>
                    <select name="user_type" class="form-select" required>
                        <option value="all">All Users</option>
                        <option value="farmer">Farmers Only</option>
                        <option value="buyer">Buyers Only</option>
                        <option value="cooperative_member">Cooperative Members Only</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Message Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g., Important Update" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Message Content</label>
                    <textarea name="message" class="form-textarea" placeholder="Type your message here..." required></textarea>
                </div>
                
                <button type="submit" class="btn-send">📤 Send Message</button>
            </form>
        </div>
    </main>
</div>

</body>
</html>
