<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Get current user if logged in
$current_user = null;
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $db = new Database();
    $current_user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    
    // Get cart count
    try {
        $cart_count = $db->fetchColumn("SELECT COUNT(*) FROM cart WHERE user_id = ?", [$_SESSION['user_id']]) ?: 0;
    } catch (Exception $e) {
        $cart_count = 0;
    }
}
?>

<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="/curuzamuhinzi/feed.php">
                <span class="logo-icon">🌱</span>
                <span class="logo-text">Curuza Muhinzi</span>
            </a>
        </div>
        
        <form action="/curuzamuhinzi/search.php" method="GET" class="search-field">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2"/>
            </svg>
            <input type="text" name="q" placeholder="Search products..." id="quickSearch" autocomplete="off">
        </form>
        
        <nav class="nav-menu">
            <a href="/curuzamuhinzi/feed.php" class="nav-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2"/>
                    <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2"/>
                </svg>
                <span>Home</span>
            </a>
            <a href="/curuzamuhinzi/crops.php" class="nav-link">
                <span style="font-size: 18px;">🌽</span>
                <span>Crops</span>
            </a>
            <a href="/curuzamuhinzi/livestock.php" class="nav-link">
                <span style="font-size: 18px;">🐄</span>
                <span>Livestock</span>
            </a>
            <a href="/curuzamuhinzi/equipment.php" class="nav-link">
                <span style="font-size: 18px;">🚜</span>
                <span>Equipment</span>
            </a>
        </nav>
        
        <div class="user-section">
            <?php if ($current_user): ?>
                <div class="header-actions">
                    <!-- Wishlist -->
                    <a href="/curuzamuhinzi/wishlist.php" class="notification-bell" style="text-decoration: none;" title="Wishlist">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </a>
                    
                    <!-- Messages -->
                    <a href="/curuzamuhinzi/messages.php" class="notification-bell" style="text-decoration: none;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <?php
                        try {
                            $unreadMessages = $db->fetchColumn("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0", [$_SESSION['user_id']]) ?: 0;
                            if ($unreadMessages > 0):
                        ?>
                            <span class="notification-badge"><?= $unreadMessages ?></span>
                        <?php 
                            endif;
                        } catch (Exception $e) {
                            // Messages table not created yet
                        }
                        ?>
                    </a>
                    
                    <!-- Notification Bell -->
                    <div class="notification-wrapper">
                        <div class="notification-bell" onclick="toggleNotifications()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span class="notification-badge hidden" id="notificationBadge">0</span>
                        </div>
                        
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h4>Notifications</h4>
                                <button class="mark-all-read" onclick="markAllAsRead()">Mark all read</button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <div class="notification-empty">Loading notifications...</div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (in_array($current_user['user_type'], ['farmer', 'cooperative', 'supplier'])): ?>
                        <?php
                        // Check subscription status with notifications
                        $canAddProduct = false;
                        $subscriptionStatus = 'none';
                        $subscriptionMessage = '';
                        
                        try {
                            // Check for active subscription
                            $activeSubscription = $db->fetch(
                                "SELECT us.*, sp.name as plan_name 
                                 FROM user_subscriptions us 
                                 LEFT JOIN subscription_plans sp ON us.plan_id = sp.id 
                                 WHERE us.user_id = ? AND us.status = 'active' AND us.expires_at > NOW() 
                                 ORDER BY us.created_at DESC LIMIT 1", 
                                [$current_user['id']]
                            );
                            
                            if ($activeSubscription) {
                                $canAddProduct = true;
                                $subscriptionStatus = 'active';
                            } else {
                                // Check for recently approved subscription
                                $recentApproval = $db->fetch(
                                    "SELECT us.*, sp.name as plan_name 
                                     FROM user_subscriptions us 
                                     LEFT JOIN subscription_plans sp ON us.plan_id = sp.id 
                                     WHERE us.user_id = ? AND us.status = 'active' 
                                     AND us.updated_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                                     ORDER BY us.updated_at DESC LIMIT 1", 
                                    [$current_user['id']]
                                );
                                
                                if ($recentApproval) {
                                    $subscriptionStatus = 'approved';
                                    $subscriptionMessage = "Congratulations! Your {$recentApproval['plan_name']} subscription has been approved. You can now add products to the marketplace. Thank you for choosing Curuza Muhinzi!";
                                } else {
                                    // Check for recently rejected subscription
                                    $recentRejection = $db->fetch(
                                        "SELECT us.*, sp.name as plan_name 
                                         FROM user_subscriptions us 
                                         LEFT JOIN subscription_plans sp ON us.plan_id = sp.id 
                                         WHERE us.user_id = ? AND us.status = 'cancelled' 
                                         AND us.updated_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                                         ORDER BY us.updated_at DESC LIMIT 1", 
                                        [$current_user['id']]
                                    );
                                    
                                    if ($recentRejection) {
                                        $subscriptionStatus = 'rejected';
                                        $subscriptionMessage = "Your subscription payment was not approved. Please check your payment details or contact Curuza Muhinzi support for assistance. You can resubmit your payment with correct information.";
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $canAddProduct = true; // Allow if subscription system not set up
                        }
                        ?>
                        <a href="<?= $canAddProduct ? '/curuzamuhinzi/add-product.php' : '#' ?>" 
                           class="add-product-link <?= !$canAddProduct ? 'subscription-required' : '' ?>" 
                           <?= !$canAddProduct ? 'onclick="showSubscriptionModal(); return false;"' : '' ?>>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="profile-wrapper">
                    <a href="/curuzamuhinzi/dashboard/profile.php" class="profile-trigger mobile-direct-link">
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($current_user['first_name']) ?></span>
                            <span class="user-role"><?= ucfirst(str_replace('_', ' ', $current_user['user_type'])) ?></span>
                        </div>
                        <div class="avatar">
                            <?php if ($current_user['profile_picture'] && file_exists(__DIR__ . '/../uploads/profiles/' . $current_user['profile_picture'])): ?>
                                <img src="/curuzamuhinzi/uploads/profiles/<?= htmlspecialchars($current_user['profile_picture']) ?>" alt="Profile">
                            <?php else: ?>
                                <span><?= strtoupper(substr($current_user['first_name'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    
                    <div class="profile-trigger desktop-dropdown">
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($current_user['first_name']) ?></span>
                            <span class="user-role"><?= ucfirst(str_replace('_', ' ', $current_user['user_type'])) ?></span>
                        </div>
                        <div class="avatar">
                            <?php if ($current_user['profile_picture'] && file_exists(__DIR__ . '/../uploads/profiles/' . $current_user['profile_picture'])): ?>
                                <img src="/curuzamuhinzi/uploads/profiles/<?= htmlspecialchars($current_user['profile_picture']) ?>" alt="Profile">
                            <?php else: ?>
                                <span><?= strtoupper(substr($current_user['first_name'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="profile-dropdown">
                    <div class="dropdown-header">
                        <div class="user-details">
                            <div class="full-name"><?= htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']) ?></div>
                            <div class="user-type"><?= ucfirst(str_replace('_', ' ', $current_user['user_type'])) ?></div>
                        </div>
                    </div>
                    <div class="dropdown-menu">
                        <a href="/curuzamuhinzi/dashboard/profile.php" class="dropdown-item">
                            <span>👤</span> My Profile
                        </a>
                        <?php if ($current_user['user_type'] !== 'buyer'): ?>
                            <a href="/curuzamuhinzi/dashboard/index.php" class="dropdown-item">
                                <span>📊</span> Dashboard
                            </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="/curuzamuhinzi/auth/login.php?logout=1" class="dropdown-item">
                            <span>🚪</span> Logout
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <div class="auth-buttons">
                    <a href="/curuzamuhinzi/auth/login.php" class="btn-login">Login</a>
                    <a href="/curuzamuhinzi/auth/register.php" class="btn-register">Join</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Subscription Status Notifications -->
<?php if ($current_user && !empty($subscriptionMessage)): ?>
    <div id="subscriptionNotification" class="subscription-notification <?= $subscriptionStatus ?>">
        <div class="notification-content">
            <div class="notification-icon">
                <?php if ($subscriptionStatus === 'approved'): ?>
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>
                <?php else: ?>
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                <?php endif; ?>
            </div>
            <div class="notification-message">
                <?= htmlspecialchars($subscriptionMessage) ?>
            </div>
            <button class="notification-close" onclick="closeNotification()">&times;</button>
        </div>
    </div>
<?php endif; ?>



<style>
/* Header Styles */
.main-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    z-index: 1000;
    height: 64px;
}

.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    height: 100%;
}

/* Logo */
.logo {
    flex-shrink: 0;
}

.logo a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: #27ae60;
    font-weight: 700;
    font-size: 1.2rem;
}

.logo-icon {
    font-size: 1.5rem;
}

/* Search Field */
.search-field {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border-radius: 25px;
    padding: 0.5rem 1rem;
    flex: 1;
    max-width: 400px;
    margin: 0 2rem;
    border: 1px solid #e9ecef;
}

.search-field svg {
    color: #6c757d;
    margin-right: 0.5rem;
}

.search-field input {
    border: none;
    background: none;
    outline: none;
    width: 100%;
    font-size: 14px;
    color: #495057;
}

.search-field input::placeholder {
    color: #6c757d;
}

/* Navigation Menu */
.nav-menu {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-right: 1rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem;
    text-decoration: none;
    color: #6c757d;
    border-radius: 6px;
    transition: all 0.2s ease;
    font-weight: 500;
    white-space: nowrap;
}

.nav-link:hover {
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
}

.nav-link span {
    font-size: 13px;
}

/* User Section */
.user-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.add-product-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
    position: relative;
    overflow: hidden;
}

.add-product-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.6s;
}

.add-product-link:hover::before {
    left: 100%;
}

.add-product-link:hover {
    background: linear-gradient(135deg, #219a52 0%, #27ae60 100%);
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 8px 20px rgba(39, 174, 96, 0.4);
}

.add-product-link:active {
    transform: translateY(0) scale(0.98);
    transition: all 0.1s ease;
}

/* Profile Wrapper */
.profile-wrapper {
    position: relative;
}

.profile-trigger {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.profile-trigger:hover {
    background: rgba(0, 0, 0, 0.05);
}

.profile-wrapper:hover .profile-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.mobile-direct-link {
    display: none;
    text-decoration: none;
    color: inherit;
}

.desktop-dropdown {
    display: flex;
}

@media (max-width: 768px) {
    .mobile-direct-link {
        display: flex;
    }
    
    .desktop-dropdown {
        display: none;
    }
    
    .profile-dropdown {
        display: none;
    }
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.user-name {
    font-weight: 600;
    font-size: 14px;
    color: #212529;
}

.user-role {
    font-size: 12px;
    color: #6c757d;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #27ae60;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 14px;
    overflow: hidden;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Auth Buttons */
.auth-buttons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-login, .btn-register {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
}

.btn-login {
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.btn-login:hover {
    background: #f8f9fa;
}

.btn-register {
    background: #27ae60;
    color: white;
    border: 1px solid #27ae60;
}

.btn-register:hover {
    background: #219a52;
}

/* Profile Dropdown */
.profile-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    z-index: 1001;
    border: 1px solid #e9ecef;
    margin-top: 0.5rem;
}

.dropdown-header {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.full-name {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}

.user-type {
    font-size: 12px;
    color: #6c757d;
    text-transform: capitalize;
}

.dropdown-menu {
    padding: 0.5rem 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: #495057;
    transition: all 0.2s ease;
    font-size: 14px;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: #27ae60;
}

.dropdown-divider {
    height: 1px;
    background: #e9ecef;
    margin: 0.5rem 0;
}

/* Mobile Responsive */


@media (max-width: 768px) {
    .header-container {
        padding: 0 0.5rem;
    }
    
    .logo-text {
        display: none;
    }
    
    .search-field {
        max-width: 200px;
        margin: 0 1rem;
    }
    
    .nav-menu {
        display: none;
    }
    
    .user-section {
        gap: 0.5rem;
    }
    
    .header-actions {
        gap: 0.5rem;
        margin-right: 0.5rem;
    }
    
    .add-product-link {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(39, 174, 96, 0.2);
        flex-shrink: 0;
    }
    
    .add-product-link svg {
        width: 16px;
        height: 16px;
    }
    
    .profile-trigger {
        padding: 0.25rem;
    }
    
    .user-info {
        display: none;
    }
    
    .profile-wrapper {
        display: flex;
    }
    
    .profile-dropdown {
        right: -10px;
        min-width: 180px;
    }
}

@media (max-width: 480px) {
    .search-field {
        max-width: 150px;
        margin: 0 0.5rem;
    }
    
    .search-field input {
        font-size: 12px;
    }
}
</style>

<style>
.search-popup { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; }
.search-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
.search-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 16px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; }
.search-header { padding: 20px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
.search-header h3 { margin: 0; color: #27ae60; font-size: 20px; }
.close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #6c757d; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
.close-btn:hover { background: #f8f9fa; }
.search-body { padding: 20px; }
.search-input-group { display: flex; align-items: center; background: #f8f9fa; border-radius: 12px; padding: 12px 16px; border: 2px solid #e9ecef; }
.search-input-group svg { color: #6c757d; margin-right: 12px; flex-shrink: 0; }
.search-input-group input { border: none; background: none; outline: none; width: 100%; font-size: 16px; color: #495057; }
.search-categories { margin-top: 24px; }
.search-categories h4 { font-size: 14px; color: #6c757d; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
.category-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.category-item { display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: #f8f9fa; border-radius: 10px; text-decoration: none; color: #495057; font-weight: 500; transition: all 0.2s ease; border: 2px solid transparent; }
.category-item:hover { background: #e9ecef; border-color: #27ae60; color: #27ae60; }
.category-item span { font-size: 20px; }
@media (max-width: 768px) {
    .search-content { width: 95%; max-width: none; }
    .category-grid { grid-template-columns: 1fr; }
}
</style>

<!-- Subscription Modal -->
<div id="subscriptionModal" class="subscription-modal">
    <div class="modal-overlay" onclick="closeSubscriptionModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Choose Your Plan</h3>
            <button class="modal-close" onclick="closeSubscriptionModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>You need an active subscription to add products. Choose a plan below:</p>
            <div class="plans-container" id="plansContainer">
                Loading plans...
            </div>
        </div>
    </div>
</div>

<style>
.add-product-link.subscription-required {
    opacity: 0.6;
}

.subscription-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    padding: 24px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #27ae60;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6c757d;
}

.modal-body {
    padding: 24px;
}

.plans-container {
    display: grid;
    gap: 16px;
    margin-top: 20px;
}

.plan-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.plan-card:hover {
    border-color: #27ae60;
    transform: translateY(-2px);
}

.plan-name {
    font-size: 20px;
    font-weight: 600;
    color: #27ae60;
    margin-bottom: 8px;
}

.plan-price {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin-bottom: 12px;
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.plan-features li {
    padding: 4px 0;
    color: #6c757d;
}

.select-plan-btn {
    background: #27ae60;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 16px;
    font-weight: 600;
}

.select-plan-btn:hover {
    background: #219a52;
}

/* Notification Bell */
.notification-bell {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #6c757d;
}

.notification-bell:hover {
    background: #e9ecef;
    color: #27ae60;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

.notification-badge.hidden {
    display: none;
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    width: 350px;
    max-height: 400px;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1001;
    border: 1px solid #e9ecef;
    margin-top: 8px;
}

.notification-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.notification-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h4 {
    margin: 0;
    font-size: 16px;
    color: #212529;
}

.mark-all-read {
    background: none;
    border: none;
    color: #27ae60;
    font-size: 12px;
    cursor: pointer;
    font-weight: 500;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 16px 20px;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: background 0.2s ease;
    position: relative;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread {
    background: #f0f9ff;
    border-left: 3px solid #27ae60;
}

.notification-item.unread::before {
    content: '';
    position: absolute;
    top: 20px;
    right: 20px;
    width: 8px;
    height: 8px;
    background: #27ae60;
    border-radius: 50%;
}

.notification-title {
    font-weight: 600;
    font-size: 14px;
    color: #212529;
    margin-bottom: 4px;
}

.notification-message {
    font-size: 13px;
    color: #6c757d;
    line-height: 1.4;
    margin-bottom: 8px;
}

.notification-time {
    font-size: 11px;
    color: #adb5bd;
}

.notification-empty {
    padding: 40px 20px;
    text-align: center;
    color: #6c757d;
}

@media (max-width: 768px) {
    .notification-dropdown {
        position: fixed;
        top: 70px;
        left: 10px;
        right: 10px;
        width: auto;
        max-width: none;
    }
    
    .notification-bell {
        width: 36px;
        height: 36px;
    }
    
    .notification-item {
        padding: 12px 16px;
    }
    
    .notification-message {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .notification-dropdown {
        top: 65px;
        left: 5px;
        right: 5px;
    }
    
    .notification-header {
        padding: 12px 16px;
    }
    
    .notification-header h4 {
        font-size: 14px;
    }
}

/* Subscription Notifications */
.subscription-notification {
    position: fixed;
    top: 80px;
    left: 50%;
    transform: translateX(-50%);
    max-width: 600px;
    width: 90%;
    z-index: 10001;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    animation: slideDown 0.5s ease-out;
}

.subscription-notification.approved {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border: 1px solid #b8dacc;
    color: #155724;
}

.subscription-notification.rejected {
    background: linear-gradient(135deg, #f8d7da 0%, #f1c2c7 100%);
    border: 1px solid #f1b0b7;
    color: #721c24;
}

.notification-content {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px;
}

.notification-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.subscription-notification.approved .notification-icon {
    background: #28a745;
    color: white;
}

.subscription-notification.rejected .notification-icon {
    background: #dc3545;
    color: white;
}

.notification-message {
    flex: 1;
    font-size: 15px;
    line-height: 1.5;
    font-weight: 500;
}

.notification-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    opacity: 0.7;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.notification-close:hover {
    opacity: 1;
    background: rgba(0,0,0,0.1);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}

@media (max-width: 768px) {
    .subscription-notification {
        top: 70px;
        width: 95%;
        max-width: none;
    }
    
    .notification-content {
        padding: 16px;
        gap: 12px;
    }
    
    .notification-icon {
        width: 32px;
        height: 32px;
    }
    
    .notification-message {
        font-size: 14px;
    }
}
</style>

<script>
function showSubscriptionModal() {
    document.getElementById('subscriptionModal').style.display = 'block';
    loadSubscriptionPlans();
}

function closeSubscriptionModal() {
    document.getElementById('subscriptionModal').style.display = 'none';
}

function loadSubscriptionPlans() {
    fetch('/curuzamuhinzi/api/get-plans.php')
        .then(response => response.json())
        .then(plans => {
            const container = document.getElementById('plansContainer');
            if (plans.length > 0) {
                container.innerHTML = plans.map(plan => `
                    <div class="plan-card" onclick="selectPlan(${plan.id})">
                        <div class="plan-name">${plan.name}</div>
                        <div class="plan-price">${parseInt(plan.price).toLocaleString()} RWF</div>
                        <ul class="plan-features">
                            <li>Duration: ${plan.duration_days} days</li>
                            <li>Max Products: ${plan.max_products || 'Unlimited'}</li>
                        </ul>
                        <button class="select-plan-btn">Choose Plan</button>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p>No subscription plans available.</p>';
            }
        })
        .catch(() => {
            document.getElementById('plansContainer').innerHTML = '<p>Unable to load plans. Please try again.</p>';
        });
}

function selectPlan(planId) {
    window.location.href = `/curuzamuhinzi/subscribe.php?plan=${planId}`;
}

function closeNotification() {
    const notification = document.getElementById('subscriptionNotification');
    if (notification) {
        notification.style.animation = 'slideUp 0.3s ease-out forwards';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

// Auto-hide notification after 10 seconds
if (document.getElementById('subscriptionNotification')) {
    setTimeout(() => {
        closeNotification();
    }, 10000);
}

// Add slideUp animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        to {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
    }
`;
document.head.appendChild(style);

// Notification System
let notificationDropdownOpen = false;

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    notificationDropdownOpen = !notificationDropdownOpen;
    
    if (notificationDropdownOpen) {
        dropdown.classList.add('show');
        loadNotifications();
    } else {
        dropdown.classList.remove('show');
    }
}

function loadNotifications() {
    fetch('/curuzamuhinzi/api/notifications.php')
        .then(response => response.json())
        .then(data => {
            const list = document.getElementById('notificationList');
            const badge = document.getElementById('notificationBadge');
            
            if (data.notifications && data.notifications.length > 0) {
                list.innerHTML = data.notifications.map(notification => `
                    <div class="notification-item ${!notification.is_read ? 'unread' : ''}" onclick="markAsRead(${notification.id})">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${formatTime(notification.created_at)}</div>
                    </div>
                `).join('');
                
                // Update badge
                const unreadCount = data.unread_count;
                if (unreadCount > 0) {
                    badge.textContent = unreadCount;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            } else {
                list.innerHTML = '<div class="notification-empty">No notifications yet</div>';
                badge.classList.add('hidden');
            }
        })
        .catch(() => {
            document.getElementById('notificationList').innerHTML = '<div class="notification-empty">Error loading notifications</div>';
        });
}

function markAsRead(notificationId) {
    fetch('/curuzamuhinzi/api/notifications.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'mark_read', id: notificationId})
    })
    .then(() => loadNotifications());
}

function markAllAsRead() {
    fetch('/curuzamuhinzi/api/notifications.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'mark_all_read'})
    })
    .then(() => loadNotifications());
}

function formatTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.notification-wrapper')) {
        document.getElementById('notificationDropdown').classList.remove('show');
        notificationDropdownOpen = false;
    }
});

// Real-time notification checking
if (document.getElementById('notificationBadge')) {
    // Check for new notifications every 30 seconds
    setInterval(() => {
        if (!notificationDropdownOpen) {
            fetch('/curuzamuhinzi/api/notifications.php?count_only=1')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                });
        }
    }, 30000);
    
    // Initial load
    setTimeout(() => {
        fetch('/curuzamuhinzi/api/notifications.php?count_only=1')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('notificationBadge');
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.classList.remove('hidden');
                }
            });
    }, 1000);
}
</script>