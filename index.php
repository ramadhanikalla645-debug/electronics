<?php
session_start();
require_once 'includes/db_connection.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: customer_dashboard.php");
    }
    exit();
}

// Handle login
$error = "";
$success = "";

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Simple validation
    if(empty($username) || empty($password)) {
        $error = "Please fill in all fields!";
    } else {
        // Check user in database
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password (using password_verify with hashed passwords)
            if(password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                if($user['role'] == 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: customer_dashboard.php");
                }
                exit();
            } else {
                $error = "Wrong password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}

// Handle registration
if(isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $email = $_POST['reg_email'];
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['reg_confirm_password'];
    
    // Simple validation
    if(empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif($password != $confirm_password) {
        $error = "Passwords don't match!";
    } else {
        // Check if user already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user (default role is 'customer')
            $insert_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if($insert_stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed!";
            }
        }
    }
}

// Handle password reset
if(isset($_POST['reset_password'])) {
    $reset_username = $_POST['reset_username'];
    $reset_email = $_POST['reset_email'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    
    // Simple validation
    if(empty($reset_username) || empty($reset_email) || empty($new_password) || empty($confirm_new_password)) {
        $error = "All fields are required!";
    } elseif($new_password != $confirm_new_password) {
        $error = "New passwords don't match!";
    } else {
        // Check if username and email match
        $sql = "SELECT id FROM users WHERE username = ? AND email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $reset_username, $reset_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            // Username and email match, reset password
            $user = $result->fetch_assoc();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_sql = "UPDATE users SET password = ? WHERE username = ? AND email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sss", $hashed_password, $reset_username, $reset_email);
            
            if($update_stmt->execute()) {
                $success = "Password reset successful! You can now login with your new password.";
            } else {
                $error = "Password reset failed!";
            }
        } else {
            $error = "Username and email do not match or user not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Electronic Ordering System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            margin-bottom: 0;
            border-bottom: 2px solid #ddd;
        }
        
        .tab-btn {
            padding: 12px 30px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            color: #7f8c8d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-btn:hover {
            color: #22972c;
        }
        
        .tab-btn.active {
            color: #22972c;
            border-bottom: 3px solid #22972c;
            background: #f8f9fa;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 0 5px 5px 5px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-content h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: bold;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            padding-right: 45px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #22972c;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        
        /* Eye Toggle Button */
        .eye-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #7f8c8d;
            padding: 5px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .eye-toggle:hover {
            color: #3498db;
        }
        
        /* Buttons */
        .btn {
            width: 100%;
            padding: 14px;
            background: #22972c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #22972c;
        }
        
        /* Messages */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        /* Products Section */
        .products {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        
        .products h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .product {
            background: white;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .product h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .product p {
            color: #555;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 16px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .tab-btn {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .tab-content {
                padding: 20px;
            }
            
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Electronic Ordering System</h1>
        <p class="subtitle">Shop for the latest electronics at best prices</p>
        
        <?php if($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Tabs Navigation -->
        <div class="tabs">
            <button class="tab-btn active" data-tab="login">Login</button>
            <button class="tab-btn" data-tab="register">Register</button>
            <button class="tab-btn" data-tab="reset">Reset Password</button>
        </div>
        
        <!-- Login Tab Content -->
        <div class="tab-content active" id="login-tab">
            <h2>Login to Your Account</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username or email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="eye-toggle" data-target="password">
                            <span class="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn">Login</button>
            </form>
        </div>
        
        <!-- Register Tab Content -->
        <div class="tab-content" id="register-tab">
            <h2>Create New Account</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="reg_username">Username</label>
                    <input type="text" id="reg_username" name="reg_username" placeholder="Choose a username" required>
                </div>
                
                <div class="form-group">
                    <label for="reg_email">Email Address</label>
                    <input type="email" id="reg_email" name="reg_email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="reg_password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="reg_password" name="reg_password" placeholder="Create a password" required>
                        <button type="button" class="eye-toggle" data-target="reg_password">
                            <span class="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reg_confirm_password">Confirm Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="reg_confirm_password" name="reg_confirm_password" placeholder="Confirm your password" required>
                        <button type="button" class="eye-toggle" data-target="reg_confirm_password">
                            <span class="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="register" class="btn">Register</button>
            </form>
        </div>
        
        <!-- Reset Password Tab Content -->
        <div class="tab-content" id="reset-tab">
            <h2>Reset Your Password</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="reset_username">Username</label>
                    <input type="text" id="reset_username" name="reset_username" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label for="reset_email">Email Address</label>
                    <input type="email" id="reset_email" name="reset_email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                        <button type="button" class="eye-toggle" data-target="new_password">
                            <span class="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_new_password">Confirm New Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm new password" required>
                        <button type="button" class="eye-toggle" data-target="confirm_new_password">
                            <span class="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="reset_password" class="btn">Reset Password</button>
            </form>
        </div>
        
        
    </div>
    
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get all tab buttons and content
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            // Add click event to each tab button
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons and content
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button
                    button.classList.add('active');
                    
                    // Show corresponding content
                    const tabId = button.getAttribute('data-tab');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
            
            // Show/hide password functionality
            document.querySelectorAll('.eye-toggle').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const eyeIcon = this.querySelector('.eye-icon');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        eyeIcon.textContent = '🙈'; // Hide icon
                    } else {
                        input.type = 'password';
                        eyeIcon.textContent = '👁️'; // Show icon
                    }
                });
            });
            
            // Password match validation for registration
            const regPassword = document.getElementById('reg_password');
            const regConfirmPassword = document.getElementById('reg_confirm_password');
            
            if(regPassword && regConfirmPassword) {
                function checkPasswordMatch() {
                    if(regPassword.value !== '' && regConfirmPassword.value !== '') {
                        if(regPassword.value !== regConfirmPassword.value) {
                            regConfirmPassword.style.borderColor = '#c62828';
                        } else {
                            regConfirmPassword.style.borderColor = '#2e7d32';
                        }
                    }
                }
                
                regPassword.addEventListener('input', checkPasswordMatch);
                regConfirmPassword.addEventListener('input', checkPasswordMatch);
            }
            
            // Password match validation for reset password
            const newPassword = document.getElementById('new_password');
            const confirmNewPassword = document.getElementById('confirm_new_password');
            
            if(newPassword && confirmNewPassword) {
                function checkNewPasswordMatch() {
                    if(newPassword.value !== '' && confirmNewPassword.value !== '') {
                        if(newPassword.value !== confirmNewPassword.value) {
                            confirmNewPassword.style.borderColor = '#c62828';
                        } else {
                            confirmNewPassword.style.borderColor = '#2e7d32';
                        }
                    }
                }
                
                newPassword.addEventListener('input', checkNewPasswordMatch);
                confirmNewPassword.addEventListener('input', checkNewPasswordMatch);
            }
        });
    </script>
</body>
</html>