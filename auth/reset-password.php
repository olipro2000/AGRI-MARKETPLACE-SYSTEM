<?php
session_start();
require_once '../config/database.php';

$db = new Database();
$errors = [];
$token = $_GET['token'] ?? '';
$valid_token = false;
$user = null;

if ($token) {
    // Check if token is valid and not expired
    $reset = $db->fetch("SELECT pr.*, u.email, u.first_name FROM password_resets pr 
                        JOIN users u ON pr.user_id = u.id 
                        WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0", [$token]);
    
    if ($reset) {
        $valid_token = true;
        $user = $reset;
    }
}

if ($_POST && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    } else {
        // Update password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $db->query("UPDATE users SET password_hash = ? WHERE id = ?", [$password_hash, $user['user_id']]);
        
        // Mark token as used
        $db->query("UPDATE password_resets SET used = 1 WHERE token = ?", [$token]);
        
        $_SESSION['success_message'] = 'Password reset successfully. You can now login with your new password.';
        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Curuza Muhinzi</title>
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
        
        .error-content {
            text-align: center;
        }
        
        .error-icon {
            width: 80px;
            height: 80px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--error-color);
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <?php if (!$valid_token): ?>
                <div class="error-content">
                    <div class="error-icon">⚠</div>
                    <h1 class="title">Invalid or Expired Link</h1>
                    <p class="subtitle" style="margin-bottom: 2rem;">
                        This password reset link is invalid or has expired. Please request a new one.
                    </p>
                    <div class="back-link">
                        <a href="forgot-password.php">Request New Link</a> | 
                        <a href="login.php">Back to Login</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="header">
                    <div class="logo">🌱</div>
                    <h1 class="title">Reset Password</h1>
                    <p class="subtitle">Enter your new password for <?= htmlspecialchars($user['email']) ?></p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?= implode('<br>', $errors) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-input" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-input" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn">Reset Password</button>
                </form>
                
                <div class="back-link">
                    <a href="login.php">← Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>