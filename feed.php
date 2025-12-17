<?php
session_start();
require_once 'config/database.php';

$db = new Database();
$user = null;

if (isset($_SESSION['user_id'])) {
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    if (!$user) {
        session_destroy();
        unset($_SESSION['user_id']);
    }
}

// Get filters
$category = $_GET['category'] ?? '';
$category_group = $_GET['category_group'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query with filters - show all products regardless of status for now
$whereClause = "1=1";
$params = [];

if ($category) {
    $whereClause .= " AND p.category = ?";
    $params[] = $category;
}

if ($category_group && $category_group !== 'all') {
    switch ($category_group) {
        case 'crops':
            $whereClause .= " AND p.category IN ('finished_crops', 'seeds')";
            break;
        case 'livestock':
            $whereClause .= " AND p.category = 'livestock'";
            break;
        case 'equipment':
            $whereClause .= " AND p.category IN ('equipment', 'tools')";
            break;
    }
}

if ($search) {
    $whereClause .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$orderClause = match($sort) {
    'price_low' => 'ORDER BY p.price ASC',
    'price_high' => 'ORDER BY p.price DESC',
    'name' => 'ORDER BY p.product_name ASC',
    default => 'ORDER BY p.created_at DESC'
};

try {
    $products = $db->fetchAll("
        SELECT p.*, u.first_name, u.last_name, u.province, u.district, u.phone, u.profile_picture 
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE $whereClause 
        $orderClause 
        LIMIT 50
    ", $params);
    
    $categories = $db->fetchAll("SELECT DISTINCT category FROM products WHERE status IN ('active', 'draft')");
    
    // Get category counts - include all statuses since products might be in draft
    $crops_count = $db->fetchColumn("SELECT COUNT(*) FROM products WHERE category IN ('finished_crops', 'seeds')");
    $livestock_count = $db->fetchColumn("SELECT COUNT(*) FROM products WHERE category = 'livestock'");
    $equipment_count = $db->fetchColumn("SELECT COUNT(*) FROM products WHERE category IN ('equipment', 'tools')");
    $all_count = $db->fetchColumn("SELECT COUNT(*) FROM products");
    
    // Get verified suppliers (users with active subscriptions)
    $suppliers = [];
    try {
        $suppliers = $db->fetchAll(
            "SELECT DISTINCT u.id, u.first_name, u.last_name, u.profile_picture, u.province, u.district,
                    sp.name as plan_name, us.expires_at
             FROM users u 
             JOIN user_subscriptions us ON u.id = us.user_id 
             LEFT JOIN subscription_plans sp ON us.plan_id = sp.id
             WHERE us.status = 'active' AND us.expires_at > NOW()
             AND u.user_type IN ('farmer', 'cooperative', 'supplier')
             ORDER BY us.created_at DESC
             LIMIT 12"
        );
        
        // Format suppliers array
        $suppliers = array_map(function($supplier) {
            return [
                'id' => $supplier['id'],
                'name' => $supplier['first_name'] . ' ' . $supplier['last_name'],
                'profile_picture' => $supplier['profile_picture'],
                'location' => ($supplier['district'] ?? '') . ', ' . ($supplier['province'] ?? ''),
                'plan_name' => $supplier['plan_name'] ?? 'Verified',
                'expires_at' => $supplier['expires_at']
            ];
        }, $suppliers);
    } catch (Exception $e) {
        $suppliers = [];
    }
} catch (PDOException $e) {
    $products = [];
    $categories = [];
    $crops_count = 0;
    $livestock_count = 0;
    $equipment_count = 0;
    $all_count = 0;
    $suppliers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }
        
        .hero-section {
            position: relative;
            height: 280px;
            margin: -1rem -1rem 2rem -1rem;
            overflow: hidden;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        }
        
        .hero-slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
        }
        
        .hero-slide.active {
            opacity: 1;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.4) 0%, rgba(5, 150, 105, 0.5) 50%, rgba(4, 120, 87, 0.6) 100%);
            z-index: 1;
        }
        
        .hero-dots {
            position: absolute;
            bottom: 15px;
            right: 20px;
            display: flex;
            gap: 6px;
            z-index: 3;
        }
        
        .hero-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .hero-dot.active {
            background: white;
            transform: scale(1.4);
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .hero-main {
            color: white;
            flex: 1;
        }
        
        .hero-title {
            font-size: clamp(1.8rem, 3.5vw, 2.4rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 0.75rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero-subtitle {
            font-size: clamp(0.9rem, 1.8vw, 1rem);
            opacity: 0.95;
            line-height: 1.5;
            max-width: 500px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        

        
        .categories-section {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(5, 150, 105, 0.1);
        }
        
        .categories-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .categories-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .categories-subtitle {
            font-size: 0.9rem;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .category-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 1.5rem;
            border-radius: 16px;
            text-decoration: none;
            color: var(--text);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: 2px solid rgba(5, 150, 105, 0.1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(5, 150, 105, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .category-card:hover::before {
            left: 100%;
        }
        
        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(5, 150, 105, 0.15);
        }
        
        /* .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .product-image {
            height: 200px;
            background: #f8fafc;
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info {
            padding: 1.25rem;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text);
        }
        
        .product-price {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .product-seller {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .seller-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            overflow: hidden;
        }
        
        .seller-info {
            flex: 1;
        }
        
        .seller-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text);
        }
        
        .seller-location {
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: var(--text);
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        } */x-shadow: 0 12px 40px rgba(5, 150, 105, 0.15);
        }
        
        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .product-image {
            height: 200px;
            background: #f8fafc;
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info {
            padding: 1.25rem;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text);
        }
        
        .product-price {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .product-seller {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .seller-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            overflow: hidden;
        }
        
        .seller-info {
            flex: 1;
        }
        
        .seller-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text);
        }
        
        .seller-location {
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: var(--text);
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }x-shadow: 0 12px 40px rgba(5, 150, 105, 0.15);
            border-color: rgba(5, 150, 105, 0.3);
        }
        
        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .category-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .category-count {
            font-size: 0.85rem;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(5, 150, 105, 0.1);
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            height: 200px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            position: relative;
            overflow: hidden;
            border-radius: 16px 16px 0 0;
            margin: 0;
            padding: 0;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }
        
        .product-info {
            padding: 1.25rem;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: 800;
            color: #10b981;
            margin-bottom: 0.75rem;
        }
        
        .product-seller {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .seller-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .seller-info {
            flex: 1;
        }
        
        .seller-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text);
        }
        
        .seller-location {
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .btn-secondary {
            background: rgba(5, 150, 105, 0.1);
            color: #059669;
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }x-shadow: 0 20px 40px rgba(5, 150, 105, 0.15);
            border-color: var(--primary);
        }
        
        .category-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .category-card:hover .category-icon {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3);
        }
        
        .category-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
            letter-spacing: 0.3px;
        }
        
        .category-count {
            font-size: 0.8rem;
            color: var(--text-light);
            font-weight: 600;
            background: rgba(5, 150, 105, 0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
        }
        
        .category-description {
            font-size: 0.75rem;
            color: var(--text-light);
            line-height: 1.4;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        
        .category-card:hover .category-description {
            opacity: 1;
            transform: translateY(0);
        }
        
        .category-card.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(5, 150, 105, 0.3);
            border-color: var(--primary);
        }
        
        .category-card.active .category-name,
        .category-card.active .category-count,
        .category-card.active .category-description {
            color: white;
        }
        
        .category-card.active .category-icon {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .category-card.active .category-count {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .main-content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .left-column {
            order: 1;
        }
        
        .right-column {
            order: 2;
        }
        
        .content-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(5, 150, 105, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .content-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(5, 150, 105, 0.1);
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-content {
            padding: 2rem;
        }
        
        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .featured-card {
            background: white;
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .featured-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
            border-color: var(--primary);
        }
        
        .featured-image-container {
            width: 100%;
            height: 150px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        .featured-content {
            padding: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .featured-info {
            flex: 1;
            margin-bottom: 1rem;
        }
        
        .featured-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }
        
        .featured-price {
            color: var(--primary);
            font-weight: 800;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }
        
        .featured-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: rgba(100, 116, 139, 0.08);
            border-radius: 8px;
        }
        
        .featured-seller {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: rgba(5, 150, 105, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(5, 150, 105, 0.1);
            margin-bottom: 1rem;
        }
        
        .seller-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .seller-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text);
        }
        
        .featured-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn-contact,
        .btn-view {
            flex: 1;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-contact {
            background: var(--primary);
            color: white;
        }
        
        .btn-contact:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-view {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-view:hover {
            background: var(--primary);
            color: white;
        }
        
        .featured-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .featured-card:hover .featured-image {
            transform: scale(1.08);
        }
        
        .featured-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--text-light);
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        .featured-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: capitalize;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .featured-info {
            flex: 1;
        }
        
        .featured-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.75rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .featured-price {
            color: var(--primary);
            font-weight: 800;
            font-size: 1.125rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .featured-location {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 0.75rem;
            padding: 0.25rem 0.5rem;
            background: rgba(100, 116, 139, 0.1);
            border-radius: 6px;
            width: fit-content;
        }
        
        .featured-seller {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            background: rgba(5, 150, 105, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(5, 150, 105, 0.1);
        }
        
        .seller-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.8rem;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
        }
        
        .seller-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .seller-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text);
        }
        
        .view-all {
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid rgba(5, 150, 105, 0.2);
        }
        
        .view-all:hover {
            color: white;
            background: var(--primary);
            border-color: var(--primary);
            transform: translateY(-1px);
        }
        
        .suppliers-container {
            background: transparent;
            padding: 0;
        }
        
        .suppliers-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .supplier-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: white;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: 2px solid rgba(5, 150, 105, 0.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .supplier-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(5, 150, 105, 0.05), transparent);
            transition: left 0.5s ease;
        }
        
        .supplier-item:hover::before {
            left: 100%;
        }
        
        .supplier-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(5, 150, 105, 0.15);
            border-color: var(--primary);
        }
        
        .supplier-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
            border: 3px solid white;
        }
        
        .supplier-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .supplier-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .supplier-name {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text);
            margin: 0;
        }
        
        .supplier-meta {
            font-size: 0.8rem;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .verified-badge {
            width: 20px;
            height: 20px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
        }
        
        .verified-icon {
            width: 12px;
            height: 12px;
            fill: white;
        }
        

        
        @media (max-width: 768px) {
            .container {
                padding: 0.5rem;
            }
            
            .hero-section {
                height: 200px;
                margin: -0.5rem -0.5rem 1.5rem -0.5rem;
            }
            
            .hero-content {
                text-align: center;
                padding: 1rem;
                justify-content: center;
            }
            

            
            .hero-dots {
                bottom: 15px;
            }
            
            .hero-dot {
                width: 6px;
                height: 6px;
            }
            
            .categories-section {
                padding: 0.75rem;
                margin: -0.5rem -0.5rem 1.5rem -0.5rem;
                border-radius: 0;
            }
            
            .categories-header {
                margin-bottom: 1rem;
            }
            
            .categories-title {
                font-size: 1.2rem;
            }
            
            .categories-subtitle {
                font-size: 0.8rem;
            }
            
            .categories-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }
            
            .category-card {
                padding: 0.6rem 0.4rem;
                min-height: 80px;
                border-radius: 10px;
            }
            
            .category-card:hover {
                transform: translateY(-2px);
            }
            
            .category-icon {
                width: 30px;
                height: 30px;
                font-size: 1.1rem;
                margin-bottom: 0.4rem;
                border-radius: 10px;
            }
            
            .category-name {
                font-size: 0.8rem;
                margin-bottom: 0.25rem;
                font-weight: 600;
            }
            
            .category-count {
                font-size: 0.65rem;
                padding: 0.15rem 0.4rem;
                border-radius: 8px;
            }
            
            .category-description {
                display: none;
            }
            
            .main-content-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .left-column {
                order: 2;
            }
            
            .right-column {
                order: 1;
            }
            
            .featured-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .featured-image-container {
                height: 160px;
            }
            
            .featured-content {
                padding: 1rem;
            }
            
            .featured-title {
                font-size: 0.95rem;
                margin-bottom: 0.5rem;
            }
            
            .featured-price {
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
            }
            
            .featured-location {
                font-size: 0.75rem;
                padding: 0.4rem;
                margin-bottom: 0.75rem;
            }
            
            .featured-seller {
                padding: 0.5rem;
                margin-bottom: 0.75rem;
            }
            
            .seller-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }
            
            .seller-name {
                font-size: 0.8rem;
            }
            
            .featured-actions {
                gap: 0.5rem;
            }
            
            .btn-contact,
            .btn-view {
                padding: 0.6rem 0.8rem;
                font-size: 0.7rem;
                border-radius: 8px;
            }
            
            .content-card {
                margin-bottom: 1rem;
            }
            
            .card-header {
                padding: 1rem;
            }
            
            .card-title {
                font-size: 1rem;
            }
            
            .card-content {
                padding: 1rem;
            }
            
            .suppliers-list {
                display: flex;
                gap: 0.75rem;
                overflow-x: auto;
                padding-bottom: 0.5rem;
                -webkit-overflow-scrolling: touch;
            }
            
            .supplier-item {
                flex: 0 0 auto;
                width: 85px;
                flex-direction: column;
                text-align: center;
                padding: 0.75rem 0.5rem;
                gap: 0.5rem;
                min-height: auto;
                border-radius: 12px;
            }
            
            .supplier-avatar {
                width: 50px;
                height: 50px;
                margin: 0 auto;
                font-size: 1rem;
            }
            
            .supplier-info {
                flex: none;
            }
            
            .supplier-name {
                font-size: 0.7rem;
                line-height: 1.2;
                margin: 0;
                font-weight: 600;
            }
            
            .verified-badge {
                position: static;
                margin: 0.25rem auto 0;
                width: 14px;
                height: 14px;
            }
            
            .verified-icon {
                width: 8px;
                height: 8px;
            }
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
    </style>
</head>
<body>
    <?php 
    $current_user = $user; // Make user available to header
    include 'includes/header.php';
    ?>
    
    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-slider">
                <div class="hero-slide active" style="background-image: url('images/hero_slider/hero1.jpg')"></div>
                <div class="hero-slide" style="background-image: url('images/hero_slider/hero2.jpg')"></div>
                <div class="hero-slide" style="background-image: url('images/hero_slider/hero3.jpg')"></div>
                <div class="hero-slide" style="background-image: url('images/hero_slider/hero4.jpg')"></div>
                <div class="hero-slide" style="background-image: url('images/hero_slider/hero5.jpg')"></div>
            </div>
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <div class="hero-main">
                    <h1 class="hero-title">Welcome to Rwanda's Premier Agricultural Marketplace</h1>

                    </div>
                </div>
            </div>
            
            <div class="hero-dots">
                <div class="hero-dot active" data-slide="0"></div>
                <div class="hero-dot" data-slide="1"></div>
                <div class="hero-dot" data-slide="2"></div>
                <div class="hero-dot" data-slide="3"></div>
                <div class="hero-dot" data-slide="4"></div>
            </div>
        </div>
        
        <!-- Categories Section -->
        <div class="categories-section">
            <div class="categories-header">
                <h2 class="categories-title">Shop by Category</h2>
                <p class="categories-subtitle">Discover quality agricultural products from trusted local suppliers</p>
            </div>
            
            <div class="categories-grid">
                <a href="#" data-category="all" class="category-card <?= (!$category_group || $category_group === 'all') ? 'active' : '' ?>">
                    <div class="category-icon">🌍</div>
                    <div class="category-name">All Products</div>
                    <div class="category-count"><?= $all_count ?> items</div>
                    <div class="category-description">Browse our complete marketplace collection</div>
                </a>
                
                <a href="#" data-category="crops" class="category-card <?= $category_group === 'crops' ? 'active' : '' ?>">
                    <div class="category-icon">🌾</div>
                    <div class="category-name">Fresh Crops</div>
                    <div class="category-count"><?= $crops_count ?> items</div>
                    <div class="category-description">Farm-fresh produce and quality seeds</div>
                </a>
                
                <a href="#" data-category="livestock" class="category-card <?= $category_group === 'livestock' ? 'active' : '' ?>">
                    <div class="category-icon">🐄</div>
                    <div class="category-name">Livestock</div>
                    <div class="category-count"><?= $livestock_count ?> items</div>
                    <div class="category-description">Healthy animals from certified farms</div>
                </a>
                
                <a href="#" data-category="equipment" class="category-card <?= $category_group === 'equipment' ? 'active' : '' ?>">
                    <div class="category-icon">🚜</div>
                    <div class="category-name">Equipment</div>
                    <div class="category-count"><?= $equipment_count ?> items</div>
                    <div class="category-description">Modern farming tools and machinery</div>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content-grid">
            <!-- Left Column - Featured Products -->
            <div class="left-column">
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">🌟 Featured Products</h3>
                    </div>
                    <div class="card-content">
                        <div class="featured-grid">
                            <?php foreach (array_slice($products, 0, 4) as $product): ?>
                                <div class="featured-card" onclick="viewProduct(<?= $product['id'] ?>)">
                                    <div class="featured-image-container">
                                        <?php if ($product['main_image']): ?>
                                            <img src="/curuzamuhinzi/uploads/products/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="featured-image">
                                        <?php else: ?>
                                            <div class="featured-placeholder">🌾</div>
                                        <?php endif; ?>
                                        <div class="featured-badge"><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></div>
                                    </div>
                                    
                                    <div class="featured-content">
                                        <div class="featured-info">
                                            <h4 class="featured-title"><?= htmlspecialchars($product['product_name']) ?></h4>
                                            <div class="featured-price"><?= number_format($product['price']) ?> RWF</div>
                                            
                                            <div class="featured-location">
                                                <span>📍</span>
                                                <?= htmlspecialchars($product['district'] . ', ' . $product['province']) ?>
                                            </div>
                                            
                                            <div class="featured-seller">
                                                <div class="seller-avatar">
                                                    <?php if ($product['profile_picture']): ?>
                                                        <img src="/curuzamuhinzi/uploads/profiles/<?= htmlspecialchars($product['profile_picture']) ?>" alt="<?= htmlspecialchars($product['first_name']) ?>">
                                                    <?php else: ?>
                                                        <?= strtoupper(substr($product['first_name'], 0, 1)) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="seller-name"><?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?></div>
                                                <div class="verified-badge">
                                                    <svg class="verified-icon" viewBox="0 0 24 24">
                                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="featured-actions">
                                        <button class="btn-contact" onclick="event.stopPropagation(); contactSeller(<?= $product['user_id'] ?>)">Contact</button>
                                        <button class="btn-view" onclick="event.stopPropagation(); viewProduct(<?= $product['id'] ?>)">View</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="text-align: center; margin-top: 1.5rem;">
                            <button onclick="window.location.href='products.php'" style="background: var(--primary); color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">View All Products</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Verified Suppliers -->
            <div class="right-column">
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">🛡️ Verified & Trusted Suppliers</h3>
                    </div>
                    <div class="card-content">
                        <div class="suppliers-container">
                            <div class="suppliers-list">
                                <?php if (empty($suppliers)): ?>
                                    <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                        <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                                        <p>No verified suppliers found yet.</p>
                                        <p style="font-size: 0.9rem;">Suppliers need active subscriptions to appear here.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach (array_slice($suppliers, 0, 6) as $supplier): ?>
                                        <div class="supplier-item" onclick="window.location.href='profile.php?user=<?= $supplier['id'] ?>'">
                                            <div class="supplier-avatar">
                                                <?php if ($supplier['profile_picture']): ?>
                                                    <img src="/curuzamuhinzi/uploads/profiles/<?= htmlspecialchars($supplier['profile_picture']) ?>" alt="<?= htmlspecialchars($supplier['name']) ?>">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($supplier['name'], 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="supplier-info">
                                                <h4 class="supplier-name"><?= htmlspecialchars($supplier['name']) ?></h4>
                                                <div class="supplier-meta"><?= htmlspecialchars($supplier['plan_name']) ?> • <?= htmlspecialchars($supplier['location']) ?></div>
                                            </div>
                                            <div class="verified-badge">
                                                <svg class="verified-icon" viewBox="0 0 24 24">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="text-align: center; margin-top: 1.5rem;">
                            <button onclick="window.location.href='suppliers.php'" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: white; border: none; padding: 0.8rem 2rem; border-radius: 25px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.5px;">View All Suppliers</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include 'feed_content.php'; ?>
        
    </div>
    
    <?php include 'includes/bottom-nav.php'; ?>
    
    <script>
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    
    function updateCartCount() {
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);
        const countEl = document.getElementById('cartCount');
        if (count > 0) {
            countEl.textContent = count;
            countEl.style.display = 'flex';
        } else {
            countEl.style.display = 'none';
        }
    }
    
    function addToCart(productId) {
        const existing = cart.find(item => item.id === productId);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({ id: productId, quantity: 1 });
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        
        // Show feedback
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = 'Added!';
        btn.style.background = 'var(--success)';
        setTimeout(() => {
            btn.textContent = originalText;
            btn.style.background = 'var(--primary)';
        }, 1000);
    }
    
    function toggleWishlist(productId) {
        const btn = event.target;
        const index = wishlist.indexOf(productId);
        if (index > -1) {
            wishlist.splice(index, 1);
            btn.textContent = '♡';
        } else {
            wishlist.push(productId);
            btn.textContent = '♥';
        }
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
    }
    
    function viewProduct(productId) {
        window.location.href = `product-detail.php?id=${productId}`;
    }
    
    function contactSeller(userId) {
        // Implement contact functionality
        window.location.href = `contact.php?seller=${userId}`;
    }
    

    
    function toggleCart() {
        // Implement cart modal or redirect to cart page
        window.location.href = 'cart.php';
    }
    
    // Category filtering without page refresh
    function filterByCategory(category) {
        const productsGrid = document.getElementById('productsContainer');
        const categoryCards = document.querySelectorAll('.category-card');
        
        // Update active state
        categoryCards.forEach(card => card.classList.remove('active'));
        document.querySelector(`[data-category="${category}"]`).classList.add('active');
        
        // Add loading state
        productsGrid.classList.add('loading');
        
        // Fetch filtered products
        fetch(`feed-ajax.php?category_group=${category}`)
            .then(response => response.text())
            .then(html => {
                setTimeout(() => {
                    productsGrid.innerHTML = html;
                    productsGrid.classList.remove('loading');
                    productsGrid.classList.add('fade-in');
                    setTimeout(() => productsGrid.classList.remove('fade-in'), 400);
                }, 200);
            })
            .catch(error => {
                console.error('Error:', error);
                productsGrid.classList.remove('loading');
            });
    }
    
    // Hero Search
    function performSearch() {
        const searchTerm = document.getElementById('heroSearch').value;
        if (searchTerm.trim()) {
            window.location.href = `feed.php?search=${encodeURIComponent(searchTerm)}`;
        }
    }
    
    // Enter key search
    document.getElementById('heroSearch')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
    
    // Hero Slider
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    
    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
        currentSlide = index;
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    // Auto-play slider
    setInterval(nextSlide, 4000);
    
    // Manual controls
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => showSlide(index));
    });
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updateCartCount();
        
        // Update wishlist buttons
        wishlist.forEach(productId => {
            const btn = document.querySelector(`[onclick*="toggleWishlist(${productId})"]`);
            if (btn) btn.textContent = '♥';
        });
        
        // Category click handlers
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', function(e) {
                e.preventDefault();
                const category = this.dataset.category;
                filterByCategory(category);
            });
        });
        

    });
    </script>
</body>
</html>