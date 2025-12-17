<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$admin = $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);

// Mock orders data since orders table doesn't exist yet
$orders = [
    [
        'id' => 1,
        'buyer_name' => 'Jean Uwimana',
        'seller_name' => 'Marie Mukamana',
        'product_name' => 'Fresh Tomatoes',
        'quantity' => 50,
        'unit' => 'kg',
        'total_amount' => 75000,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'province' => 'Kigali City'
    ],
    [
        'id' => 2,
        'buyer_name' => 'Paul Nzeyimana',
        'seller_name' => 'Grace Uwamahoro',
        'product_name' => 'Maize Seeds',
        'quantity' => 25,
        'unit' => 'kg',
        'total_amount' => 125000,
        'status' => 'completed',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'province' => 'Southern'
    ],
    [
        'id' => 3,
        'buyer_name' => 'Alice Mukamana',
        'seller_name' => 'John Habimana',
        'product_name' => 'Irish Potatoes',
        'quantity' => 100,
        'unit' => 'kg',
        'total_amount' => 80000,
        'status' => 'cancelled',
        'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
        'province' => 'Northern'
    ]
];

$stats = [
    'total_orders' => count($orders),
    'pending_orders' => count(array_filter($orders, fn($o) => $o['status'] === 'pending')),
    'completed_orders' => count(array_filter($orders, fn($o) => $o['status'] === 'completed')),
    'total_revenue' => array_sum(array_column(array_filter($orders, fn($o) => $o['status'] === 'completed'), 'total_amount'))
];

$page_title = 'Orders';
$current_page = 'orders';
include 'includes/header.php';
?>

<style>
    .order-card {
        background: var(--bg-secondary);
        border-radius: 12px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    
    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .order-id {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .order-status {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .status-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }
    
    .status-completed {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }
    
    .status-cancelled {
        background: rgba(239, 68, 68, 0.1);
        color: var(--error-color);
    }
    
    .order-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .detail-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .detail-value {
        font-size: 0.875rem;
        color: var(--text-primary);
        font-weight: 500;
    }
    
    .order-amount {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-green);
    }
    
    .order-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }
    
    .btn-action {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-view {
        background: var(--primary-green);
        color: white;
    }
    
    .btn-contact {
        background: #3b82f6;
        color: white;
    }
    
    .btn-cancel {
        background: var(--error-color);
        color: white;
    }
    
    @media (max-width: 768px) {
        .order-details {
            grid-template-columns: 1fr;
        }
        
        .order-header {
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-start;
        }
    }
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Order Management</h1>
            <p class="page-subtitle">Monitor and manage marketplace orders</p>
        </div>
        
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['total_orders']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Total Orders</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏳</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['pending_orders']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Pending Orders</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['completed_orders']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Completed Orders</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">💰</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['total_revenue']) ?> RWF</div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Total Revenue</div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Recent Orders</h3>
                <span class="table-count"><?= count($orders) ?> orders</span>
            </div>
            <div class="card-content">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-id">Order #<?= $order['id'] ?></div>
                            <div class="order-status status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></div>
                        </div>
                        
                        <div class="order-details">
                            <div class="detail-item">
                                <div class="detail-label">Product</div>
                                <div class="detail-value"><?= htmlspecialchars($order['product_name']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Buyer</div>
                                <div class="detail-value"><?= htmlspecialchars($order['buyer_name']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Seller</div>
                                <div class="detail-value"><?= htmlspecialchars($order['seller_name']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Quantity</div>
                                <div class="detail-value"><?= $order['quantity'] ?> <?= $order['unit'] ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Location</div>
                                <div class="detail-value"><?= htmlspecialchars($order['province']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Total Amount</div>
                                <div class="detail-value order-amount"><?= number_format($order['total_amount']) ?> RWF</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Order Date</div>
                                <div class="detail-value"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></div>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <button class="btn-action btn-view">View Details</button>
                            <button class="btn-action btn-contact">Contact Parties</button>
                            <?php if ($order['status'] === 'pending'): ?>
                                <button class="btn-action btn-cancel">Cancel Order</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                        <h3>No orders yet</h3>
                        <p>Orders will appear here once users start making purchases.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>