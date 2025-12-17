<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Dashboard' ?> - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #16a34a;
            --primary-dark: #15803d;
            --primary-light: #22c55e;
            --bg-primary: #f8fafb;
            --bg-secondary: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --info-color: #3b82f6;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Government Header */
        .top-header {
            background: #ffffff;
            border-bottom: 3px solid var(--primary-green);
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 64px;
        }
        
        .header-container {
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
        }
        
        /* Desktop: Adjust for sidebar */
        @media (min-width: 1024px) {
            .header-container {
                margin-left: 280px;
            }
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .menu-toggle {
            display: none;
            background: var(--primary-green);
            border: none;
            width: 40px;
            height: 40px;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 1.125rem;
            transition: background 0.2s;
        }
        
        .menu-toggle:hover {
            background: var(--primary-dark);
        }
        
        .gov-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .gov-logo {
            width: 40px;
            height: 40px;
            background: var(--primary-green);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }
        
        .gov-info {
            display: flex;
            flex-direction: column;
        }
        
        .gov-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.2;
        }
        
        .gov-subtitle {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-time {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .user-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .user-section:hover {
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.2;
        }
        
        .user-role {
            font-size: 0.75rem;
            opacity: 0.7;
            font-weight: 400;
        }
        
        .logout-btn {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .logout-btn:hover {
            background: var(--error-color);
            color: white;
            border-color: var(--error-color);
        }
        
        /* Mobile Responsive */
        @media (max-width: 1023px) {
            .menu-toggle {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .gov-info {
                display: none;
            }
            
            .header-time {
                display: none;
            }
        }
        
        @media (max-width: 640px) {
            .user-details {
                display: none;
            }
            
            .logout-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="top-header">
        <div class="header-container">
            <div class="header-left">
                <button class="menu-toggle" onclick="toggleSidebar()">
                    ☰
                </button>
                <div class="gov-brand">
                    <div class="gov-logo">🌱</div>
                    <div class="gov-info">
                        <div class="gov-title">Curuza Muhinzi</div>
                        <div class="gov-subtitle">Administration Portal</div>
                    </div>
                </div>
            </div>
            
            <div class="header-right">
                <div class="header-time">
                    <?= date('l, F j, Y') ?>
                </div>
                
                <div class="user-section">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin['first_name'] . ' ' . $admin['last_name']) ?>&background=16a34a&color=fff" 
                         class="user-avatar" alt="Avatar">
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></div>
                        <div class="user-role"><?= ucfirst($admin['department']) ?> Department</div>
                    </div>
                </div>
                
                <button class="logout-btn" onclick="location.href='logout.php'">
                    Logout
                </button>
            </div>
        </div>
    </div>