<?php
/**
 * Reset Admin Password
 * Run once: http://localhost/curuzamuhinzi/admin/reset_password.php
 */

require_once '../config/database.php';

$db = new Database();

// Reset superadmin password to "password"
$newPassword = 'password';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$db->query(
    "UPDATE admins SET password_hash = ?, login_attempts = 0 WHERE email = 'superadmin@curuzamuhinzi.com'",
    [$hash]
);

// Check if admin exists
$admin = $db->fetch("SELECT * FROM admins WHERE email = 'superadmin@curuzamuhinzi.com'");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; }
        .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin: 20px 0; }
        h1 { color: #059669; }
        .credentials { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .credentials strong { color: #059669; }
        a { color: #059669; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>
    <h1>🔐 Admin Password Reset</h1>
    
    <?php if ($admin): ?>
        <div class="success">
            ✅ Password reset successful!
        </div>
        
        <div class="credentials">
            <h3>Login Credentials:</h3>
            <p><strong>Email:</strong> superadmin@curuzamuhinzi.com</p>
            <p><strong>Password:</strong> password</p>
            <p><strong>Department:</strong> <?= $admin['department'] ?></p>
            <p><strong>Status:</strong> <?= $admin['status'] ?></p>
        </div>
        
        <div class="info">
            <strong>⚠️ Security Note:</strong><br>
            Delete this file after use: <code>admin/reset_password.php</code>
        </div>
        
        <p><a href="index.php">→ Go to Admin Login</a></p>
    <?php else: ?>
        <div class="error">
            ❌ Superadmin account not found!<br><br>
            Please run the admin.sql import first.
        </div>
        <p><a href="../import_all.php">→ Import SQL Files</a></p>
    <?php endif; ?>
</body>
</html>
