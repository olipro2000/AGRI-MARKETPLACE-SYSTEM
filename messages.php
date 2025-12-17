<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$db = new Database();
$userId = $_SESSION['user_id'];
$current_user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

// Get conversations
$conversations = $db->fetchAll("
    SELECT 
        CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as other_user_id,
        u.first_name, u.last_name, u.profile_picture,
        m.message as last_message,
        m.created_at as last_message_time,
        m.sender_id as last_sender_id,
        COUNT(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 END) as unread_count
    FROM messages m
    INNER JOIN (
        SELECT 
            LEAST(sender_id, receiver_id) as user1,
            GREATEST(sender_id, receiver_id) as user2,
            MAX(created_at) as max_time
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY user1, user2
    ) latest ON (
        LEAST(m.sender_id, m.receiver_id) = latest.user1 AND
        GREATEST(m.sender_id, m.receiver_id) = latest.user2 AND
        m.created_at = latest.max_time
    )
    JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
    WHERE m.sender_id = ? OR m.receiver_id = ?
    GROUP BY other_user_id, u.first_name, u.last_name, u.profile_picture, m.message, m.created_at, m.sender_id
    ORDER BY m.created_at DESC
", [$userId, $userId, $userId, $userId, $userId, $userId, $userId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
    <style>
        body { background: #f5f7fa; }
        .messages-container { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        
        .messages-header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 20px; padding: 2.5rem; margin-bottom: 2rem; box-shadow: 0 8px 32px rgba(16,185,129,0.25); color: white; }
        .messages-header h1 { font-size: 2.25rem; font-weight: 800; margin-bottom: 0.5rem; }
        .messages-header p { opacity: 0.95; font-size: 1rem; }
        
        .messages-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-box { background: white; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center; }
        .stat-value { font-size: 2rem; font-weight: 800; color: #10b981; margin-bottom: 0.25rem; }
        .stat-label { font-size: 0.875rem; color: #64748b; font-weight: 600; }
        
        .search-bar { background: white; padding: 1rem 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .search-input { width: 100%; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; transition: all 0.2s; }
        .search-input:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
        
        .conversations-list { background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .conversation { display: flex; align-items: center; gap: 1.25rem; padding: 1.5rem; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: all 0.3s; position: relative; }
        .conversation:last-child { border-bottom: none; }
        .conversation:hover { background: linear-gradient(90deg, rgba(16,185,129,0.05) 0%, transparent 100%); transform: translateX(4px); }
        .conversation.unread { background: linear-gradient(90deg, rgba(16,185,129,0.08) 0%, transparent 100%); }
        .conversation.unread::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, #10b981 0%, #059669 100%); }
        
        .conv-avatar { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.5rem; overflow: hidden; flex-shrink: 0; box-shadow: 0 4px 12px rgba(16,185,129,0.3); position: relative; }
        .conv-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .online-indicator { position: absolute; bottom: 2px; right: 2px; width: 14px; height: 14px; background: #10b981; border: 3px solid white; border-radius: 50%; }
        
        .conv-info { flex: 1; min-width: 0; }
        .conv-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
        .conv-name { font-weight: 700; color: #1e293b; font-size: 1.05rem; }
        .conv-time { font-size: 0.8rem; color: #94a3b8; font-weight: 500; }
        .conv-message { color: #64748b; font-size: 0.9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.5; }
        .conv-message.unread { font-weight: 600; color: #1e293b; }
        .conv-message-prefix { color: #10b981; font-weight: 600; }
        
        .unread-badge { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 20px; min-width: 28px; height: 28px; padding: 0 8px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; flex-shrink: 0; box-shadow: 0 2px 8px rgba(16,185,129,0.4); }
        
        .empty-state { text-align: center; padding: 5rem 2rem; }
        .empty-icon { font-size: 5rem; margin-bottom: 1.5rem; opacity: 0.5; }
        .empty-state h3 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem; }
        .empty-state p { color: #64748b; font-size: 1rem; margin-bottom: 2rem; }
        .btn-browse { display: inline-block; padding: 0.875rem 2rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 12px; text-decoration: none; font-weight: 600; transition: all 0.3s; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
        .btn-browse:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); }
        
        @media (max-width: 768px) {
            .messages-container { padding: 0 0.5rem; margin: 1rem auto; }
            .messages-header { padding: 1.75rem; border-radius: 16px; }
            .messages-header h1 { font-size: 1.75rem; }
            .messages-stats { grid-template-columns: repeat(2, 1fr); }
            .conversation { padding: 1.25rem; gap: 1rem; }
            .conv-avatar { width: 52px; height: 52px; font-size: 1.25rem; }
            .conv-name { font-size: 0.95rem; }
            .conv-message { font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="messages-container">
        <div class="messages-header">
            <h1>💬 Messages</h1>
            <p>Your conversations with buyers and sellers</p>
        </div>
        
        <?php 
        $conversations = $conversations ?: [];
        $totalConvs = count($conversations);
        $unreadConvs = count(array_filter($conversations, fn($c) => $c['unread_count'] > 0 && $c['last_sender_id'] != $userId));
        $totalUnread = $conversations ? array_sum(array_column($conversations, 'unread_count')) : 0;
        ?>
        
        <div class="messages-stats">
            <div class="stat-box">
                <div class="stat-value"><?= $totalConvs ?></div>
                <div class="stat-label">Total Chats</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?= $unreadConvs ?></div>
                <div class="stat-label">Unread Chats</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?= $totalUnread ?></div>
                <div class="stat-label">New Messages</div>
            </div>
        </div>
        
        <?php if (!empty($conversations)): ?>
        <div class="search-bar">
            <input type="text" class="search-input" placeholder="🔍 Search conversations..." id="searchConversations" onkeyup="filterConversations()">
        </div>
        <?php endif; ?>
        
        <div class="conversations-list">
            <?php if (empty($conversations)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No messages yet</h3>
                    <p>Start a conversation by contacting a seller on their product page</p>
                    <a href="products.php" class="btn-browse">Browse Products</a>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <div class="conversation <?= $conv['unread_count'] > 0 && $conv['last_sender_id'] != $userId ? 'unread' : '' ?>" onclick="location.href='chat.php?user=<?= $conv['other_user_id'] ?>'" data-name="<?= strtolower($conv['first_name'] . ' ' . $conv['last_name']) ?>">
                        <div class="conv-avatar">
                            <?php if ($conv['profile_picture']): ?>
                                <img src="/curuzamuhinzi/uploads/profiles/<?= htmlspecialchars($conv['profile_picture']) ?>" alt="<?= htmlspecialchars($conv['first_name']) ?>">
                            <?php else: ?>
                                <?= strtoupper(substr($conv['first_name'], 0, 1)) ?>
                            <?php endif; ?>
                            <div class="online-indicator"></div>
                        </div>
                        <div class="conv-info">
                            <div class="conv-header">
                                <span class="conv-name"><?= htmlspecialchars($conv['first_name'] . ' ' . $conv['last_name']) ?></span>
                                <span class="conv-time"><?php
                                    $time = strtotime($conv['last_message_time']);
                                    $diff = time() - $time;
                                    if ($diff < 60) echo 'Just now';
                                    elseif ($diff < 3600) echo floor($diff/60) . 'm ago';
                                    elseif ($diff < 86400) echo floor($diff/3600) . 'h ago';
                                    elseif ($diff < 604800) echo floor($diff/86400) . 'd ago';
                                    else echo date('M j', $time);
                                ?></span>
                            </div>
                            <div class="conv-message <?= $conv['unread_count'] > 0 && $conv['last_sender_id'] != $userId ? 'unread' : '' ?>">
                                <?php if ($conv['last_sender_id'] == $userId): ?>
                                    <span class="conv-message-prefix">You: </span>
                                <?php endif; ?>
                                <?= htmlspecialchars(substr($conv['last_message'], 0, 60)) ?>
                                <?= strlen($conv['last_message']) > 60 ? '...' : '' ?>
                            </div>
                        </div>
                        <?php if ($conv['unread_count'] > 0 && $conv['last_sender_id'] != $userId): ?>
                            <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/bottom-nav.php'; ?>
    
    <script>
    function filterConversations() {
        const search = document.getElementById('searchConversations').value.toLowerCase();
        const conversations = document.querySelectorAll('.conversation');
        
        conversations.forEach(conv => {
            const name = conv.dataset.name;
            conv.style.display = name.includes(search) ? 'flex' : 'none';
        });
    }
    </script>
</body>
</html>
