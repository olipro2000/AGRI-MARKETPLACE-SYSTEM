    <style>
        /* Main Content Wrapper */
        .main-wrapper {
            min-height: 100vh;
            padding-top: 64px;
        }
        
        @media (min-width: 1024px) {
            .main-wrapper {
                margin-left: 280px;
            }
        }
        
        /* Sidebar Styles */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 150;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100vh;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            transition: left 0.3s ease;
            z-index: 200;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .sidebar-logo {
            width: 40px;
            height: 40px;
            background: var(--primary-green);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: 0 2px 8px rgba(22, 163, 74, 0.2);
        }
        
        .sidebar-info h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.125rem;
        }
        
        .sidebar-info p {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        
        .nav-menu {
            padding: 1rem 0 6rem;
        }
        
        .nav-section {
            margin-bottom: 2rem;
        }
        
        .nav-section:last-child {
            margin-bottom: 0;
        }
        
        .nav-section-title {
            padding: 0 1.5rem 0.75rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
            margin-bottom: 0.75rem;
        }
        
        .nav-item {
            margin: 0.125rem 1rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.875rem 1.125rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 0.875rem;
            position: relative;
        }
        
        .nav-link:hover {
            background: rgba(22, 163, 74, 0.08);
            color: var(--primary-green);
            transform: translateX(2px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(22, 163, 74, 0.3);
        }
        
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: white;
            border-radius: 0 2px 2px 0;
        }
        
        .nav-icon {
            font-size: 1.125rem;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .nav-badge {
            background: var(--error-color);
            color: white;
            font-size: 0.7rem;
            padding: 0.125rem 0.375rem;
            border-radius: 12px;
            margin-left: auto;
            font-weight: 600;
        }
        
        .sidebar-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 280px;
            padding: 1.25rem 1.5rem;
            border-top: 1px solid var(--border-color);
            background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
            z-index: 10;
        }
        
        .logout-link {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.875rem 1.125rem;
            color: var(--error-color);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 0.875rem;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .logout-link:hover {
            background: rgba(239, 68, 68, 0.1);
            transform: translateX(2px);
            border-color: var(--error-color);
        }
        
        /* Desktop Sidebar */
        @media (min-width: 1024px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 64px;
                height: calc(100vh - 64px);
                box-shadow: none;
                border-right: 1px solid var(--border-color);
            }
            
            .sidebar-overlay {
                display: none;
            }
            
            .sidebar-header {
                display: none;
            }
            
            .nav-menu {
                padding-top: 2rem;
            }
            
            .sidebar-footer {
                top: auto;
                bottom: 0;
            }
        }
        
        /* Mobile adjustments */
        @media (max-width: 1023px) {
            .sidebar {
                top: 0;
                height: 100vh;
            }
        }
    </style>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="sidebar-logo">🌱</div>
                <div class="sidebar-info">
                    <h3>Curuza Muhinzi</h3>
                    <p>Admin Portal</p>
                </div>
            </div>
        </div>
        
        <nav class="nav-menu">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <div class="nav-item">
                    <a class="nav-link <?= ($current_page == 'dashboard') ? 'active' : '' ?>" href="dashboard.php">
                        <span class="nav-icon">📊</span>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?= ($current_page == 'analytics') ? 'active' : '' ?>" href="analytics.php">
                        <span class="nav-icon">📈</span>
                        <span>Analytics</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Users & Access</div>
                <div class="nav-item">
                    <a class="nav-link <?= ($current_page == 'users') ? 'active' : '' ?>" href="users.php">
                        <span class="nav-icon">👥</span>
                        <span>Users</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?= ($current_page == 'subscriptions') ? 'active' : '' ?>" href="subscriptions.php">
                        <span class="nav-icon">💳</span>
                        <span>Subscriptions</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?= ($current_page == 'cooperatives') ? 'active' : '' ?>" href="cooperatives.php">
                        <span class="nav-icon">🤝</span>
                        <span>Cooperatives</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Marketplace</div>
                <div class="nav-item">
                    <a class="nav-link <?= ($current_page == 'products') ? 'active' : '' ?>" href="products.php">
                        <span class="nav-icon">📝</span>
                        <span>Products</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?= ($current_page == 'orders') ? 'active' : '' ?>" href="orders.php">
                        <span class="nav-icon">🛒</span>
                        <span>Orders</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?= ($current_page == 'payments') ? 'active' : '' ?>" href="payments.php">
                        <span class="nav-icon">💰</span>
                        <span>Payments</span>
                        <span class="nav-badge">3</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">System</div>
                <div class="nav-item">
                    <a class="nav-link <?= ($current_page == 'broadcast') ? 'active' : '' ?>" href="broadcast.php">
                        <span class="nav-icon">📢</span>
                        <span>Broadcast</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?= ($current_page == 'settings') ? 'active' : '' ?>" href="settings.php">
                        <span class="nav-icon">⚙️</span>
                        <span>Settings</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <a class="logout-link" href="logout.php">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
        
        // Close sidebar when clicking on nav links (mobile)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    closeSidebar();
                }
            });
        });
        
        // Close sidebar on window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });
    </script>