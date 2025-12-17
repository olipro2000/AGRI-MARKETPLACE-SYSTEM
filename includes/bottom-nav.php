<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav class="bottom-nav">
    <a href="/curuzamuhinzi/feed.php" class="bottom-nav-item <?= $current_page === 'feed' ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9,22 9,12 15,12 15,22"/>
        </svg>
        <span class="nav-label">Home</span>
    </a>
    
    <a href="/curuzamuhinzi/crops.php" class="bottom-nav-item <?= $current_page === 'crops' ? 'active' : '' ?>">
        <span class="nav-icon" style="font-size: 22px;">🌽</span>
        <span class="nav-label">Crops</span>
    </a>
    
    <a href="/curuzamuhinzi/livestock.php" class="bottom-nav-item <?= $current_page === 'livestock' ? 'active' : '' ?>">
        <span class="nav-icon" style="font-size: 22px;">🐄</span>
        <span class="nav-label">Livestock</span>
    </a>
    
    <a href="/curuzamuhinzi/equipment.php" class="bottom-nav-item <?= $current_page === 'equipment' ? 'active' : '' ?>">
        <span class="nav-icon" style="font-size: 22px;">🚜</span>
        <span class="nav-label">Equipment</span>
    </a>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/curuzamuhinzi/dashboard/profile.php" class="bottom-nav-item <?= $current_page === 'profile' ? 'active' : '' ?>">
            <?php if ($current_user && $current_user['profile_picture']): ?>
                <img src="/curuzamuhinzi/uploads/profiles/<?= htmlspecialchars($current_user['profile_picture']) ?>" alt="Profile" class="nav-icon profile-img">
            <?php else: ?>
                <div class="nav-icon profile-avatar"><?= strtoupper(substr($current_user['first_name'] ?? 'U', 0, 1)) ?></div>
            <?php endif; ?>
            <span class="nav-label">Profile</span>
        </a>
    <?php else: ?>
        <a href="/curuzamuhinzi/auth/login.php" class="bottom-nav-item">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10,17 15,12 10,7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            <span class="nav-label">Login</span>
        </a>
    <?php endif; ?>
</nav>

<style>
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 1px solid rgba(16,185,129,0.1);
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    padding: 0;
    z-index: 1000;
    box-shadow: 0 -4px 24px rgba(0,0,0,0.08);
    height: 65px;
}

.bottom-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #64748b;
    padding: 8px 4px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    gap: 4px;
}

.bottom-nav-item::after {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%) scaleX(0);
    width: 40px;
    height: 3px;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    border-radius: 0 0 3px 3px;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.bottom-nav-item.active::after {
    transform: translateX(-50%) scaleX(1);
}

.bottom-nav-item.active {
    color: #10b981;
}

.bottom-nav-item:active {
    transform: scale(0.95);
}

.bottom-nav-item .nav-icon {
    width: 24px;
    height: 24px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: block;
    stroke-width: 2;
}

.bottom-nav-item.active .nav-icon {
    transform: translateY(-2px);
    filter: drop-shadow(0 2px 4px rgba(16,185,129,0.3));
}

.bottom-nav-item .nav-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.2px;
    transition: all 0.3s ease;
}

.bottom-nav-item.active .nav-label {
    font-weight: 700;
    color: #10b981;
}

/* Hide on desktop */
@media (min-width: 769px) {
    .bottom-nav {
        display: none;
    }
}

.profile-img {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.bottom-nav-item.active .profile-img {
    border-color: #10b981;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(16,185,129,0.3);
}

.profile-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(16,185,129,0.2);
    transition: all 0.3s ease;
}

.bottom-nav-item.active .profile-avatar {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16,185,129,0.4);
}

/* Add bottom padding to body when bottom nav is visible */
@media (max-width: 768px) {
    body {
        padding-bottom: 65px;
    }
}
</style>