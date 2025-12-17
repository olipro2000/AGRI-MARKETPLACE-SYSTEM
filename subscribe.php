<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$plan_id = isset($_GET['plan']) ? (int)$_GET['plan'] : 0;

if (!$plan_id) {
    header('Location: feed.php');
    exit();
}

$db = new Database();

// Get plan details
$plan = $db->query("SELECT * FROM subscription_plans WHERE id = ? AND is_active = 1", [$plan_id])->fetch();

if (!$plan) {
    header('Location: feed.php');
    exit();
}

// Check if user already has active subscription
$existing = $db->query("SELECT * FROM user_subscriptions WHERE user_id = ? AND status = 'active' AND expires_at > NOW()", [$_SESSION['user_id']])->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($payment_method)) {
        $error = "Please select a payment method.";
    } elseif (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== 0) {
        $error = "Please upload payment proof screenshot.";
    } else {
        $payment_proof = '';
        
        // Handle file upload
        $upload_dir = 'uploads/payment_proofs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $payment_proof = uniqid() . '.' . $file_extension;
        move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_dir . $payment_proof);
        
        // Create subscription record
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime("+{$plan['duration_days']} days"));
    
    $subscription_id = $db->query("INSERT INTO user_subscriptions (user_id, plan_id, status, starts_at, expires_at, created_at) VALUES (?, ?, 'pending', ?, ?, NOW())", 
        [$_SESSION['user_id'], $plan_id, $start_date, $end_date]);
    
    // Create payment proof record
    $db->query("INSERT INTO payment_proofs (user_id, subscription_id, payment_method_id, amount, screenshot_url, status, created_at) VALUES (?, ?, 1, ?, ?, 'pending', NOW())", 
        [$_SESSION['user_id'], $subscription_id, $plan['price'], $payment_proof]);
    
        $_SESSION['payment_success'] = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe - <?php echo htmlspecialchars($plan['name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; margin-bottom: 20px; }
        .back-btn:hover { color: #28a745; }
        
        .subscription-card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .plan-header { text-align: center; margin-bottom: 30px; }
        .plan-name { font-size: 2rem; font-weight: bold; color: #28a745; margin-bottom: 10px; }
        .plan-price { font-size: 3rem; font-weight: bold; color: #333; }
        .plan-duration { color: #666; font-size: 1.1rem; }
        
        .features { margin: 30px 0; }
        .feature { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .feature i { color: #28a745; }
        
        .payment-section { margin-top: 30px; }
        .payment-methods { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .payment-method { border: 2px solid #e9ecef; border-radius: 8px; padding: 15px; cursor: pointer; transition: all 0.3s; }
        .payment-method:hover, .payment-method.selected { border-color: #28a745; background: #f8fff9; }
        .payment-method input { display: none; }
        .payment-method label { cursor: pointer; font-weight: 500; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; }
        
        .submit-btn { width: 100%; background: #28a745; color: white; padding: 15px; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 500; cursor: pointer; }
        .submit-btn:hover { background: #218838; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
.alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

.validation-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.validation-content {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
    animation: modalSlideIn 0.3s ease-out;
}

.validation-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.validation-header h3 {
    margin: 0;
    color: #dc2626;
    font-size: 18px;
}

.close-validation {
    background: none;
    border: none;
    font-size: 24px;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.close-validation:hover {
    background: #f3f4f6;
}

.validation-body {
    padding: 20px 24px;
}

.validation-body p {
    margin: 0 0 16px 0;
    color: #374151;
    font-weight: 500;
}

.error-list {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 16px;
    color: #dc2626;
    line-height: 1.6;
}

.validation-footer {
    padding: 16px 24px 24px;
    text-align: center;
}

.btn-ok {
    background: #dc2626;
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-ok:hover {
    background: #b91c1c;
    transform: translateY(-1px);
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.success-modal {
    border: 2px solid #10b981;
}

.success-header {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border-bottom-color: #10b981;
}

.success-header h3 {
    color: #065f46;
}

.success-message {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 8px;
    padding: 16px;
    color: #065f46;
    line-height: 1.6;
    font-weight: 500;
}

.success-btn {
    background: #10b981;
}

.success-btn:hover {
    background: #059669;
}
        
        .payment-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .payment-details { display: none; margin-top: 15px; }
        .payment-details.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <a href="feed.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Feed
        </a>
        
        <?php if (isset($_SESSION['payment_success'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showSuccessModal();
                });
            </script>
            <?php unset($_SESSION['payment_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($existing): ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> You already have an active subscription. It expires on <?php echo date('M j, Y', strtotime($existing['expires_at'])); ?>
            </div>
        <?php endif; ?>
        
        <div class="subscription-card">
            <div class="plan-header">
                <div class="plan-name"><?php echo htmlspecialchars($plan['name']); ?></div>
                <div class="plan-price">RWF <?php echo number_format($plan['price']); ?></div>
                <div class="plan-duration">per <?php echo $plan['duration_days']; ?> days</div>
            </div>
            
            <div class="features">
                <?php 
                $features = json_decode($plan['features'], true);
                foreach ($features as $feature): 
                ?>
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span><?php echo htmlspecialchars($feature); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!$existing): ?>
            <form method="POST" enctype="multipart/form-data" class="payment-section">
                <h3>Payment Method <span style="color: red;">*</span></h3>
                <div class="payment-methods">
                    <div class="payment-method" onclick="selectPayment('mtn')">
                        <input type="radio" name="payment_method" value="mtn" id="mtn">
                        <label for="mtn">MTN Mobile Money</label>
                    </div>
                    <div class="payment-method" onclick="selectPayment('airtel')">
                        <input type="radio" name="payment_method" value="airtel" id="airtel">
                        <label for="airtel">Airtel Money</label>
                    </div>
                    <div class="payment-method" onclick="selectPayment('momo')">
                        <input type="radio" name="payment_method" value="momo" id="momo">
                        <label for="momo">MOMO Code</label>
                    </div>
                    <div class="payment-method" onclick="selectPayment('bank')">
                        <input type="radio" name="payment_method" value="bank" id="bank">
                        <label for="bank">Bank Transfer</label>
                    </div>
                </div>
                
                <div class="payment-info">
                    <div id="mtn-details" class="payment-details">
                        <strong>MTN Mobile Money:</strong><br>
                        Send RWF <?php echo number_format($plan['price']); ?> to: <strong>0788123456</strong><br>
                        Reference: Your username
                    </div>
                    <div id="airtel-details" class="payment-details">
                        <strong>Airtel Money:</strong><br>
                        Send RWF <?php echo number_format($plan['price']); ?> to: <strong>0732123456</strong><br>
                        Reference: Your username
                    </div>
                    <div id="momo-details" class="payment-details">
                        <strong>MOMO Code:</strong><br>
                        Send RWF <?php echo number_format($plan['price']); ?> to: <strong>0788123456</strong><br>
                        Reference: Your username
                    </div>
                    <div id="bank-details" class="payment-details">
                        <strong>Bank Transfer:</strong><br>
                        Account: 1234567890<br>
                        Bank: Bank of Kigali<br>
                        Amount: RWF <?php echo number_format($plan['price']); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="payment_proof">Upload Payment Screenshot *</label>
                    <input type="file" name="payment_proof" id="payment_proof" accept="image/*" required>
                </div>
                
                <button type="submit" class="submit-btn" onclick="return validateForm()">
                    <i class="fas fa-credit-card"></i> Submit Payment
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Success Modal -->
    <div id="successModal" class="validation-modal">
        <div class="validation-content success-modal">
            <div class="validation-header success-header">
                <h3>✅ Payment Submitted Successfully!</h3>
            </div>
            <div class="validation-body">
                <p>Thank you for your payment submission!</p>
                <div class="success-message">
                    Your subscription will be activated after verification by our admin team. You will receive a notification once approved.
                </div>
            </div>
            <div class="validation-footer">
                <button class="btn-ok success-btn" onclick="goToFeed()">Continue to Feed</button>
            </div>
        </div>
    </div>
    
    <!-- Validation Modal -->
    <div id="validationModal" class="validation-modal">
        <div class="validation-content">
            <div class="validation-header">
                <h3>⚠️ Missing Information</h3>
                <button class="close-validation" onclick="closeValidationModal()">&times;</button>
            </div>
            <div class="validation-body">
                <p>Please complete the following required fields:</p>
                <div id="errorList" class="error-list"></div>
            </div>
            <div class="validation-footer">
                <button class="btn-ok" onclick="closeValidationModal()">OK, Got It!</button>
            </div>
        </div>
    </div>
    
    <script>
        function selectPayment(method) {
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('selected'));
            document.querySelectorAll('.payment-details').forEach(el => el.classList.remove('active'));
            
            event.currentTarget.classList.add('selected');
            document.getElementById(method).checked = true;
            document.getElementById(method + '-details').classList.add('active');
        }
        
        function validateForm() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            const paymentProof = document.getElementById('payment_proof');
            
            let errors = [];
            
            if (!paymentMethod) {
                errors.push('• Please select a payment method');
            }
            
            if (!paymentProof.files.length) {
                errors.push('• Please upload payment proof screenshot');
            }
            
            if (errors.length > 0) {
                showValidationModal(errors);
                return false;
            }
            
            return true;
        }
        
        function showValidationModal(errors) {
            const modal = document.getElementById('validationModal');
            const errorList = document.getElementById('errorList');
            errorList.innerHTML = errors.join('<br>');
            modal.style.display = 'flex';
        }
        
        function closeValidationModal() {
            document.getElementById('validationModal').style.display = 'none';
        }
        
        function showSuccessModal() {
            document.getElementById('successModal').style.display = 'flex';
        }
        
        function goToFeed() {
            window.location.href = 'feed.php';
        }
    </script>
</body>
</html>