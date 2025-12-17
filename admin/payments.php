<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$admin = $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);

// Get real payment proofs data
$payments = $db->query("
    SELECT 
        pp.*,
        u.first_name, u.last_name, u.phone, u.email,
        sp.name as plan_name, sp.price as plan_price,
        pm.name as payment_method_name, pm.account_number,
        us.id as subscription_id
    FROM payment_proofs pp
    JOIN users u ON pp.user_id = u.id
    JOIN user_subscriptions us ON pp.subscription_id = us.id
    JOIN subscription_plans sp ON us.plan_id = sp.id
    JOIN payment_methods pm ON pp.payment_method_id = pm.id
    ORDER BY pp.created_at DESC
")->fetchAll();

$payments = $payments ?: [];

$stats = [
    'total_payments' => count($payments),
    'pending_payments' => count(array_filter($payments, fn($p) => $p['status'] === 'pending')),
    'verified_payments' => count(array_filter($payments, fn($p) => $p['status'] === 'verified')),
    'total_volume' => array_sum(array_column(array_filter($payments, fn($p) => $p['status'] === 'verified'), 'amount'))
];

$page_title = 'Payments';
$current_page = 'payments';
include 'includes/header.php';
?>

<style>
    .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; }
    .alert-success { background: rgba(16,185,129,0.1); color: #059669; border: 1px solid rgba(16,185,129,0.3); }
    .alert-error { background: rgba(239,68,68,0.1); color: #dc2626; border: 1px solid rgba(239,68,68,0.3); }
    .payment-card {
        background: var(--bg-secondary);
        border-radius: 12px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    
    .payment-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .payment-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .payment-id {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .payment-method {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        background: rgba(22, 163, 74, 0.1);
        color: var(--primary-green);
        text-transform: uppercase;
    }
    
    .payment-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-green);
        margin-bottom: 1rem;
    }
    
    .payment-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .payment-parties {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background: rgba(22, 163, 74, 0.05);
        border-radius: 8px;
    }
    
    .party-info {
        flex: 1;
        text-align: center;
    }
    
    .party-name {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }
    
    .party-role {
        font-size: 0.75rem;
        color: var(--text-secondary);
        text-transform: uppercase;
    }
    
    .payment-arrow {
        font-size: 1.5rem;
        color: var(--primary-green);
    }
    
    .transaction-info {
        background: var(--bg-primary);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .transaction-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .transaction-row:last-child {
        margin-bottom: 0;
    }
    
    .transaction-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }
    
    .transaction-value {
        font-size: 0.875rem;
        color: var(--text-primary);
        font-weight: 500;
    }
    
    .payment-actions {
        display: flex;
        gap: 0.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }
    
    @media (max-width: 768px) {
        .payment-details {
            grid-template-columns: 1fr;
        }
        
        .payment-parties {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .payment-arrow {
            transform: rotate(90deg);
        }
    }
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Payment Management</h1>
            <p class="page-subtitle">Monitor transactions and payment processing</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">💳</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['total_payments']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Total Payments</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏳</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['pending_payments']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Pending</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['verified_payments']) ?></div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Verified</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">💰</div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= number_format($stats['total_volume']) ?> RWF</div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Payment Volume</div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Recent Payments</h3>
                <span class="table-count"><?= count($payments) ?> payments</span>
            </div>
            <div class="card-content">
                <?php foreach ($payments as $payment): ?>
                    <div class="payment-card">
                        <div class="payment-header">
                            <div class="payment-id">Payment #<?= $payment['id'] ?></div>
                            <div class="payment-method"><?= htmlspecialchars($payment['payment_method_name']) ?></div>
                        </div>
                        
                        <div class="payment-amount"><?= number_format($payment['amount']) ?> RWF</div>
                        
                        <div class="payment-parties">
                            <div class="party-info">
                                <div class="party-name"><?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?></div>
                                <div class="party-role">User</div>
                            </div>
                            <div class="payment-arrow">→</div>
                            <div class="party-info">
                                <div class="party-name"><?= htmlspecialchars($payment['plan_name']) ?> Plan</div>
                                <div class="party-role">Subscription</div>
                            </div>
                        </div>
                        
                        <div class="transaction-info">
                            <?php if ($payment['reference_number']): ?>
                            <div class="transaction-row">
                                <span class="transaction-label">Reference Number</span>
                                <span class="transaction-value"><?= htmlspecialchars($payment['reference_number']) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="transaction-row">
                                <span class="transaction-label">Subscription ID</span>
                                <span class="transaction-value">#<?= $payment['subscription_id'] ?></span>
                            </div>
                            <div class="transaction-row">
                                <span class="transaction-label">Phone Number</span>
                                <span class="transaction-value"><?= htmlspecialchars($payment['phone']) ?></span>
                            </div>
                            <div class="transaction-row">
                                <span class="transaction-label">Email</span>
                                <span class="transaction-value"><?= htmlspecialchars($payment['email']) ?></span>
                            </div>
                            <div class="transaction-row">
                                <span class="transaction-label">Status</span>
                                <span class="status-badge status-<?= $payment['status'] ?>"><?= ucfirst($payment['status']) ?></span>
                            </div>
                            <div class="transaction-row">
                                <span class="transaction-label">Date</span>
                                <span class="transaction-value"><?= date('M j, Y g:i A', strtotime($payment['created_at'])) ?></span>
                            </div>
                            <?php if ($payment['screenshot_url']): ?>
                            <div class="transaction-row">
                                <span class="transaction-label">Screenshot</span>
                                <a href="../uploads/payment_proofs/<?= htmlspecialchars($payment['screenshot_url']) ?>" target="_blank" class="transaction-value" style="color: var(--primary-green); text-decoration: underline;">View Proof</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="payment-actions">
                            <?php if ($payment['status'] === 'pending'): ?>
                                <form method="POST" action="subscription_actions.php" style="display: inline;">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="subscription_id" value="<?= $payment['subscription_id'] ?>">
                                    <button type="submit" class="btn-action btn-approve">✓ Approve</button>
                                </form>
                                <form method="POST" action="subscription_actions.php" style="display: inline;">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="subscription_id" value="<?= $payment['subscription_id'] ?>">
                                    <button type="submit" class="btn-action btn-cancel">✗ Reject</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($payment['admin_notes']): ?>
                                <button class="btn-action btn-view" onclick="alert('Admin Notes: <?= htmlspecialchars($payment['admin_notes']) ?>')">View Notes</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($payments)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                        <h3>No payments yet</h3>
                        <p>Payment transactions will appear here once orders are placed.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>