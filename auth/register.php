<?php
session_start();
require_once '../config/database.php';


// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db = new Database();
$errors = [];

// Fetch provinces from database
$provinces = $db->fetchAll("SELECT DISTINCT province FROM users WHERE province IS NOT NULL AND province != '' ORDER BY province");
if (empty($provinces)) {
    $provinces = [
        ['province' => 'Kigali City'],
        ['province' => 'Eastern Province'],
        ['province' => 'Western Province'],
        ['province' => 'Northern Province'],
        ['province' => 'Southern Province']
    ];
}

$user_types = [
    'farmer' => 'Farmer',
    'buyer' => 'Buyer',
    'cooperative_member' => 'Cooperative Member',
    'supplier' => 'Supplier'
];

if ($_POST) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $birth_day = $_POST['birth_day'] ?? '';
        $birth_month = $_POST['birth_month'] ?? '';
        $birth_year = $_POST['birth_year'] ?? '';
        
        $date_of_birth = '';
        if ($birth_day && $birth_month && $birth_year) {
            $date_of_birth = $birth_year . '-' . $birth_month . '-' . $birth_day;
        }
        
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'user_type' => $_POST['user_type'] ?? '',
            'date_of_birth' => $date_of_birth,
            'gender' => $_POST['gender'] ?? ''
        ];
        
        // Input validation
        if (empty($data['first_name'])) $errors[] = 'First name is required';
        if (empty($data['last_name'])) $errors[] = 'Last name is required';
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        if (empty($data['user_type']) || !array_key_exists($data['user_type'], $user_types)) {
            $errors[] = 'Please select a valid user type';
        }
        
        // Check if email already exists
        if (empty($errors)) {
            $existing = $db->fetch("SELECT id FROM users WHERE email = ?", [$data['email']]);
            if ($existing) {
                $errors[] = 'Email address is already registered';
            }
        }
        
        // Register user if no errors
        if (empty($errors)) {
            try {
                // Hash password
                $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
                
                // Insert user with basic info
                $user_id = $db->query(
                    "INSERT INTO users (first_name, last_name, email, phone, password_hash, user_type, date_of_birth, gender, profile_completion_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $data['first_name'], 
                        $data['last_name'], 
                        $data['email'], 
                        $data['phone'], 
                        $password_hash, 
                        $data['user_type'], 
                        $data['date_of_birth'] ?: null,
                        $data['gender'] ?: null,
                        30
                    ]
                );
                
                // Set session for new user
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_email'] = $data['email'];
                $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
                $_SESSION['user_type'] = $data['user_type'];
                $_SESSION['profile_incomplete'] = true;
                
                header('Location: complete-profile.php');
                exit;
                
            } catch (Exception $e) {
                error_log("Registration error: " . $e->getMessage());
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Curuza Muhinzi - Rwanda's Agricultural Platform</title>
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
        
        .back-button {
            position: fixed;
            top: 1.5rem;
            left: 1.5rem;
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .back-button:hover {
            background: var(--bg-alt);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
        }
        
        .register-card {
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 800px;
            padding: 3rem;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        
        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 1.5rem;
            box-shadow: var(--shadow);
            animation: logoFloat 3s ease-in-out infinite;
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }
        
        .register-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 0.75rem;
        }
        
        .register-subtitle {
            color: var(--text-light);
            font-size: 1rem;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-icon {
            width: 24px;
            height: 24px;
            background: var(--primary);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            color: white;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
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
        
        .form-input, .form-select {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background: var(--bg);
            font-family: inherit;
        }
        
        .form-select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"><path fill="%2364748b" d="M2 0L0 2h4zm0 5L0 3h4z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 12px;
            cursor: pointer;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        .form-input::placeholder {
            color: var(--text-light);
        }
        
        .date-selector {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 0.5rem;
        }
        
        .date-select {
            padding: 1rem 0.75rem;
        }
        
        .role-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .role-card {
            background: white;
            border: 2px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            text-align: center;
        }
        
        .role-card:hover {
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .role-card.selected {
            border-color: var(--primary);
            background: rgba(5, 150, 105, 0.05);
            box-shadow: var(--shadow);
        }
        
        .role-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .role-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .role-desc {
            font-size: 0.875rem;
            color: var(--text-light);
            line-height: 1.4;
        }
        
        .role-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        
        .role-check {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 24px;
            height: 24px;
            border: 2px solid var(--border);
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .role-card.selected .role-check {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }
        
        .register-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 2rem 0 1rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        
        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .register-btn:hover::before {
            left: 100%;
        }
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .login-link {
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-light);
            padding: 1rem;
            background: var(--bg-alt);
            border-radius: 12px;
            margin-top: 1rem;
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        
        .login-link a:hover {
            color: var(--primary-dark);
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
        
        .progress-steps {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .step {
            width: 40px;
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .step.active {
            background: var(--primary);
        }
        
        @media (max-width: 768px) {
            .register-card {
                padding: 2rem 1.5rem;
                margin: 0.5rem;
                border-radius: 20px;
            }
            
            .back-button {
                top: 1rem;
                left: 1rem;
                padding: 0.5rem;
            }
            
            .back-text {
                display: none;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .register-title {
                font-size: 1.75rem;
            }
            
            .logo {
                width: 70px;
                height: 70px;
                font-size: 1.75rem;
            }
            
            .role-cards {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .role-card {
                padding: 1.25rem;
            }
            
            .role-icon {
                font-size: 2rem;
                margin-bottom: 0.75rem;
            }
            
            .date-selector {
                grid-template-columns: 1fr 1.5fr 1fr;
                gap: 0.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .register-card {
                padding: 1.5rem 1rem;
            }
            
            .register-title {
                font-size: 1.5rem;
            }
            
            .form-input, .form-select, .register-btn {
                padding: 0.875rem;
            }
            
            .date-select {
                padding: 0.875rem 0.5rem;
                font-size: 0.8rem;
            }
            
            .role-card {
                padding: 1rem;
            }
            
            .role-title {
                font-size: 1rem;
            }
            
            .role-desc {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-button">
        <span>←</span>
        <span class="back-text">Back to Home</span>
    </a>
    
    <div class="register-container">
        <div class="register-card">
            <div class="progress-steps">
                <div class="step active"></div>
                <div class="step"></div>
                <div class="step"></div>
            </div>
            
            <div class="register-header">
                <div class="logo">🌱</div>
                <h1 class="register-title">Join Curuza Muhinzi</h1>
                <p class="register-subtitle">Connect with Rwanda's farmers and buyers. Start your agricultural journey today!</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">👤</div>
                        Personal Information
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-input" 
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" 
                                   placeholder="Enter your first name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-input" 
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" 
                                   placeholder="Enter your last name" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-input" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                   placeholder="your.email@example.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-input" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" 
                                   placeholder="+250 788 123 456">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Date of Birth</label>
                            <div class="date-selector">
                                <select name="birth_day" class="form-select date-select">
                                    <option value="">Day</option>
                                    <?php for($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= sprintf('%02d', $i) ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select name="birth_month" class="form-select date-select">
                                    <option value="">Month</option>
                                    <option value="01">January</option>
                                    <option value="02">February</option>
                                    <option value="03">March</option>
                                    <option value="04">April</option>
                                    <option value="05">May</option>
                                    <option value="06">June</option>
                                    <option value="07">July</option>
                                    <option value="08">August</option>
                                    <option value="09">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                                <select name="birth_year" class="form-select date-select">
                                    <option value="">Year</option>
                                    <?php for($i = date('Y'); $i >= 1950; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select gender</option>
                                <option value="male" <?= ($_POST['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($_POST['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">🌾</div>
                        Choose Your Agricultural Role
                    </div>
                    <div class="role-cards">
                        <label class="role-card" data-role="farmer">
                            <input type="radio" name="user_type" value="farmer" class="role-radio" <?= ($_POST['user_type'] ?? '') === 'farmer' ? 'checked' : '' ?> required>
                            <div class="role-check">✓</div>
                            <span class="role-icon">🌱</span>
                            <div class="role-title">Farmer</div>
                            <div class="role-desc">I grow crops, raise livestock, and produce agricultural products</div>
                        </label>
                        
                        <label class="role-card" data-role="buyer">
                            <input type="radio" name="user_type" value="buyer" class="role-radio" <?= ($_POST['user_type'] ?? '') === 'buyer' ? 'checked' : '' ?>>
                            <div class="role-check">✓</div>
                            <span class="role-icon">🛒</span>
                            <div class="role-title">Buyer</div>
                            <div class="role-desc">I purchase farm products for retail, wholesale, or processing</div>
                        </label>
                        
                        <label class="role-card" data-role="cooperative_member">
                            <input type="radio" name="user_type" value="cooperative_member" class="role-radio" <?= ($_POST['user_type'] ?? '') === 'cooperative_member' ? 'checked' : '' ?>>
                            <div class="role-check">✓</div>
                            <span class="role-icon">🤝</span>
                            <div class="role-title">Cooperative Member</div>
                            <div class="role-desc">I'm part of a farmers' cooperative or agricultural group</div>
                        </label>
                        
                        <label class="role-card" data-role="supplier">
                            <input type="radio" name="user_type" value="supplier" class="role-radio" <?= ($_POST['user_type'] ?? '') === 'supplier' ? 'selected' : '' ?>>
                            <div class="role-check">✓</div>
                            <span class="role-icon">📦</span>
                            <div class="role-title">Supplier</div>
                            <div class="role-desc">I provide farming equipment, seeds, fertilizers, and supplies</div>
                        </label>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">🔒</div>
                        Account Security
                    </div>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-input" 
                                   placeholder="Create a strong password (minimum 8 characters)" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="register-btn">
                    🌱 Create My Account
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Sign in here</a>
            </div>
        </div>
    </div>
    <script>
        // Form validation and progress
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const steps = document.querySelectorAll('.step');
            const inputs = document.querySelectorAll('.form-input, .form-select');
            
            // Update progress based on filled inputs
            function updateProgress() {
                const totalInputs = inputs.length;
                let filledInputs = 0;
                
                inputs.forEach(input => {
                    if (input.value.trim() !== '') {
                        filledInputs++;
                    }
                });
                
                const progress = Math.ceil((filledInputs / totalInputs) * 3);
                
                steps.forEach((step, index) => {
                    if (index < progress) {
                        step.classList.add('active');
                    } else {
                        step.classList.remove('active');
                    }
                });
            }
            
            // Add event listeners to inputs
            inputs.forEach(input => {
                input.addEventListener('input', updateProgress);
                input.addEventListener('change', updateProgress);
            });
            
            // Initial progress update
            updateProgress();
            
            // Form submission animation
            form.addEventListener('submit', function(e) {
                const submitBtn = document.querySelector('.register-btn');
                submitBtn.innerHTML = '🌱 Creating Account...';
                submitBtn.style.opacity = '0.7';
            });
            
            // Role card selection
            const roleCards = document.querySelectorAll('.role-card');
            const roleRadios = document.querySelectorAll('.role-radio');
            
            roleCards.forEach((card, index) => {
                card.addEventListener('click', function() {
                    // Remove selected class from all cards
                    roleCards.forEach(c => c.classList.remove('selected'));
                    
                    // Add selected class to clicked card
                    this.classList.add('selected');
                    
                    // Check the corresponding radio button
                    const radio = this.querySelector('.role-radio');
                    radio.checked = true;
                    
                    // Update progress
                    updateProgress();
                });
            });
            
            // Initialize selected state
            roleRadios.forEach((radio, index) => {
                if (radio.checked) {
                    roleCards[index].classList.add('selected');
                }
            });
        });
    </script>
</body>
</html>