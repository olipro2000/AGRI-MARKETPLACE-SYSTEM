-- Professional Products Table for Agricultural Platform
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    
    -- REQUIRED: Basic Product Information
    product_name VARCHAR(150) NOT NULL,
    category ENUM('finished_crops', 'seeds', 'livestock', 'equipment', 'fertilizers', 'pesticides', 'tools', 'services') NOT NULL,
    product_type ENUM('cassava', 'rice', 'maize', 'beans', 'irish_potatoes', 'sweet_potatoes', 'bananas', 'tomatoes', 'onions', 'carrots', 'cabbage', 'wheat', 'sorghum', 'millet', 'groundnuts', 'soybeans', 'coffee', 'tea', 'avocado', 'passion_fruit', 'pineapple', 'watermelon', 'cucumber', 'pepper', 'eggplant', 'spinach', 'lettuce', 'cow', 'goat', 'sheep', 'pig', 'chicken', 'duck', 'rabbit', 'fish', 'tractor', 'plough', 'hoe', 'sprayer', 'harvester', 'irrigation_system', 'greenhouse', 'storage_facility', 'npk_fertilizer', 'urea', 'dap', 'organic_fertilizer', 'compost', 'manure', 'insecticide', 'herbicide', 'fungicide', 'hand_tools', 'farm_consultation', 'transport_service', 'processing_service', 'other') NOT NULL,
    
    -- REQUIRED: Pricing and Quantity (RWF - Rwandan Francs)
    price DECIMAL(12,2) NOT NULL COMMENT 'Price in Rwandan Francs (RWF)',
    unit ENUM('kg', 'ton', 'bag_50kg', 'bag_25kg', 'piece', 'liter', 'hectare', 'hour', 'day', 'month', 'service') NOT NULL DEFAULT 'kg',
    quantity_available INT NOT NULL DEFAULT 1,
    
    -- OPTIONAL: Additional Details
    description TEXT,
    quality_grade ENUM('premium', 'standard', 'basic') DEFAULT 'standard',
    organic_certified BOOLEAN DEFAULT FALSE,
    
    -- OPTIONAL: Images (Free for all users)
    main_image VARCHAR(255),
    image_2 VARCHAR(255),
    image_3 VARCHAR(255),
    image_4 VARCHAR(255),
    
    -- OPTIONAL: Availability and Timing
    harvest_season ENUM('dry_season', 'rainy_season', 'year_round') DEFAULT 'year_round',
    available_from DATE,
    available_until DATE,
    
    -- OPTIONAL: Location Specifics
    province VARCHAR(50),
    district VARCHAR(50),
    
    -- OPTIONAL: Contact Preferences
    preferred_contact ENUM('phone', 'whatsapp', 'visit_farm', 'any') DEFAULT 'any',
    delivery_available BOOLEAN DEFAULT FALSE,
    pickup_available BOOLEAN DEFAULT TRUE,
    
    -- System Fields
    status ENUM('active', 'sold_out', 'seasonal_break', 'draft') DEFAULT 'active',
    views_count INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes for Performance
    INDEX idx_user (user_id),
    INDEX idx_category (category),
    INDEX idx_product_type (product_type),
    INDEX idx_status (status),
    INDEX idx_location (province, district),
    INDEX idx_price_range (price, unit),
    INDEX idx_availability (available_from, available_until),
    INDEX idx_featured (featured, status)
);
