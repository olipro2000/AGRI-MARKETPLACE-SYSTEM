<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$db = new Database();
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

if (!$user) {
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

$errors = [];
$success = '';

if ($_POST) {
    // Handle profile image removal
    if (isset($_POST['remove_image'])) {
        if ($user['profile_picture'] && file_exists('../uploads/profiles/' . $user['profile_picture'])) {
            unlink('../uploads/profiles/' . $user['profile_picture']);
        }
        $db->query("UPDATE users SET profile_picture = NULL WHERE id = ?", [$_SESSION['user_id']]);
        $user['profile_picture'] = null;
        $success = 'Profile photo removed successfully!';
    }
    // Handle profile image upload
    elseif (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['profile_image']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($extension, $allowedTypes)) {
            if ($_FILES['profile_image']['size'] <= 5 * 1024 * 1024) { // 5MB limit
                $fileName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                    // Delete old profile image if exists
                    if ($user['profile_picture'] && file_exists($uploadDir . $user['profile_picture'])) {
                        unlink($uploadDir . $user['profile_picture']);
                    }
                    
                    $db->query("UPDATE users SET profile_picture = ? WHERE id = ?", [$fileName, $_SESSION['user_id']]);
                    $user['profile_picture'] = $fileName;
                    $success = 'Profile image updated successfully!';
                } else {
                    $errors[] = 'Failed to upload image.';
                }
            } else {
                $errors[] = 'Image size must be less than 5MB.';
            }
        } else {
            $errors[] = 'Only JPG, JPEG, PNG, and GIF images are allowed.';
        }
    }
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'province' => $_POST['province'] ?? '',
        'district' => trim($_POST['district'] ?? ''),
        'sector' => trim($_POST['sector'] ?? ''),
        'cell' => trim($_POST['cell'] ?? ''),
        'village' => trim($_POST['village'] ?? ''),
        'farm_size' => $_POST['farm_size'] ?? '',
        'what_do_you_grow' => trim($_POST['what_do_you_grow'] ?? ''),
        'mobile_money_number' => trim($_POST['mobile_money_number'] ?? ''),
        'language_preference' => $_POST['language_preference'] ?? 'kinyarwanda'
    ];
    
    if (empty($data['first_name'])) $errors[] = 'First name is required';
    if (empty($data['last_name'])) $errors[] = 'Last name is required';
    
    if (empty($errors)) {
        try {
            $completion = 50;
            if (!empty($data['province'])) $completion += 10;
            if (!empty($data['district'])) $completion += 10;
            if (!empty($data['farm_size'])) $completion += 10;
            if (!empty($data['what_do_you_grow'])) $completion += 10;
            if (!empty($data['mobile_money_number'])) $completion += 10;
            
            $db->query(
                "UPDATE users SET first_name = ?, last_name = ?, phone = ?, province = ?, district = ?, sector = ?, cell = ?, village = ?, farm_size = ?, what_do_you_grow = ?, mobile_money_number = ?, language_preference = ?, profile_completion_percentage = ? WHERE id = ?",
                [
                    $data['first_name'], $data['last_name'], $data['phone'], 
                    $data['province'] ?: null, $data['district'] ?: null, $data['sector'] ?: null,
                    $data['cell'] ?: null, $data['village'] ?: null, $data['farm_size'] ?: null,
                    $data['what_do_you_grow'] ?: null, $data['mobile_money_number'] ?: null,
                    $data['language_preference'], $completion, $_SESSION['user_id']
                ]
            );
            
            $success = 'Profile updated successfully!';
            $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        } catch (Exception $e) {
            $errors[] = "Failed to update profile. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> - Curuza Muhinzi</title>
    <?php include '../includes/styles.php'; ?>
<style>
.profile-form {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.form-section {
    margin-bottom: 2rem;
}

.form-section h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 1rem;
    border-bottom: 2px solid var(--primary);
    padding-bottom: 0.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text);
    font-size: 0.875rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
    background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

.form-group textarea {
    min-height: 80px;
    resize: vertical;
}

.card-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.9rem;
    margin-top: 0.25rem;
    background: white;
}

.card-input:focus {
    outline: none;
    border-color: var(--primary);
}

.form-actions {
    padding: 1rem;
    text-align: center;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.alert.success {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.alert.error {
    background: rgba(239, 68, 68, 0.1);
    color: var(--error);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.logout-btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #dc3545;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: background 0.2s;
}

.logout-btn:hover {
    background: #c82333;
    color: white;
}

.desktop-logout {
    margin-top: 1rem;
    border-top: 1px solid var(--border);
    padding-top: 1rem;
}

.image-popup {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.popup-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.popup-content {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
    min-width: 280px;
    text-align: center;
}

.popup-content h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
}

.popup-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.popup-btn {
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.popup-btn.view {
    background: var(--primary);
    color: white;
}

.popup-btn.change {
    background: var(--accent);
    color: white;
}

.popup-btn.remove {
    background: var(--error);
    color: white;
}

.popup-btn.cancel {
    background: var(--bg-alt);
    color: var(--text);
    border: 1px solid var(--border);
}

.popup-btn:hover {
    transform: translateY(-1px);
    opacity: 0.9;
}

.image-viewer {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.viewer-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.viewer-content img {
    width: 100%;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.close-viewer {
    position: absolute;
    top: -40px;
    right: 0;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
    backdrop-filter: blur(10px);
}

@media (max-width: 480px) {
    .popup-content {
        margin: 1rem;
        padding: 1.5rem;
        min-width: auto;
        width: calc(100% - 2rem);
    }
    
    .popup-btn {
        padding: 0.625rem 0.75rem;
        font-size: 0.875rem;
    }
}



/* Tablet Styles (768px - 1024px) */
@media (min-width: 769px) and (max-width: 1024px) {
    .desktop-content {
        grid-template-columns: 1fr;
        padding: 1.5rem;
        gap: 1.5rem;
    }
    
    .profile-banner {
        height: 180px;
    }
    
    .profile-avatar-desktop {
        width: 100px;
        height: 100px;
        font-size: 2.5rem;
    }
    
    .profile-header-desktop {
        padding: 1.5rem;
    }
    
    .name-section h1 {
        font-size: 2rem;
    }
    
    .form-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .profile-form {
        padding: 1.5rem;
    }
    
    .form-section h3 {
        font-size: 1.1rem;
    }
}

/* Mobile Styles (max-width: 768px) */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .profile-form {
        padding: 1rem;
    }
    
    .form-section {
        margin-bottom: 1.5rem;
    }
    
    .form-section h3 {
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .form-group {
        margin-bottom: 0.75rem;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 0.625rem;
        font-size: 0.9rem;
    }
    
    .alert {
        padding: 0.75rem;
        font-size: 0.875rem;
    }
    
    .mobile-content {
        padding: 0 0.5rem;
        gap: 0.5rem;
    }
    
    .content-section {
        padding: 1rem;
    }
    
    .section-title h3 {
        font-size: 1rem;
    }
    
    .action-btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
    
    .mobile-logout {
        margin-top: 2rem;
        border-top: 1px solid var(--border);
        padding-top: 1rem;
    }
    
    .logout-btn {
        display: block;
        width: 100%;
        padding: 0.75rem;
        background: #dc3545;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
        transition: background 0.2s;
    }
    
    .logout-btn:hover {
        background: #c82333;
        color: white;
    }
}



/* Small Mobile Styles (max-width: 480px) */
@media (max-width: 480px) {
    .profile-form {
        padding: 0.75rem;
    }
    
    .form-section h3 {
        font-size: 0.95rem;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-group label {
        font-size: 0.8rem;
    }
    
    .content-section {
        padding: 0.75rem;
    }
    
    .mobile-content {
        padding: 0 0.25rem;
    }
    
    .cover-section {
        height: 140px;
    }
    
    .banner-name {
        font-size: 1.5rem;
    }
    
    .profile-pic {
        width: 80px;
        height: 80px;
        font-size: 2rem;
    }
    
    .profile-section {
        bottom: -40px;
    }
    
    .action-buttons {
        padding: 40px 10px 10px;
    }
    

}
</style>
</head>
<body class="profile-body">
    
    <!-- Desktop Profile -->
    <div class="desktop-profile">
        <div class="profile-banner">
            <div class="banner-overlay"></div>
            <div class="profile-header-desktop">
                <div class="profile-main-info">
                    <div class="profile-avatar-desktop" onclick="showImageOptions()" style="cursor: pointer;" title="Click for photo options">
                        <?php if ($user['profile_picture'] && file_exists('../uploads/profiles/' . $user['profile_picture'])): ?>
                            <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile">
                        <?php else: ?>
                            <span><?= strtoupper(substr($user['first_name'], 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <div class="name-section">
                            <h1><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
                        </div>
                        <p class="role-badge"><?= ucfirst(str_replace('_', ' ', $user['user_type'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="desktop-content">
            <div class="main-content">
                <?php if ($success): ?>
                    <div class="alert success">✅ <?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert error">
                        <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-form">
                <form method="POST" enctype="multipart/form-data">
            <div class="form-section">
                <h3>📝 Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Email Address</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="+250 788 123 456">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>📍 Location Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Province</label>
                        <select name="province">
                            <option value="">Select province</option>
                            <option value="Kigali City" <?= $user['province'] === 'Kigali City' ? 'selected' : '' ?>>Kigali City</option>
                            <option value="Eastern" <?= $user['province'] === 'Eastern' ? 'selected' : '' ?>>Eastern Province</option>
                            <option value="Western" <?= $user['province'] === 'Western' ? 'selected' : '' ?>>Western Province</option>
                            <option value="Northern" <?= $user['province'] === 'Northern' ? 'selected' : '' ?>>Northern Province</option>
                            <option value="Southern" <?= $user['province'] === 'Southern' ? 'selected' : '' ?>>Southern Province</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>District</label>
                        <input type="text" name="district" value="<?= htmlspecialchars($user['district']) ?>" placeholder="e.g., Gasabo">
                    </div>
                    <div class="form-group">
                        <label>Sector</label>
                        <input type="text" name="sector" value="<?= htmlspecialchars($user['sector']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Cell</label>
                        <input type="text" name="cell" value="<?= htmlspecialchars($user['cell']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Village</label>
                        <input type="text" name="village" value="<?= htmlspecialchars($user['village']) ?>">
                    </div>
                </div>
            </div>
            
            <?php if (in_array($user['user_type'], ['farmer', 'cooperative'])): ?>
            <div class="form-section">
                <h3>🌾 Farming Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Farm Size</label>
                        <select name="farm_size">
                            <option value="">Select farm size</option>
                            <option value="Small (< 1 hectare)" <?= $user['farm_size'] === 'Small (< 1 hectare)' ? 'selected' : '' ?>>Small (< 1 hectare)</option>
                            <option value="Medium (1-5 hectares)" <?= $user['farm_size'] === 'Medium (1-5 hectares)' ? 'selected' : '' ?>>Medium (1-5 hectares)</option>
                            <option value="Large (> 5 hectares)" <?= $user['farm_size'] === 'Large (> 5 hectares)' ? 'selected' : '' ?>>Large (> 5 hectares)</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label>What do you grow?</label>
                        <textarea name="what_do_you_grow" placeholder="Describe the crops you grow or animals you raise"><?= htmlspecialchars($user['what_do_you_grow']) ?></textarea>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-section">
                <h3>⚙️ Platform Settings</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Mobile Money Number</label>
                        <input type="tel" name="mobile_money_number" value="<?= htmlspecialchars($user['mobile_money_number']) ?>" placeholder="+250 788 123 456">
                    </div>
                    <div class="form-group">
                        <label>Language Preference</label>
                        <select name="language_preference">
                            <option value="kinyarwanda" <?= $user['language_preference'] === 'kinyarwanda' ? 'selected' : '' ?>>Kinyarwanda</option>
                            <option value="english" <?= $user['language_preference'] === 'english' ? 'selected' : '' ?>>English</option>
                            <option value="french" <?= $user['language_preference'] === 'french' ? 'selected' : '' ?>>French</option>
                        </select>
                    </div>
                </div>
            </div>
            
                    <input type="file" name="profile_image" id="profileImageUpload" accept="image/*" style="display: none;" onchange="this.form.submit()">
                    <button type="submit" class="btn-primary">💾 Update Profile</button>
                </form>
                
                <!-- Desktop Logout Section -->
                <div class="form-section desktop-logout">
                    <h3>🚪 Account Actions</h3>
                    <a href="../auth/login.php?logout=1" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a>
                </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Profile -->
    <div class="mobile-profile">
        <div class="mobile-header">
            <div class="cover-section">
                <div class="cover-bg"></div>
                <div class="banner-content">
                    <h1 class="banner-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
                </div>
                <div class="profile-section">
                    <div class="profile-pic" onclick="showImageOptions()" style="cursor: pointer;" title="Tap for photo options">
                        <?php if ($user['profile_picture'] && file_exists('../uploads/profiles/' . $user['profile_picture'])): ?>
                            <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile">
                        <?php else: ?>
                            <span><?= strtoupper(substr($user['first_name'], 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mobile-content">
            <?php if ($success): ?>
                <div class="alert success">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="content-section">
                    <div class="section-title"><h3>📝 Personal Information</h3></div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="+250 788 123 456">
                        </div>
                    </div>
                </div>
                
                <div class="content-section">
                    <div class="section-title"><h3>📍 Location Information</h3></div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Province</label>
                            <select name="province">
                                <option value="">Select province</option>
                                <option value="Kigali City" <?= $user['province'] === 'Kigali City' ? 'selected' : '' ?>>Kigali City</option>
                                <option value="Eastern" <?= $user['province'] === 'Eastern' ? 'selected' : '' ?>>Eastern Province</option>
                                <option value="Western" <?= $user['province'] === 'Western' ? 'selected' : '' ?>>Western Province</option>
                                <option value="Northern" <?= $user['province'] === 'Northern' ? 'selected' : '' ?>>Northern Province</option>
                                <option value="Southern" <?= $user['province'] === 'Southern' ? 'selected' : '' ?>>Southern Province</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>District</label>
                            <input type="text" name="district" value="<?= htmlspecialchars($user['district']) ?>" placeholder="e.g., Gasabo">
                        </div>
                        <div class="form-group">
                            <label>Sector</label>
                            <input type="text" name="sector" value="<?= htmlspecialchars($user['sector']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Cell</label>
                            <input type="text" name="cell" value="<?= htmlspecialchars($user['cell']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Village</label>
                            <input type="text" name="village" value="<?= htmlspecialchars($user['village']) ?>">
                        </div>
                    </div>
                </div>
                
                <?php if (in_array($user['user_type'], ['farmer', 'cooperative'])): ?>
                <div class="content-section">
                    <div class="section-title"><h3>🌾 Farming Information</h3></div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Farm Size</label>
                            <select name="farm_size">
                                <option value="">Select farm size</option>
                                <option value="Small (< 1 hectare)" <?= $user['farm_size'] === 'Small (< 1 hectare)' ? 'selected' : '' ?>>Small (< 1 hectare)</option>
                                <option value="Medium (1-5 hectares)" <?= $user['farm_size'] === 'Medium (1-5 hectares)' ? 'selected' : '' ?>>Medium (1-5 hectares)</option>
                                <option value="Large (> 5 hectares)" <?= $user['farm_size'] === 'Large (> 5 hectares)' ? 'selected' : '' ?>>Large (> 5 hectares)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>What do you grow?</label>
                            <textarea name="what_do_you_grow" placeholder="Describe the crops you grow or animals you raise"><?= htmlspecialchars($user['what_do_you_grow']) ?></textarea>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="content-section">
                    <div class="section-title"><h3>⚙️ Platform Settings</h3></div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Mobile Money Number</label>
                            <input type="tel" name="mobile_money_number" value="<?= htmlspecialchars($user['mobile_money_number']) ?>" placeholder="+250 788 123 456">
                        </div>
                        <div class="form-group">
                            <label>Language Preference</label>
                            <select name="language_preference">
                                <option value="kinyarwanda" <?= $user['language_preference'] === 'kinyarwanda' ? 'selected' : '' ?>>Kinyarwanda</option>
                                <option value="english" <?= $user['language_preference'] === 'english' ? 'selected' : '' ?>>English</option>
                                <option value="french" <?= $user['language_preference'] === 'french' ? 'selected' : '' ?>>French</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <input type="file" name="profile_image" id="profileImageUpload" accept="image/*" style="display: none;" onchange="this.form.submit()">
                <div class="form-actions">
                    <button type="submit" class="action-btn primary">💾 Update Profile</button>
                </div>
            </form>
            
            <!-- Mobile Logout Section -->
            <div class="content-section mobile-logout">
                <div class="section-title"><h3>🚪 Account</h3></div>
                <a href="../auth/login.php?logout=1" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a>
            </div>
        </div>
    </div>
    
    <!-- Image Options Popup -->
    <div id="imageOptionsPopup" class="image-popup" style="display: none;">
        <div class="popup-overlay" onclick="hideImageOptions()"></div>
        <div class="popup-content">
            <h3>Profile Photo</h3>
            <div class="popup-options">
                <?php if ($user['profile_picture'] && file_exists('../uploads/profiles/' . $user['profile_picture'])): ?>
                    <button onclick="viewImage()" class="popup-btn view">👁️ View Photo</button>
                    <button onclick="changeImage()" class="popup-btn change">📷 Change Photo</button>
                    <button onclick="removeImage()" class="popup-btn remove">🗑️ Remove Photo</button>
                <?php else: ?>
                    <button onclick="changeImage()" class="popup-btn change">📷 Add Photo</button>
                <?php endif; ?>
            </div>
            <button onclick="hideImageOptions()" class="popup-btn cancel">Cancel</button>
        </div>
    </div>
    
    <!-- Image Viewer -->
    <div id="imageViewer" class="image-viewer">
        <div class="viewer-content">
            <img id="viewerImage" src="" alt="Profile Photo">
            <button class="close-viewer" onclick="closeImageViewer()">×</button>
        </div>
    </div>
    
    <script>
    function showImageOptions() {
        document.getElementById('imageOptionsPopup').style.display = 'flex';
    }
    
    function hideImageOptions() {
        document.getElementById('imageOptionsPopup').style.display = 'none';
    }
    
    function viewImage() {
        <?php if ($user['profile_picture'] && file_exists('../uploads/profiles/' . $user['profile_picture'])): ?>
            document.getElementById('viewerImage').src = '../uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>';
            document.getElementById('imageViewer').style.display = 'flex';
            hideImageOptions();
        <?php endif; ?>
    }
    
    function changeImage() {
        document.getElementById('profileImageUpload').click();
        hideImageOptions();
    }
    
    function removeImage() {
        if (confirm('Are you sure you want to remove your profile photo?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="remove_image" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
        hideImageOptions();
    }
    
    function closeImageViewer() {
        document.getElementById('imageViewer').style.display = 'none';
    }
    
    // Close popup when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('popup-overlay')) {
            hideImageOptions();
        }
        if (e.target.classList.contains('image-viewer')) {
            closeImageViewer();
        }
    });
    
    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideImageOptions();
            closeImageViewer();
        }
    });
    </script>
    
    <?php 
    // Set current_user for bottom nav
    if (!isset($current_user)) {
        $current_user = $user;
    }
    include '../includes/bottom-nav.php'; 
    ?>
</body>
</html>