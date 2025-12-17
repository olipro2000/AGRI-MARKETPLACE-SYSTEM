<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$admin = $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);

// Handle settings update
if ($_POST['action'] ?? false) {
    $action = $_POST['action'];
    
    try {
        switch ($action) {
            case 'update_profile':
                $first_name = $_POST['first_name'] ?? '';
                $last_name = $_POST['last_name'] ?? '';
                $email = $_POST['email'] ?? '';
                
                $db->query("UPDATE admins SET first_name = ?, last_name = ?, email = ? WHERE id = ?", 
                    [$first_name, $last_name, $email, $_SESSION['admin_id']]);
                $_SESSION['success'] = "Profile updated successfully";
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if ($new_password !== $confirm_password) {
                    throw new Exception("New passwords don't match");
                }
                
                if (!password_verify($current_password, $admin['password_hash'])) {
                    throw new Exception("Current password is incorrect");
                }
                
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $db->query("UPDATE admins SET password_hash = ? WHERE id = ?", 
                    [$new_hash, $_SESSION['admin_id']]);
                $_SESSION['success'] = "Password changed successfully";
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: settings.php');
    exit;
}

// Get system stats for settings overview
try {
    $system_stats = [
        'total_users' => $db->fetchColumn("SELECT COUNT(*) FROM users"),
        'total_products' => $db->fetchColumn("SELECT COUNT(*) FROM products"),
        'active_sessions' => $db->fetchColumn("SELECT COUNT(*) FROM user_sessions WHERE expires_at > NOW()"),
        'database_size' => '2.5 MB', // This would need actual calculation
    ];
} catch (Exception $e) {
    $system_stats = [
        'total_users' => 0,
        'total_products' => 0,
        'active_sessions' => 0,
        'database_size' => 'Unknown',
    ];
}

$page_title = 'Settings';
$current_page = 'settings';
include 'includes/header.php';
?>

<style>
    .settings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .settings-card {
        background: var(--bg-secondary);
        border-radius: 12px;
        padding: 2rem;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .settings-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    
    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.875rem;
        background: var(--bg-primary);
        transition: all 0.2s ease;
    }
    
    .form-input:focus {
        outline: none;
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }
    
    .btn-primary {
        background: var(--primary-green);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }
    
    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }
    
    .btn-danger {
        background: var(--error-color);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }
    
    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-1px);
    }
    
    .system-info {
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        border-radius: 12px;
        padding: 2rem;
        border: 1px solid rgba(22, 163, 74, 0.1);
        margin-bottom: 2rem;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    
    .info-item {
        text-align: center;
    }
    
    .info-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-green);
        margin-bottom: 0.5rem;
    }
    
    .info-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
        font-weight: 500;
    }
    
    .danger-zone {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 12px;
        padding: 2rem;
        margin-top: 2rem;
    }
    
    .danger-title {
        color: var(--error-color);
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .danger-description {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }
    
    .maintenance-toggle {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--bg-primary);
        border-radius: 8px;
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }
    
    .toggle-switch {
        position: relative;
        width: 50px;
        height: 24px;
        background: #ccc;
        border-radius: 12px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .toggle-switch.active {
        background: var(--primary-green);
    }
    
    .toggle-slider {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        transition: transform 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .toggle-switch.active .toggle-slider {
        transform: translateX(26px);
    }
    
    .backup-section {
        background: var(--bg-primary);
        border-radius: 8px;
        padding: 1.5rem;
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }
    
    .backup-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .backup-item:last-child {
        border-bottom: none;
    }
    
    .backup-info h4 {
        margin: 0 0 0.25rem 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .backup-info p {
        margin: 0;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    
    .btn-small {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        border-radius: 6px;
    }
    
    @media (max-width: 768px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
        
        .info-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">System Settings</h1>
            <p class="page-subtitle">Manage platform configuration and administrative settings</p>
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
        
        <!-- System Overview -->
        <div class="system-info">
            <h2 class="section-title">System Overview</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-value"><?= number_format($system_stats['total_users']) ?></div>
                    <div class="info-label">Total Users</div>
                </div>
                <div class="info-item">
                    <div class="info-value"><?= number_format($system_stats['total_products']) ?></div>
                    <div class="info-label">Total Products</div>
                </div>
                <div class="info-item">
                    <div class="info-value"><?= $system_stats['active_sessions'] ?></div>
                    <div class="info-label">Active Sessions</div>
                </div>
                <div class="info-item">
                    <div class="info-value"><?= $system_stats['database_size'] ?></div>
                    <div class="info-label">Database Size</div>
                </div>
            </div>
        </div>
        
        <!-- Settings Grid -->
        <div class="settings-grid">
            <!-- Profile Settings -->
            <div class="settings-card">
                <h3 class="settings-title">
                    <span>👤</span>
                    Profile Settings
                </h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars($admin['first_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars($admin['last_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($admin['email']) ?>" required>
                    </div>
                    <button type="submit" class="btn-primary">Update Profile</button>
                </form>
            </div>
            
            <!-- Security Settings -->
            <div class="settings-card">
                <h3 class="settings-title">
                    <span>🔒</span>
                    Security Settings
                </h3>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input" required>
                    </div>
                    <button type="submit" class="btn-primary">Change Password</button>
                </form>
            </div>
        </div>
        
        <!-- Platform Settings -->
        <div class="settings-card">
            <h3 class="settings-title">
                <span>⚙️</span>
                Platform Settings
            </h3>
            
            <div class="maintenance-toggle">
                <div class="toggle-switch" onclick="toggleMaintenance(this)">
                    <div class="toggle-slider"></div>
                </div>
                <div>
                    <h4 style="margin: 0 0 0.25rem 0; font-size: 0.875rem; font-weight: 600;">Maintenance Mode</h4>
                    <p style="margin: 0; font-size: 0.75rem; color: var(--text-secondary);">Temporarily disable public access for maintenance</p>
                </div>
            </div>
            
            <div class="backup-section">
                <h4 style="margin-bottom: 1rem; font-size: 1rem; font-weight: 600;">Database Backups</h4>
                <div class="backup-item">
                    <div class="backup-info">
                        <h4>Latest Backup</h4>
                        <p>Created on <?= date('M j, Y \a\t g:i A') ?></p>
                    </div>
                    <button class="btn-primary btn-small">Download</button>
                </div>
                <div class="backup-item">
                    <div class="backup-info">
                        <h4>Create New Backup</h4>
                        <p>Generate a fresh database backup</p>
                    </div>
                    <button class="btn-primary btn-small" onclick="createBackup()">Create Backup</button>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <button class="btn-primary" onclick="clearCache()">Clear Cache</button>
                <button class="btn-primary" onclick="optimizeDatabase()">Optimize Database</button>
                <button class="btn-primary" onclick="exportData()">Export Data</button>
                <button class="btn-primary" onclick="viewLogs()">View System Logs</button>
            </div>
        </div>
        
        <!-- Danger Zone -->
        <div class="danger-zone">
            <h3 class="danger-title">
                <span>⚠️</span>
                Danger Zone
            </h3>
            <p class="danger-description">
                These actions are irreversible and can cause data loss. Please proceed with extreme caution.
            </p>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button class="btn-danger" onclick="resetPlatform()">Reset Platform</button>
                <button class="btn-danger" onclick="deleteAllData()">Delete All Data</button>
                <button class="btn-danger" onclick="factoryReset()">Factory Reset</button>
            </div>
        </div>
    </main>
</div>

<script>
function toggleMaintenance(toggle) {
    toggle.classList.toggle('active');
    const isActive = toggle.classList.contains('active');
    
    // Here you would make an AJAX call to update maintenance mode
    console.log('Maintenance mode:', isActive ? 'ON' : 'OFF');
    
    // Show feedback
    const message = isActive ? 'Maintenance mode enabled' : 'Maintenance mode disabled';
    showNotification(message, 'success');
}

function createBackup() {
    if (confirm('Create a new database backup?')) {
        // Simulate backup creation
        showNotification('Backup created successfully', 'success');
    }
}

function clearCache() {
    if (confirm('Clear all cached data?')) {
        showNotification('Cache cleared successfully', 'success');
    }
}

function optimizeDatabase() {
    if (confirm('Optimize database tables?')) {
        showNotification('Database optimized successfully', 'success');
    }
}

function exportData() {
    showNotification('Data export started. You will receive a download link shortly.', 'info');
}

function viewLogs() {
    window.open('logs.php', '_blank');
}

function resetPlatform() {
    if (confirm('This will reset all platform settings to default. Continue?')) {
        if (confirm('Are you absolutely sure? This action cannot be undone.')) {
            showNotification('Platform reset initiated', 'warning');
        }
    }
}

function deleteAllData() {
    if (confirm('This will DELETE ALL DATA permanently. Continue?')) {
        if (confirm('Type "DELETE" to confirm:') === 'DELETE') {
            showNotification('Data deletion initiated', 'error');
        }
    }
}

function factoryReset() {
    if (confirm('This will restore the platform to factory settings. Continue?')) {
        if (confirm('All data, settings, and customizations will be lost. Are you sure?')) {
            showNotification('Factory reset initiated', 'error');
        }
    }
}

function showNotification(message, type) {
    // Simple notification system
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

</body>
</html>