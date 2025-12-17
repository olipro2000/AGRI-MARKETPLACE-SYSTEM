<?php
session_start();
require_once '../config/database.php';

$db = new Database();
$errors = [];
$success = false;

if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    } else {
        $user = $db->fetch("SELECT id, first_name, email FROM users WHERE email = ? AND status = 'active'", [$email]);
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $db->query("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?) 
                       ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)", 
                       [$user['id'], $token, $expires]);
            
            // Send email (simplified - in production use proper email service)
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/curuzamuhinzi/auth/reset-password.php?token=" . $token;
            
            // For now, just show success message
            $success = true;
        } else {
            // Don't reveal if email exists or not for security
            $success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Curuza Muhinzi</title>
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
        
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .card {
            background: var(--bg-secondary);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        
        .header {
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
        
        .title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
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
        
        .btn {
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
        
        .btn:hover {
            background: var(--primary-dark);
        }
        
        .back-link {
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .back-link a {
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
        
        .success-content {
            text-align: center;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary-green);
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <?php if ($success): ?>
                <div class="success-content">
                    <div class="success-icon">✓</div>
                    <h1 class="title">Check Your Email</h1>
                    <p class="subtitle" style="margin-bottom: 2rem;">
                        If an account with that email exists, we've sent you a password reset link.
                    </p>
                    <div class="back-link">
                        <a href="login.php">← Back to Login</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="header">
                    <div class="logo">🌱</div>
                    <h1 class="title">Forgot Password</h1>
                    <p class="subtitle">Enter your email to receive a password reset link</p>
                </div>
                
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
                    
                    <button type="submit" class="btn">Send Reset Link</button>
                </form>
                
                <div class="back-link">
                    <a href="login.php">← Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>