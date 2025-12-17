-- Users Table with Professional Details and Authentication
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Basic Information
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    
    -- Authentication
    password_hash VARCHAR(255),
    
    -- User Type
    user_type ENUM('farmer', 'buyer', 'cooperative_member', 'supplier') NOT NULL,
    
    -- Location Information
    province VARCHAR(50),
    district VARCHAR(50),
    sector VARCHAR(50),
    cell VARCHAR(50),
    village VARCHAR(50),
    address_details TEXT,
    
    -- Farmer Specific (Simple)
    farm_size VARCHAR(20),
    what_do_you_grow TEXT,
    
    -- Simple Contact
    mobile_money_number VARCHAR(20),
    
    -- Profile Picture
    profile_picture VARCHAR(255),
    
    -- Platform Settings
    subscription_type ENUM('free', 'basic', 'premium') DEFAULT 'free',
    subscription_expires DATE,
    language_preference ENUM('kinyarwanda', 'english', 'french') DEFAULT 'kinyarwanda',
    notification_preferences JSON,
    
    -- Status and Tracking
    status ENUM('active', 'suspended', 'banned') DEFAULT 'active',
    profile_completion_percentage INT DEFAULT 0,
    last_login DATETIME,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_status (status),
    INDEX idx_location (province, district),
    INDEX idx_subscription (subscription_type, subscription_expires)
);



-- User Sessions for Security
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    device_info TEXT,
    ip_address VARCHAR(45),
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token),
    INDEX idx_user (user_id)
);

-- Simple User Profiles
CREATE TABLE user_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    profile_image VARCHAR(255),
    about_me TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample Data for Testing
INSERT INTO users (
    first_name, last_name, email, phone, user_type, 
    province, district, status, profile_completion_percentage
) VALUES 
('Jean', 'Uwimana', 'jean.uwimana@gmail.com', '+250788123456', 'farmer', 'Eastern', 'Nyagatare', 'active', 70),
('Marie', 'Mukamana', 'marie.mukamana@gmail.com', '+250788234567', 'buyer', 'Kigali City', 'Gasabo', 'active', 80),
('Paul', 'Nzeyimana', 'paul.nzeyimana@gmail.com', '+250788345678', 'cooperative_member', 'Southern', 'Huye', 'active', 60),
('Grace', 'Uwamahoro', 'grace.uwamahoro@gmail.com', '+250788456789', 'supplier', 'Western', 'Karongi', 'active', 50);