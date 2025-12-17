<?php
session_start();
require_once '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        $db = new Database();
        $admin = $db->fetch(
            "SELECT * FROM admins WHERE email = ? AND status = 'active' AND deleted_at IS NULL", 
            [$email]
        );
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
            $_SESSION['admin_department'] = $admin['department'];
            
            // Update last login
            $db->query("UPDATE admins SET last_login_at = NOW(), login_attempts = 0 WHERE id = ?", [$admin['id']]);
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password';
            // Increment login attempts
            if ($admin) {
                $db->query("UPDATE admins SET login_attempts = login_attempts + 1 WHERE id = ?", [$admin['id']]);
            }
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curuza Muhinzi - Administrative Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary-color: #16a34a;
            --primary-dark: #15803d;
            --primary-light: #22c55e;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-primary: #f9fafb;
            --bg-secondary: #ffffff;
            --border-color: #e5e7eb;
            --success-color: #16a34a;
            --error-color: #dc2626;
            --warning-color: #d97706;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }
        
        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
            z-index: -1;
        }
        

        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .login-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            position: relative;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-color);
        }
        
        .card-header {
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        }
        
        .brand-logo {
            width: 70px;
            height: 70px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
        }
        
        .brand-logo::before {
            content: '🌱';
            font-size: 2rem;
        }
        
        .system-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .system-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .admin-badge {
            background: rgba(22, 163, 74, 0.1);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.2s ease;
            outline: none;
        }
        
        .form-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
            background: var(--bg-secondary);
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1.125rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: color 0.2s;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .login-button {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .login-button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
        }
        
        .login-button:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: var(--error-color);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: center;
        }
        
        .card-footer {
            padding: 1.5rem 2rem;
            background: var(--bg-primary);
            border-top: 1px solid var(--border-color);
            text-align: center;
        }
        
        .security-note {
            font-size: 0.875rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .loading .login-button {
            background: var(--text-secondary);
        }
        
        @media (max-width: 768px) {
            .login-container {
                padding: 1rem;
            }
            
            .card-header {
                padding: 1.5rem 1.5rem 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .card-footer {
                padding: 1rem 1.5rem;
            }
            
            .brand-logo {
                width: 60px;
                height: 60px;
            }
            
            .brand-logo::before {
                font-size: 1.75rem;
            }
            
            .system-title {
                font-size: 1.5rem;
            }
            
            .system-subtitle {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 0.5rem;
            }
            
            .card-header {
                padding: 1.25rem 1.25rem 1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .card-footer {
                padding: 1rem 1.25rem;
            }
            
            .brand-logo {
                width: 50px;
                height: 50px;
            }
            
            .brand-logo::before {
                font-size: 1.5rem;
            }
            
            .system-title {
                font-size: 1.3rem;
            }
            
            .form-input {
                font-size: 16px;
                padding: 0.875rem 1rem 0.875rem 2.75rem;
            }
            
            .input-icon {
                left: 0.875rem;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 360px) {
            .card-header {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .system-title {
                font-size: 1.2rem;
            }
            
            .admin-badge {
                font-size: 0.8rem;
                padding: 0.375rem 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <div class="brand-logo"></div>
                <h1 class="system-title">Curuza Muhinzi</h1>
                <p class="system-subtitle">Agricultural Management System</p>
                <div class="admin-badge">Administrative Portal</div>
            </div>
            
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="error-message">
                        ⚠️ <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📧</span>
                            <input type="email" class="form-input" name="email" required 
                                   placeholder="Enter your email address" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   autocomplete="email">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔐</span>
                            <input type="password" class="form-input" name="password" required 
                                   placeholder="Enter your password"
                                   autocomplete="current-password" id="password">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <span id="toggleIcon">👁️</span>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="login-button" id="loginBtn">
                        <span id="btnText">Login</span>
                    </button>
                </form>
            </div>
            
            <div class="card-footer">
                <div class="security-note">
                    <span>🔒</span>
                    <span>Secure Connection - All data is encrypted</span>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const form = this;
            
            // Add loading state
            form.classList.add('loading');
            btnText.innerHTML = '<span class="spinner"></span>Authenticating...';
            loginBtn.disabled = true;
            
            // Remove loading state after 3 seconds if form hasn't submitted
            setTimeout(() => {
                if (form.classList.contains('loading')) {
                    form.classList.remove('loading');
                    btnText.textContent = 'Login';
                    loginBtn.disabled = false;
                }
            }, 3000);
        });
        
        // Auto-focus first empty field
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.querySelector('input[name="email"]');
            const passwordInput = document.querySelector('input[name="password"]');
            
            if (!emailInput.value) {
                emailInput.focus();
            } else {
                passwordInput.focus();
            }
        });
        
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>