<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and profile is incomplete
if (!isset($_SESSION['user_id']) || !isset($_SESSION['profile_incomplete'])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$errors = [];
$success = '';

// Get user data
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
if (!$user) {
    header('Location: login.php');
    exit;
}

// Handle profile picture upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/profiles/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file = $_FILES['profile_picture'];
    $file_size = $file['size'];
    $file_type = $file['type'];
    
    // Validate file size (20MB max)
    if ($file_size > 20 * 1024 * 1024) {
        $errors[] = 'Profile picture must be under 20MB';
    }
    
    // Validate file type
    if (!in_array($file_type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
        $errors[] = 'Profile picture must be JPG, PNG, or GIF';
    }
    
    if (empty($errors)) {
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Delete old profile picture if exists
            if ($user['profile_picture'] && file_exists($upload_dir . $user['profile_picture'])) {
                unlink($upload_dir . $user['profile_picture']);
            }
            $user['profile_picture'] = $new_filename;
        } else {
            $errors[] = 'Failed to upload profile picture';
        }
    }
}

if ($_POST) {
    // Sanitize and validate input
    $data = [
        'province' => $_POST['province'] ?? '',
        'district' => trim($_POST['district'] ?? ''),
        'sector' => trim($_POST['sector'] ?? ''),
        'cell' => trim($_POST['cell'] ?? ''),
        'village' => trim($_POST['village'] ?? ''),
        'address_details' => trim($_POST['address_details'] ?? ''),
        'farm_size' => trim($_POST['farm_size'] ?? ''),
        'what_do_you_grow' => trim($_POST['what_do_you_grow'] ?? ''),
        'mobile_money_number' => trim($_POST['mobile_money_number'] ?? ''),
        'language_preference' => $_POST['language_preference'] ?? 'kinyarwanda'
    ];
    
    try {
        // Calculate completion percentage
        $completion = 30; // Base from registration
        if (!empty($data['province'])) $completion += 10;
        if (!empty($data['district'])) $completion += 10;
        if (!empty($data['sector'])) $completion += 10;
        if (!empty($data['cell'])) $completion += 10;
        if (!empty($data['village'])) $completion += 10;
        if (!empty($data['address_details'])) $completion += 5;
        if (!empty($data['farm_size'])) $completion += 5;
        if (!empty($data['what_do_you_grow'])) $completion += 5;
        if (!empty($data['mobile_money_number'])) $completion += 5;
        
        // Update user profile
        $db->query(
            "UPDATE users SET province = ?, district = ?, sector = ?, cell = ?, village = ?, address_details = ?, farm_size = ?, what_do_you_grow = ?, mobile_money_number = ?, language_preference = ?, profile_picture = ?, profile_completion_percentage = ? WHERE id = ?",
            [
                $data['province'] ?: null,
                $data['district'] ?: null,
                $data['sector'] ?: null,
                $data['cell'] ?: null,
                $data['village'] ?: null,
                $data['address_details'] ?: null,
                $data['farm_size'] ?: null,
                $data['what_do_you_grow'] ?: null,
                $data['mobile_money_number'] ?: null,
                $data['language_preference'],
                $user['profile_picture'],
                $completion,
                $_SESSION['user_id']
            ]
        );
        
        // Clear incomplete profile flag
        unset($_SESSION['profile_incomplete']);
        
        $_SESSION['success_message'] = 'Profile completed successfully! Welcome to Curuza Muhinzi.';
        header('Location: ../feed.php');
        exit;
        
    } catch (Exception $e) {
        error_log("Profile completion error: " . $e->getMessage());
        $errors[] = "Failed to update profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - Curuza Muhinzi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --primary-light: #10b981;
            --accent: #f59e0b;
            --bg: #fefefe;
            --bg-alt: #f8fafc;
            --text: #0f172a;
            --text-light: #64748b;
            --border: #e2e8f0;
            --error: #ef4444;
            --success: #10b981;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--bg-alt) 0%, var(--bg) 100%);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .profile-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .profile-card {
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 900px;
            padding: 3rem;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        
        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .welcome-badge {
            background: rgba(5, 150, 105, 0.1);
            color: var(--primary);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        
        .profile-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 0.75rem;
        }
        
        .profile-subtitle {
            color: var(--text-light);
            font-size: 1.125rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .progress-section {
            background: var(--bg-alt);
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .progress-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 1rem;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--border);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            width: 30%;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 0.875rem;
            color: var(--text-light);
        }
        
        .form-section {
            margin-bottom: 2.5rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .section-icon {
            width: 32px;
            height: 32px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .form-group {
            position: relative;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background: var(--bg);
            font-family: inherit;
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"><path fill="%2364748b" d="M2 0L0 2h4zm0 5L0 3h4z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 12px;
            cursor: pointer;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        .form-input::placeholder, .form-textarea::placeholder {
            color: var(--text-light);
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2.5rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 160px;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            box-shadow: var(--shadow);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-secondary {
            background: white;
            color: var(--text);
            border: 2px solid var(--border);
        }
        
        .btn-secondary:hover {
            background: var(--bg-alt);
            border-color: var(--primary);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
            border-color: rgba(239, 68, 68, 0.2);
        }
        
        @media (max-width: 768px) {
            .profile-container {
                padding: 1rem 0.5rem;
                align-items: flex-start;
            }
            
            .profile-card {
                padding: 1.5rem 1rem;
                margin: 0;
                border-radius: 16px;
                max-width: 100%;
            }
            
            .profile-header {
                margin-bottom: 2rem;
            }
            
            .welcome-badge {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }
            
            .profile-title {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
            
            .profile-subtitle {
                font-size: 1rem;
            }
            
            .progress-section {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .section-title {
                font-size: 1.1rem;
                margin-bottom: 1rem;
                flex-wrap: wrap;
            }
            
            .section-icon {
                width: 28px;
                height: 28px;
                font-size: 0.9rem;
            }
            
            .form-section {
                margin-bottom: 2rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .form-input, .form-select, .form-textarea {
                padding: 0.875rem;
                font-size: 0.875rem;
            }
            
            .form-textarea {
                min-height: 80px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.75rem;
                margin-top: 2rem;
            }
            
            .btn {
                padding: 0.875rem 1.5rem;
                font-size: 0.9rem;
                justify-content: center;
            }
        }
        
        .profile-picture-section {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 2rem;
            align-items: start;
        }
        
        .current-picture {
            text-align: center;
        }
        
        .picture-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 1rem;
            border: 3px solid var(--border);
            position: relative;
        }
        
        .picture-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .no-picture {
            width: 100%;
            height: 100%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: 800;
        }
        
        .btn-upload {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 0.5rem;
        }
        
        .btn-upload:hover {
            background: var(--primary-light);
        }
        
        .upload-info {
            font-size: 0.75rem;
            color: var(--text-light);
        }
        
        .image-editor {
            background: #000;
            border-radius: 12px;
            padding: 1rem;
            color: white;
        }
        
        .crop-container {
            margin-bottom: 1rem;
        }
        
        .crop-area {
            position: relative;
            width: 100%;
            height: 300px;
            overflow: hidden;
            border-radius: 8px;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #cropImage {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: all 0.3s ease;
        }
        
        .crop-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }
        
        .crop-box {
            position: absolute;
            border: 2px solid #fff;
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);
            cursor: move;
            pointer-events: all;
            min-width: 100px;
            min-height: 100px;
            touch-action: none;
            user-select: none;
        }
        
        .crop-handle {
            position: absolute;
            width: 20px;
            height: 20px;
            background: #fff;
            border: 2px solid #000;
            border-radius: 50%;
            cursor: pointer;
            touch-action: none;
            z-index: 10;
        }
        
        .crop-handle.nw { top: -10px; left: -10px; cursor: nw-resize; }
        .crop-handle.ne { top: -10px; right: -10px; cursor: ne-resize; }
        .crop-handle.sw { bottom: -10px; left: -10px; cursor: sw-resize; }
        .crop-handle.se { bottom: -10px; right: -10px; cursor: se-resize; }
        
        .editor-controls {
            display: grid;
            gap: 1rem;
        }
        
        .control-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .control-row label {
            color: #fff;
            font-weight: 600;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }
        
        .control-row input[type="range"] {
            flex: 1;
            max-width: 200px;
        }
        
        .btn-filter {
            padding: 0.5rem 1rem;
            border: 1px solid #333;
            border-radius: 20px;
            background: #222;
            color: #fff;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.2s ease;
        }
        
        .btn-filter:hover, .btn-filter.active {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .editor-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }
        
        .btn-editor {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .btn-cancel {
            background: #333;
            color: #fff;
        }
        
        .btn-apply {
            background: var(--primary);
            color: white;
        }
        
        .btn-editor:hover {
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .profile-picture-section {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .picture-preview {
                width: 120px;
                height: 120px;
            }
            
            .no-picture {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .profile-container {
                padding: 0.5rem;
            }
            
            .profile-card {
                padding: 1rem 0.75rem;
                border-radius: 12px;
            }
            
            .profile-title {
                font-size: 1.25rem;
            }
            
            .profile-subtitle {
                font-size: 0.9rem;
            }
            
            .section-title {
                font-size: 1rem;
            }
            
            .form-input, .form-select, .form-textarea {
                padding: 0.75rem;
                font-size: 0.8rem;
            }
            
            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="welcome-badge">👋 Welcome, <?= htmlspecialchars($user['first_name']) ?>!</div>
                <h1 class="profile-title">Complete Your Profile</h1>
                <p class="profile-subtitle">Help us personalize your experience by completing your profile information</p>
            </div>
            
            <div class="progress-section">
                <div class="progress-title">Profile Completion</div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-text" id="progressText">30% Complete</div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">📷</div>
                        Profile Picture
                    </div>
                    <div class="profile-picture-section">
                        <div class="current-picture">
                            <div class="picture-preview" id="picturePreview">
                                <?php if ($user['profile_picture'] && file_exists('../uploads/profiles/' . $user['profile_picture'])): ?>
                                    <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" id="previewImg">
                                <?php else: ?>
                                    <div class="no-picture" id="noPicture">
                                        <span><?= strtoupper(substr($user['first_name'], 0, 1)) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="picture-controls">
                                <input type="file" id="profilePicture" name="profile_picture" accept="image/*" style="display: none;">
                                <button type="button" class="btn-upload" onclick="document.getElementById('profilePicture').click()">📷 Choose Photo</button>
                                <div class="upload-info">Max 20MB • JPG, PNG, GIF</div>
                            </div>
                        </div>
                        
                        <div class="image-editor" id="imageEditor" style="display: none;">
                            <div class="crop-container">
                                <div class="crop-area">
                                    <img id="cropImage" src="" alt="Crop Image">
                                    <div class="crop-overlay">
                                        <div class="crop-box" id="cropBox">
                                            <div class="crop-handle nw"></div>
                                            <div class="crop-handle ne"></div>
                                            <div class="crop-handle sw"></div>
                                            <div class="crop-handle se"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="editor-controls">
                                <div class="control-row">
                                    <button type="button" class="btn-filter" onclick="applyFilter('none')">Original</button>
                                    <button type="button" class="btn-filter" onclick="applyFilter('grayscale')">B&W</button>
                                    <button type="button" class="btn-filter" onclick="applyFilter('sepia')">Sepia</button>
                                    <button type="button" class="btn-filter" onclick="applyFilter('vintage')">Vintage</button>
                                </div>
                                <div class="control-row">
                                    <label>Zoom:</label>
                                    <input type="range" id="zoomSlider" min="0.5" max="3" step="0.1" value="1">
                                </div>
                                <div class="editor-buttons">
                                    <button type="button" class="btn-editor btn-cancel" onclick="cancelEdit()">Cancel</button>
                                    <button type="button" class="btn-editor btn-apply" onclick="cropAndApply()">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">📍</div>
                        Location Information
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Province</label>
                            <select name="province" class="form-select">
                                <option value="">Select your province</option>
                                <option value="Kigali City">🏙️ Kigali City</option>
                                <option value="Eastern">🌅 Eastern Province</option>
                                <option value="Western">🌄 Western Province</option>
                                <option value="Northern">⛰️ Northern Province</option>
                                <option value="Southern">🌳 Southern Province</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">District</label>
                            <input type="text" name="district" class="form-input" 
                                   placeholder="e.g., Gasabo, Nyagatare, Huye">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Sector</label>
                            <input type="text" name="sector" class="form-input" 
                                   placeholder="Enter your sector">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Cell</label>
                            <input type="text" name="cell" class="form-input" 
                                   placeholder="Enter your cell">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Village</label>
                            <input type="text" name="village" class="form-input" 
                                   placeholder="Enter your village">
                        </div>
                        
                        <div class="form-group full-width">
                            <label class="form-label">Address Details</label>
                            <textarea name="address_details" class="form-textarea" 
                                      placeholder="Provide additional address details (optional)"></textarea>
                        </div>
                    </div>
                </div>
                
                <?php if ($user['user_type'] === 'farmer'): ?>
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">🌾</div>
                        Farming Information
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Farm Size</label>
                            <select name="farm_size" class="form-select">
                                <option value="">Select farm size</option>
                                <option value="Small (< 1 hectare)">Small (< 1 hectare)</option>
                                <option value="Medium (1-5 hectares)">Medium (1-5 hectares)</option>
                                <option value="Large (> 5 hectares)">Large (> 5 hectares)</option>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label class="form-label">What do you grow?</label>
                            <textarea name="what_do_you_grow" class="form-textarea" 
                                      placeholder="Describe the crops you grow or animals you raise"></textarea>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">⚙️</div>
                        Platform Settings
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Mobile Money Number</label>
                            <input type="tel" name="mobile_money_number" class="form-input" 
                                   placeholder="+250 788 123 456">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Preferred Language</label>
                            <select name="language_preference" class="form-select">
                                <option value="kinyarwanda">🇷🇼 Kinyarwanda</option>
                                <option value="english">🇬🇧 English</option>
                                <option value="french">🇫🇷 French</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="login.php" class="btn btn-secondary">⏭️ Skip for Now</a>
                    <button type="submit" class="btn btn-primary">✅ Complete Profile</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('profileForm');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
            
            function updateProgress() {
                const totalFields = inputs.length;
                let filledFields = 0;
                
                inputs.forEach(input => {
                    if (input.value.trim() !== '') {
                        filledFields++;
                    }
                });
                
                const progress = Math.max(30, Math.min(100, 30 + (filledFields / totalFields) * 70));
                progressFill.style.width = progress + '%';
                progressText.textContent = Math.round(progress) + '% Complete';
            }
            
            inputs.forEach(input => {
                input.addEventListener('input', updateProgress);
                input.addEventListener('change', updateProgress);
            });
            
            form.addEventListener('submit', function(e) {
                const submitBtn = document.querySelector('.btn-primary');
                submitBtn.innerHTML = '⏳ Completing Profile...';
                submitBtn.style.opacity = '0.7';
            });
        });
        
        let originalImageSrc = null;
        let currentFilter = 'none';
        let cropBox = null;
        let isDragging = false;
        let isResizing = false;
        let startX, startY, startWidth, startHeight, startLeft, startTop;
        
        document.getElementById('profilePicture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 20 * 1024 * 1024) {
                    alert('File size must be under 20MB');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(event) {
                    originalImageSrc = event.target.result;
                    setupCropEditor();
                    document.getElementById('imageEditor').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
        
        function setupCropEditor() {
            const cropImage = document.getElementById('cropImage');
            cropImage.src = originalImageSrc;
            
            cropImage.onload = function() {
                initializeCropBox();
                setupCropHandlers();
            };
        }
        
        function initializeCropBox() {
            cropBox = document.getElementById('cropBox');
            const cropArea = document.querySelector('.crop-area');
            const rect = cropArea.getBoundingClientRect();
            
            // Center crop box (square)
            const size = Math.min(rect.width, rect.height) * 0.6;
            const left = (rect.width - size) / 2;
            const top = (rect.height - size) / 2;
            
            cropBox.style.width = size + 'px';
            cropBox.style.height = size + 'px';
            cropBox.style.left = left + 'px';
            cropBox.style.top = top + 'px';
        }
        
        function setupCropHandlers() {
            // Mouse events for desktop
            cropBox.addEventListener('mousedown', startDrag);
            document.querySelectorAll('.crop-handle').forEach(handle => {
                handle.addEventListener('mousedown', startResize);
            });
            document.addEventListener('mousemove', handleMove);
            document.addEventListener('mouseup', endAction);
            
            // Touch events for mobile
            cropBox.addEventListener('touchstart', startDrag);
            document.querySelectorAll('.crop-handle').forEach(handle => {
                handle.addEventListener('touchstart', startResize);
            });
            document.addEventListener('touchmove', handleMove);
            document.addEventListener('touchend', endAction);
        }
        
        function getEventCoords(e) {
            if (e.touches && e.touches[0]) {
                return { x: e.touches[0].clientX, y: e.touches[0].clientY };
            }
            return { x: e.clientX, y: e.clientY };
        }
        
        function startDrag(e) {
            if (e.target === cropBox) {
                isDragging = true;
                const coords = getEventCoords(e);
                const rect = cropBox.getBoundingClientRect();
                startX = coords.x - rect.left;
                startY = coords.y - rect.top;
                e.preventDefault();
            }
        }
        
        function startResize(e) {
            isResizing = true;
            const coords = getEventCoords(e);
            startX = coords.x;
            startY = coords.y;
            startWidth = parseInt(cropBox.style.width);
            startHeight = parseInt(cropBox.style.height);
            startLeft = parseInt(cropBox.style.left);
            startTop = parseInt(cropBox.style.top);
            e.preventDefault();
            e.stopPropagation();
        }
        
        function handleMove(e) {
            if (!isDragging && !isResizing) return;
            
            const coords = getEventCoords(e);
            const cropArea = document.querySelector('.crop-area');
            const areaRect = cropArea.getBoundingClientRect();
            
            if (isDragging) {
                let newLeft = coords.x - areaRect.left - startX;
                let newTop = coords.y - areaRect.top - startY;
                
                // Constrain to crop area
                newLeft = Math.max(0, Math.min(newLeft, areaRect.width - cropBox.offsetWidth));
                newTop = Math.max(0, Math.min(newTop, areaRect.height - cropBox.offsetHeight));
                
                cropBox.style.left = newLeft + 'px';
                cropBox.style.top = newTop + 'px';
            }
            
            if (isResizing) {
                const deltaX = coords.x - startX;
                const deltaY = coords.y - startY;
                const delta = Math.max(deltaX, deltaY);
                
                let newSize = Math.max(100, startWidth + delta);
                
                // Constrain size
                newSize = Math.min(newSize, areaRect.width - startLeft, areaRect.height - startTop);
                
                cropBox.style.width = newSize + 'px';
                cropBox.style.height = newSize + 'px';
            }
            
            e.preventDefault();
        }
        
        function endAction() {
            isDragging = false;
            isResizing = false;
        }
        
        function applyFilter(filter) {
            currentFilter = filter;
            const cropImage = document.getElementById('cropImage');
            
            // Remove active class from all filter buttons
            document.querySelectorAll('.btn-filter').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            switch(filter) {
                case 'grayscale':
                    cropImage.style.filter = 'grayscale(100%)';
                    break;
                case 'sepia':
                    cropImage.style.filter = 'sepia(100%)';
                    break;
                case 'vintage':
                    cropImage.style.filter = 'sepia(50%) contrast(1.2) brightness(1.1)';
                    break;
                default:
                    cropImage.style.filter = 'none';
            }
        }
        
        document.getElementById('zoomSlider').addEventListener('input', function() {
            const cropImage = document.getElementById('cropImage');
            const zoom = this.value;
            cropImage.style.transform = `scale(${zoom})`;
        });
        
        function cropAndApply() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const cropImage = document.getElementById('cropImage');
            
            // Set canvas size to crop box size
            canvas.width = 300;
            canvas.height = 300;
            
            // Calculate crop coordinates
            const cropArea = document.querySelector('.crop-area');
            const areaRect = cropArea.getBoundingClientRect();
            const imageRect = cropImage.getBoundingClientRect();
            
            const scaleX = cropImage.naturalWidth / imageRect.width;
            const scaleY = cropImage.naturalHeight / imageRect.height;
            
            const cropLeft = (parseInt(cropBox.style.left) - (imageRect.left - areaRect.left)) * scaleX;
            const cropTop = (parseInt(cropBox.style.top) - (imageRect.top - areaRect.top)) * scaleY;
            const cropWidth = parseInt(cropBox.style.width) * scaleX;
            const cropHeight = parseInt(cropBox.style.height) * scaleY;
            
            // Apply filter to canvas
            if (currentFilter !== 'none') {
                ctx.filter = cropImage.style.filter;
            }
            
            // Draw cropped image
            const img = new Image();
            img.onload = function() {
                ctx.drawImage(img, cropLeft, cropTop, cropWidth, cropHeight, 0, 0, 300, 300);
                
                canvas.toBlob(function(blob) {
                    const url = URL.createObjectURL(blob);
                    const preview = document.getElementById('picturePreview');
                    preview.innerHTML = `<img src="${url}" alt="Profile Picture" id="previewImg">`;
                    
                    // Hide editor
                    document.getElementById('imageEditor').style.display = 'none';
                    
                    // Update file input
                    const dt = new DataTransfer();
                    const file = new File([blob], 'profile.jpg', { type: 'image/jpeg' });
                    dt.items.add(file);
                    document.getElementById('profilePicture').files = dt.files;
                }, 'image/jpeg', 0.9);
            };
            img.src = originalImageSrc;
        }
        
        function cancelEdit() {
            document.getElementById('imageEditor').style.display = 'none';
            document.getElementById('profilePicture').value = '';
        }
    </script>
</body>
</html>