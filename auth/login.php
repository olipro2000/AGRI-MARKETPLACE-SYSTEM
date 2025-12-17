<?php
session_start();
require_once '../config/database.php';

$db = new Database();
$errors = [];

if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($password)) $errors[] = 'Password is required';
    
    if (empty($errors)) {
        $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                $errors[] = 'Your account is not active. Please contact support.';
            } else {
                // Update last login
                $db->query("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?", [$user['id']]);
                
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect to feed page
                header('Location: ../feed.php');
                exit;
            }
        } else {
            $errors[] = 'Invalid email or password';
            
            // Track failed attempts
            if ($user) {
                $db->query("UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?", [$user['id']]);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #16a34a;
            --primary-dark: #15803d;
            --bg-primary: #f8fafb;
            --bg-secondary: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --error-color: #ef4444;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            background: var(--primary-green);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin: 0 auto 1rem;
        }
        
        .login-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
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
            border-radius: 6px;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-green);
        }
        
        .login-btn {
            width: 100%;
            background: var(--primary-green);
            color: white;
            border: none;
            padding: 0.875rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 1rem;
        }
        
        .login-btn:hover {
            background: var(--primary-dark);
        }
        

        
        .forgot-password {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .forgot-password a {
            color: var(--primary-green);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .register-link {
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .register-link a {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 500;
        }
        
        .alert {
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">🌱</div>
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Sign in to your Curuza Muhinzi account</p>
            </div>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?= implode('<br>', $errors) ?>
                </div>
            <?php endif; ?>
            

            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>
            </form>
            
            <div class="forgot-password">
                <a href="forgot-password.php">Forgot your password?</a>
            </div>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Create one here</a>
            </div>
        </div>
    </div>
    

</body>
</html>