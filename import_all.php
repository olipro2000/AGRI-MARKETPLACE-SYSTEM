<?php
/**
 * Import All SQL Files
 * Run: http://localhost/curuzamuhinzi/import_all.php
 */

$host = 'localhost';
$dbname = 'curuzamuhinzi';
$username = 'root';
$password = '';

$sqlFiles = [
    'notifications_table.sql',
    'products_table.sql',
    'subscription_system.sql',
    'users_table.sql',
    'admin.sql'
];

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html><html><head><title>Import All SQL</title><style>
    body{font-family:Arial;max-width:900px;margin:50px auto;padding:20px}
    .success{background:#d4edda;color:#155724;padding:15px;border-radius:5px;margin:10px 0}
    .error{background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;margin:10px 0}
    .info{background:#d1ecf1;color:#0c5460;padding:15px;border-radius:5px;margin:10px 0}
    h1{color:#059669}
    .file{background:#f8f9fa;padding:10px;margin:10px 0;border-left:4px solid #059669}
    </style></head><body><h1>🌾 Import All SQL Files</h1>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE $dbname");
    echo "<div class='success'>✅ Database '$dbname' ready</div>";
    
    foreach ($sqlFiles as $file) {
        echo "<div class='file'><strong>📄 Processing: $file</strong><br>";
        
        if (!file_exists($file)) {
            echo "<span style='color:#dc3545'>❌ File not found</span></div>";
            continue;
        }
        
        $sql = file_get_contents($file);
        
        // Split and execute
        $statements = array_filter(
            array_map('trim', preg_split('/;[\r\n]+/', $sql)),
            function($s) { return !empty($s) && !preg_match('/^(--|\/\*)/', $s); }
        );
        
        $success = 0;
        $skipped = 0;
        
        foreach ($statements as $stmt) {
            try {
                $pdo->exec($stmt);
                $success++;
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    $skipped++;
                } else {
                    echo "<span style='color:#856404'>⚠ " . htmlspecialchars($e->getMessage()) . "</span><br>";
                }
            }
        }
        
        echo "✓ Executed: $success | Skipped: $skipped</div>";
    }
    
    // Show tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='info'><strong>📊 Total Tables: " . count($tables) . "</strong><br>";
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "• $table ($count records)<br>";
    }
    echo "</div>";
    
    echo "<div class='success'>🎉 Import completed!</div>";
    echo "<p><a href='index.php' style='color:#059669;font-weight:bold'>← Back to Homepage</a></p>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</body></html>";
?>
