<?php
require_once 'config/database.php';

$db = new Database();

try {
    // Read the SQL file
    $sql = file_get_contents('database/messages.sql');
    
    // Execute using PDO directly for CREATE TABLE
    $pdo = $db->getConnection();
    $pdo->exec($sql);
    
    echo "✅ Messages table created successfully!<br>";
    echo "<a href='feed.php'>Go to Feed</a> | <a href='messages.php'>Go to Messages</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
