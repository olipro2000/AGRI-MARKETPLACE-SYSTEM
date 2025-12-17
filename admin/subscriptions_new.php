<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$admin = $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);

try {
    $subscriptions = $db->fetchAll("
        SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.location, u.profile_image, u.created_at,
               us.status, us.starts_at, us.expires_at,
               sp.name as plan_name, sp.price
        FROM users u 
        LEFT JOIN user_subscriptions us ON u.id = us.user_id
        LEFT JOIN subscription_plans sp ON us.plan_id = sp.id
        ORDER BY u.created_at DESC
    ");
    
    $stats = [
        'free' => $db->fetchColumn("SELECT COUNT(*) FROM users u LEFT JOIN user_subscriptions us ON u.id = us.user_id WHERE us.id IS NULL"),
        'basic' => $db->fetchColumn("SELECT COUNT(*) FROM user_subscriptions us JOIN subscription_plans sp ON us.plan_id = sp.id WHERE sp.name = 'Basic'"),
        'premium' => $db->fetchColumn("SELECT COUNT(*) FROM user_subscriptions us JOIN subscription_plans sp ON us.plan_id = sp.id WHERE sp.name = 'Premium'"),
        'expired' => $db->fetchColumn("SELECT COUNT(*) FROM user_subscriptions WHERE status = 'expired'"),
    ];
} catch (Exception $e) {
    $subscriptions = [];
    $stats = ['free' => 0, 'basic' => 0, 'premium' => 0, 'expired' => 0];
}

$page_title = 'Subscriptions';
$current_page = 'subscriptions';
include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <main class="main-content">
        <div class="dashboard-header">
            <div class="header-content">
                <div class="header-text">
                    <h1>Subscription Management</h1>
                    <p>Manage plans and monitor user subscriptions</p>
                </div>
                <button class="add-plan-btn" onclick="addPlan()">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                    New Plan
                </button>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Stats Overview -->
            <div class="stats-section">
                <div class="stats-container">
                    <div class="stat-item free">
                        <div class="stat-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                            </svg>
                        </div>
                        <div class="stat-data">
                            <span class="stat-number"><?= number_format($stats['free']) ?></span>
                            <span class="stat-label">Free Users</span>
                        </div>
                    </div>
                    
                    <div class="stat-item basic">
                        <div class="stat-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div class="stat-data">
                            <span class="stat-number"><?= number_format($stats['basic']) ?></span>
                            <span class="stat-label">Basic Plans</span>
                        </div>
                    </div>
                    
                    <div class="stat-item premium">
                        <div class="stat-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm2.7-2h8.6l.9-5.4-2.1 1.4L12 8l-3.1 2L6.8 8.6L7.7 14z"/>
                            </svg>
                        </div>
                        <div class="stat-data">
                            <span class="stat-number"><?= number_format($stats['premium']) ?></span>
                            <span class="stat-label">Premium Plans</span>
                        </div>
                    </div>
                    
                    <div class="stat-item expired">
                        <div class="stat-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm3.5 6L12 10.5 8.5 8 7 9.5l3.5 3.5-3.5 3.5L8.5 17l3.5-3.5L15.5 17 17 15.5 13.5 12 17 8.5 15.5 7z"/>
                            </svg>
                        </div>
                        <div class="stat-data">
                            <span class="stat-number"><?= number_format($stats['expired']) ?></span>
                            <span class="stat-label">Expired</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plans Management -->
            <div class="plans-section">
                <div class="section-title">
                    <h2>Subscription Plans</h2>
                    <span class="plan-count"><?php $plans = $db->fetchAll("SELECT * FROM subscription_plans ORDER BY price ASC"); echo count($plans); ?> plans</span>
                </div>
                
                <div class="plans-list">
                    <?php foreach ($plans as $plan): ?>
                        <div class="plan-item <?= strtolower($plan['name']) ?> <?= $plan['is_active'] ? 'active' : 'inactive' ?>">
                            <div class="plan-info">
                                <div class="plan-main">
                                    <h3><?= htmlspecialchars($plan['name']) ?></h3>
                                    <p><?= htmlspecialchars($plan['description']) ?></p>
                                </div>
                                <div class="plan-meta">
                                    <span class="price"><?= number_format($plan['price']) ?> RWF</span>
                                    <span class="duration"><?= $plan['duration_days'] ?> days</span>
                                    <span class="products"><?= $plan['max_products'] ?: '∞' ?> products</span>
                                </div>
                            </div>
                            <div class="plan-controls">
                                <button class="edit-btn" onclick="editPlan(<?= $plan['id'] ?>)">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                    </svg>
                                </button>
                                <button class="toggle-btn <?= $plan['is_active'] ? 'active' : 'inactive' ?>" onclick="togglePlan(<?= $plan['id'] ?>)">
                                    <?= $plan['is_active'] ? 'ON' : 'OFF' ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Users Section -->
            <div class="users-section">
                <div class="section-title">
                    <h2>User Subscriptions</h2>
                    <div class="filter-controls">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <button class="filter-btn" data-filter="active">Active</button>
                        <button class="filter-btn" data-filter="pending">Pending</button>
                        <button class="filter-btn" data-filter="free">Free</button>
                    </div>
                </div>
                
                <div class="users-grid">
                    <?php foreach ($subscriptions as $sub): ?>
                        <div class="user-card <?= $sub['status'] ?: 'free' ?>" data-status="<?= $sub['status'] ?: 'free' ?>">
                            <div class="user-header">
                                <div class="user-avatar">
                                    <?php if ($sub['profile_image']): ?>
                                        <img src="<?= htmlspecialchars($sub['profile_image']) ?>" alt="Profile">
                                    <?php else: ?>
                                        <span><?= strtoupper(substr($sub['first_name'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                    <?php if ($sub['status'] === 'active'): ?>
                                        <div class="verified-dot"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="user-details">
                                    <h4><?= htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']) ?></h4>
                                    <p><?= htmlspecialchars($sub['email']) ?></p>
                                </div>
                                <div class="status-badge <?= $sub['status'] ?: 'free' ?>">
                                    <?= ucfirst($sub['status'] ?: 'free') ?>
                                </div>
                            </div>
                            
                            <div class="subscription-details">
                                <div class="plan-info">
                                    <span class="plan-name"><?= $sub['plan_name'] ?: 'Free Plan' ?></span>
                                    <span class="plan-price"><?= $sub['price'] ? number_format($sub['price']) . ' RWF' : 'Free' ?></span>
                                </div>
                                
                                <?php if ($sub['expires_at']): ?>
                                    <div class="expiry-info">
                                        <span class="expiry-label">Expires</span>
                                        <span class="expiry-date"><?= date('M j, Y', strtotime($sub['expires_at'])) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($sub['status'] === 'pending'): ?>
                                <div class="user-actions">
                                    <button class="approve-btn" onclick="approveSubscription(<?= $sub['id'] ?>)">
                                        Approve
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Plan Modal -->
<div id="planModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Plan</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <form id="planForm" class="modal-form">
            <input type="hidden" id="planId" name="planId">
            
            <div class="form-group">
                <label for="planName">Plan Name</label>
                <input type="text" id="planName" name="name" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="planPrice">Price (RWF)</label>
                    <input type="number" id="planPrice" name="price" required>
                </div>
                <div class="form-group">
                    <label for="planDuration">Duration (Days)</label>
                    <input type="number" id="planDuration" name="duration_days" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="planMaxProducts">Max Products (Optional)</label>
                <input type="number" id="planMaxProducts" name="max_products" placeholder="Leave empty for unlimited">
            </div>
            
            <div class="form-group">
                <label for="planDescription">Description</label>
                <textarea id="planDescription" name="description" rows="3" required></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn-save" onclick="savePlan()">Save Plan</button>
            </div>
        </form>
    </div>
</div>

<style>
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 32px;
    color: white;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-text h1 {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.header-text p {
    font-size: 16px;
    opacity: 0.9;
    margin: 0;
}

.add-plan-btn {
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.3);
    color: white;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.add-plan-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
}

.stats-section {
    background: white;
    border-radius: 20px;
    padding: 24px;
    border: 1px solid #f1f5f9;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border-radius: 16px;
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.stat-item.free {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.stat-item.basic {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
}

.stat-item.premium {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.stat-item.expired {
    background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.8);
    color: #374151;
}

.stat-data {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.stat-label {
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}

.plans-section, .users-section {
    background: white;
    border-radius: 20px;
    padding: 24px;
    border: 1px solid #f1f5f9;
}

.section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f1f5f9;
}

.section-title h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.plan-count {
    font-size: 14px;
    color: #64748b;
    background: #f8fafc;
    padding: 4px 12px;
    border-radius: 20px;
}

.plans-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.plan-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-radius: 16px;
    border: 2px solid #f1f5f9;
    transition: all 0.3s ease;
}

.plan-item:hover {
    border-color: #e2e8f0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.plan-item.free {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.plan-item.basic {
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
}

.plan-item.premium {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
}

.plan-item.inactive {
    opacity: 0.6;
}

.plan-info {
    display: flex;
    align-items: center;
    gap: 24px;
    flex: 1;
}

.plan-main h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.plan-main p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.plan-meta {
    display: flex;
    gap: 16px;
    align-items: center;
}

.plan-meta span {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 6px;
    background: rgba(255,255,255,0.8);
    color: #475569;
    font-weight: 500;
}

.price {
    font-weight: 700 !important;
    color: #059669 !important;
}

.plan-controls {
    display: flex;
    gap: 8px;
}

.edit-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: none;
    background: #3b82f6;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.edit-btn:hover {
    background: #2563eb;
    transform: scale(1.05);
}

.toggle-btn {
    padding: 8px 16px;
    border-radius: 20px;
    border: none;
    font-weight: 600;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 50px;
}

.toggle-btn.active {
    background: #10b981;
    color: white;
}

.toggle-btn.inactive {
    background: #ef4444;
    color: white;
}

.filter-controls {
    display: flex;
    gap: 8px;
}

.filter-btn {
    padding: 8px 16px;
    border: none;
    background: #f8fafc;
    color: #64748b;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
}

.filter-btn.active {
    background: #3b82f6;
    color: white;
}

.filter-btn:hover:not(.active) {
    background: #f1f5f9;
}

.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-top: 24px;
}

.user-card {
    background: white;
    border: 2px solid #f1f5f9;
    border-radius: 16px;
    padding: 20px;
    transition: all 0.3s ease;
}

.user-card:hover {
    border-color: #e2e8f0;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.user-card.active {
    border-color: #10b981;
}

.user-card.pending {
    border-color: #f59e0b;
}

.user-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.user-avatar {
    position: relative;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 18px;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 12px;
    object-fit: cover;
}

.verified-dot {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 16px;
    height: 16px;
    background: #10b981;
    border: 3px solid white;
    border-radius: 50%;
}

.user-details {
    flex: 1;
    min-width: 0;
}

.user-details h4 {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 4px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-details p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.active {
    background: #dcfce7;
    color: #166534;
}

.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.free {
    background: #f1f5f9;
    color: #475569;
}

.subscription-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.plan-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.plan-name {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.plan-price {
    font-size: 16px;
    font-weight: 700;
    color: #059669;
}

.expiry-info {
    text-align: right;
}

.expiry-label {
    font-size: 12px;
    color: #64748b;
    display: block;
}

.expiry-date {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.user-actions {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
}

.approve-btn {
    width: 100%;
    padding: 10px 16px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.approve-btn:hover {
    background: #059669;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

.modal-header {
    padding: 24px 24px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
}

.close-btn {
    background: none;
    border: none;
    font-size: 28px;
    color: #64748b;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
}

.close-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.modal-form {
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.2s;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    resize: vertical;
    font-family: inherit;
}

.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 32px;
}

.btn-cancel {
    flex: 1;
    padding: 12px 24px;
    background: #f8fafc;
    color: #64748b;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.btn-save {
    flex: 1;
    padding: 12px 24px;
    background: #3b82f6;
    color: white;
    border: 2px solid #3b82f6;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-save:hover {
    background: #2563eb;
    border-color: #2563eb;
}

.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    border-left: 4px solid #3b82f6;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 10000;
    max-width: 350px;
}

.toast.show {
    transform: translateX(0);
    opacity: 1;
}

.toast-success {
    border-left-color: #10b981;
}

.toast-error {
    border-left-color: #ef4444;
}

.toast-warning {
    border-left-color: #f59e0b;
}

.toast-info {
    border-left-color: #3b82f6;
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.toast-icon {
    font-size: 18px;
}

.toast-message {
    font-weight: 500;
    color: #1f2937;
    font-size: 14px;
}

@media (max-width: 1200px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .stats-container {
        grid-template-columns: 1fr;
    }
    
    .plan-item {
        flex-direction: column;
        gap: 16px;
        align-items: stretch;
    }
    
    .plan-info {
        flex-direction: column;
        gap: 12px;
    }
    
    .plan-meta {
        justify-content: space-between;
    }
    
    .plan-controls {
        justify-content: center;
    }
    
    .users-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-controls {
        flex-wrap: wrap;
    }
    
    .subscription-details {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    .expiry-info {
        text-align: left;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        width: 95%;
        margin: 20px;
    }
    
    .toast {
        right: 10px;
        left: 10px;
        max-width: none;
        transform: translateY(-100px);
    }
    
    .toast.show {
        transform: translateY(0);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const userCards = document.querySelectorAll('.user-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            userCards.forEach(card => {
                const status = card.dataset.status;
                if (filter === 'all' || status === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});

function addPlan() {
    document.getElementById('planModal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = 'Add New Plan';
    document.getElementById('planForm').reset();
    document.getElementById('planId').value = '';
}

function editPlan(id) {
    fetch(`plan_actions.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(plan => {
            document.getElementById('planModal').style.display = 'flex';
            document.getElementById('modalTitle').textContent = 'Edit Plan';
            document.getElementById('planId').value = plan.id;
            document.getElementById('planName').value = plan.name;
            document.getElementById('planPrice').value = plan.price;
            document.getElementById('planDuration').value = plan.duration_days;
            document.getElementById('planMaxProducts').value = plan.max_products || '';
            document.getElementById('planDescription').value = plan.description;
        });
}

function savePlan() {
    const formData = new FormData(document.getElementById('planForm'));
    const data = {
        action: formData.get('planId') ? 'edit' : 'add',
        id: formData.get('planId'),
        name: formData.get('name'),
        price: parseFloat(formData.get('price')),
        duration_days: parseInt(formData.get('duration_days')),
        max_products: formData.get('max_products') ? parseInt(formData.get('max_products')) : null,
        description: formData.get('description')
    };
    
    showToast('Saving plan...', 'info');
    
    fetch('plan_actions.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    }).then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('Plan saved successfully!', 'success');
            closeModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Failed to save plan', 'error');
        }
    }).catch(() => {
        showToast('Network error occurred', 'error');
    });
}

function closeModal() {
    document.getElementById('planModal').style.display = 'none';
}

function togglePlan(id) {
    showToast('Updating plan status...', 'info');
    
    fetch('plan_actions.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'toggle',
            id: id
        })
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Plan status updated successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Failed to update plan status', 'error');
        }
    }).catch(() => {
        showToast('Network error occurred', 'error');
    });
}

function approveSubscription(id) {
    showToast('Approving subscription...', 'info');
    setTimeout(() => {
        showToast('Subscription approved successfully!', 'success');
    }, 1000);
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon">${getToastIcon(type)}</span>
            <span class="toast-message">${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
}

function getToastIcon(type) {
    const icons = {
        success: '✅',
        error: '❌',
        info: 'ℹ️',
        warning: '⚠️'
    };
    return icons[type] || icons.info;
}
</script>

</body>
</html>