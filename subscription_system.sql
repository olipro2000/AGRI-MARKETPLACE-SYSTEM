-- SUBSCRIPTION SYSTEM TABLES
-- Create these tables to manage subscriptions separately from users table

-- Subscription Plans Table
CREATE TABLE subscription_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'RWF',
    duration_days INT NOT NULL,
    features JSON,
    max_products INT DEFAULT NULL, -- NULL = unlimited
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_active (is_active),
    INDEX idx_price (price)
);

-- User Subscriptions Table
CREATE TABLE user_subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    status ENUM('pending', 'active', 'expired', 'cancelled') DEFAULT 'pending',
    starts_at DATE,
    expires_at DATE,
    auto_renew BOOLEAN DEFAULT FALSE,
    payment_reference VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_expires_at (expires_at),
    INDEX idx_status (status)
);

-- Payment Methods Table
CREATE TABLE payment_methods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('mobile_money', 'bank_transfer', 'cash') NOT NULL,
    account_number VARCHAR(100),
    account_name VARCHAR(255),
    instructions TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_active (is_active)
);

-- Payment Proofs Table
CREATE TABLE payment_proofs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    subscription_id INT NOT NULL,
    payment_method_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    reference_number VARCHAR(100),
    screenshot_url VARCHAR(500) NOT NULL,
    status ENUM('pending', 'verified', 'rejected', 'spam') DEFAULT 'pending',
    verified_by INT NULL,
    verified_at TIMESTAMP NULL,
    rejection_reason TEXT,
    admin_notes TEXT,
    is_critical_issue BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES user_subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id),
    FOREIGN KEY (verified_by) REFERENCES users(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_status (status),
    INDEX idx_critical (is_critical_issue)
);

-- User Verification Status Table (for verified badges)
CREATE TABLE user_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    verification_type ENUM('subscription_verified', 'admin_verified', 'community_verified') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    verified_by INT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id),
    UNIQUE KEY unique_user_verification (user_id, verification_type),
    INDEX idx_user_active (user_id, is_active)
);

-- Insert Default Subscription Plans
INSERT INTO subscription_plans (name, description, price, duration_days, max_products, features) VALUES
('Free', 'Browse products only - No posting allowed', 0.00, 0, 0, '["Browse products", "Contact sellers", "Basic profile"]'),
('Basic', 'Post up to 10 products per month', 5000.00, 30, 10, '["Post 10 products/month", "Basic analytics", "Verified badge", "Priority support"]'),
('Premium', 'Unlimited product posting with advanced features', 15000.00, 30, NULL, '["Unlimited products", "Advanced analytics", "Featured listings", "Verified badge", "Priority support", "Marketing tools"]');

-- Insert Default Payment Methods
INSERT INTO payment_methods (name, type, account_number, account_name, instructions) VALUES
('MTN Mobile Money', 'mobile_money', '0788123456', 'Curuza Muhinzi', 'Send payment to MTN Mobile Money number and upload screenshot'),
('Airtel Money', 'mobile_money', '0733123456', 'Curuza Muhinzi', 'Send payment to Airtel Money number and upload screenshot'),
('MOMO Code', 'mobile_money', 'MOMO123', 'Curuza Muhinzi', 'Use MOMO Code to send payment and upload screenshot'),
('Bank of Kigali', 'bank_transfer', '00012345678', 'Curuza Muhinzi Ltd', 'Transfer to BK account and upload bank slip');

-- User Issues Tracking Table (for spam/critical issues)
CREATE TABLE user_issues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    issue_type ENUM('spam_payment', 'fake_screenshot', 'multiple_accounts', 'fraudulent_activity', 'other') NOT NULL,
    description TEXT NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('open', 'investigating', 'resolved', 'closed') DEFAULT 'open',
    reported_by INT NOT NULL,
    resolved_by INT NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_severity (severity),
    INDEX idx_status (status)
);

-- Update Users Table - Remove subscription columns and add verification status
ALTER TABLE users 
DROP COLUMN subscription_type,
DROP COLUMN subscription_expires,
ADD COLUMN verification_status ENUM('unverified', 'verified', 'flagged') DEFAULT 'unverified',
ADD COLUMN account_flags INT DEFAULT 0,
ADD INDEX idx_verification (verification_status),
ADD INDEX idx_flags (account_flags);