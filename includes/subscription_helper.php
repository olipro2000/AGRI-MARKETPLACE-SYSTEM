<?php
// Subscription Helper Functions

class SubscriptionHelper {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Check if user can post products
     */
    public function canUserPostProducts($user_id) {
        $subscription = $this->getUserActiveSubscription($user_id);
        
        if (!$subscription) {
            return false; // No active subscription
        }
        
        if ($subscription['status'] !== 'active') {
            return false; // Subscription not active
        }
        
        if ($subscription['expires_at'] && $subscription['expires_at'] < date('Y-m-d')) {
            return false; // Subscription expired
        }
        
        return true;
    }
    
    /**
     * Get user's active subscription
     */
    public function getUserActiveSubscription($user_id) {
        return $this->db->fetch("
            SELECT us.*, sp.name as plan_name, sp.max_products, sp.features 
            FROM user_subscriptions us 
            JOIN subscription_plans sp ON us.plan_id = sp.id 
            WHERE us.user_id = ? AND us.status = 'active' 
            ORDER BY us.expires_at DESC LIMIT 1
        ", [$user_id]);
    }
    
    /**
     * Check if user is verified (has paid subscription)
     */
    public function isUserVerified($user_id) {
        $verification = $this->db->fetch("
            SELECT * FROM user_verifications 
            WHERE user_id = ? AND verification_type = 'subscription_verified' AND is_active = 1
        ", [$user_id]);
        
        return $verification !== false;
    }
    
    /**
     * Get user's product posting limit
     */
    public function getUserProductLimit($user_id) {
        $subscription = $this->getUserActiveSubscription($user_id);
        
        if (!$subscription) {
            return 0; // Free users can't post
        }
        
        return $subscription['max_products']; // NULL means unlimited
    }
    
    /**
     * Count user's products this month
     */
    public function getUserProductsThisMonth($user_id) {
        return $this->db->fetchColumn("
            SELECT COUNT(*) FROM products 
            WHERE user_id = ? AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ", [$user_id]) ?: 0;
    }
    
    /**
     * Verify user subscription and add verification badge
     */
    public function verifyUserSubscription($user_id, $subscription_id, $verified_by = null) {
        try {
            // Update subscription status
            $this->db->query("
                UPDATE user_subscriptions 
                SET status = 'active', starts_at = CURDATE() 
                WHERE id = ? AND user_id = ?
            ", [$subscription_id, $user_id]);
            
            // Add verification badge
            $this->db->query("
                INSERT INTO user_verifications (user_id, verification_type, verified_by) 
                VALUES (?, 'subscription_verified', ?) 
                ON DUPLICATE KEY UPDATE is_active = 1, verified_at = NOW()
            ", [$user_id, $verified_by]);
            
            // Update user verification status (only if not flagged)
            $this->db->query("
                UPDATE users 
                SET verification_status = CASE 
                    WHEN verification_status != 'flagged' THEN 'verified' 
                    ELSE verification_status 
                END 
                WHERE id = ?
            ", [$user_id]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all subscription plans
     */
    public function getSubscriptionPlans() {
        return $this->db->fetchAll("
            SELECT * FROM subscription_plans 
            WHERE is_active = 1 
            ORDER BY price ASC
        ");
    }
    
    /**
     * Get payment methods
     */
    public function getPaymentMethods() {
        return $this->db->fetchAll("
            SELECT * FROM payment_methods 
            WHERE is_active = 1 
            ORDER BY name ASC
        ");
    }
    
    /**
     * Submit payment proof for manual verification
     */
    public function submitPaymentProof($user_id, $plan_id, $payment_method_id, $amount, $reference, $screenshot_path) {
        try {
            // Create pending subscription
            $subscription_id = $this->db->query("
                INSERT INTO user_subscriptions (user_id, plan_id, status, expires_at) 
                VALUES (?, ?, 'pending', DATE_ADD(CURDATE(), INTERVAL 30 DAY))
            ", [$user_id, $plan_id]);
            
            // Create payment proof with screenshot
            $this->db->query("
                INSERT INTO payment_proofs (user_id, subscription_id, payment_method_id, amount, reference_number, screenshot_url, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ", [$user_id, $subscription_id, $payment_method_id, $amount, $reference, $screenshot_path]);
            
            return $subscription_id;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Admin: Approve payment proof
     */
    public function approvePayment($payment_proof_id, $admin_id, $notes = null) {
        try {
            // Get payment proof details
            $proof = $this->db->fetch("SELECT * FROM payment_proofs WHERE id = ?", [$payment_proof_id]);
            if (!$proof) return false;
            
            // Update payment proof status
            $this->db->query("
                UPDATE payment_proofs 
                SET status = 'verified', verified_by = ?, verified_at = NOW(), admin_notes = ? 
                WHERE id = ?
            ", [$admin_id, $notes, $payment_proof_id]);
            
            // Activate subscription
            $this->verifyUserSubscription($proof['user_id'], $proof['subscription_id'], $admin_id);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Admin: Reject payment proof
     */
    public function rejectPayment($payment_proof_id, $admin_id, $reason, $is_spam = false) {
        try {
            $status = $is_spam ? 'spam' : 'rejected';
            
            // Update payment proof status
            $this->db->query("
                UPDATE payment_proofs 
                SET status = ?, verified_by = ?, verified_at = NOW(), rejection_reason = ?, is_critical_issue = ? 
                WHERE id = ?
            ", [$status, $admin_id, $reason, $is_spam, $payment_proof_id]);
            
            // If spam, flag user account
            if ($is_spam) {
                $proof = $this->db->fetch("SELECT user_id FROM payment_proofs WHERE id = ?", [$payment_proof_id]);
                $this->flagUserAccount($proof['user_id'], 'spam_payment', $reason, $admin_id);
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Flag user account for critical issues
     */
    public function flagUserAccount($user_id, $issue_type, $description, $reported_by) {
        try {
            // Add user issue record
            $this->db->query("
                INSERT INTO user_issues (user_id, issue_type, description, severity, reported_by) 
                VALUES (?, ?, ?, 'high', ?)
            ", [$user_id, $issue_type, $description, $reported_by]);
            
            // Update user flags count
            $this->db->query("
                UPDATE users 
                SET account_flags = account_flags + 1, verification_status = 'flagged' 
                WHERE id = ?
            ", [$user_id]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get pending payment proofs for admin review
     */
    public function getPendingPayments() {
        return $this->db->fetchAll("
            SELECT pp.*, us.plan_id, sp.name as plan_name, sp.price, 
                   pm.name as payment_method, pm.account_number,
                   u.first_name, u.last_name, u.email
            FROM payment_proofs pp
            JOIN user_subscriptions us ON pp.subscription_id = us.id
            JOIN subscription_plans sp ON us.plan_id = sp.id
            JOIN payment_methods pm ON pp.payment_method_id = pm.id
            JOIN users u ON pp.user_id = u.id
            WHERE pp.status = 'pending'
            ORDER BY pp.created_at ASC
        ");
    }
}
?>