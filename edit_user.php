<?php
session_start();
require_once 'includes/db_connection.php';

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// Get user ID from URL
if(!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = $_GET['id'];

// Get user details from database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    // User not found
    header("Location: admin_dashboard.php");
    exit();
}

$user = $result->fetch_assoc();

$error = "";
$success = "";

// Handle form submission to update user
if(isset($_POST['update_user'])) {
    $name = $_POST['user_name'];
    $email = $_POST['user_email'];
    $role = $_POST['user_role'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Simple validation
    if(empty($name) || empty($email) || empty($role)) {
        $error = "Username, email, and role are required!";
    } elseif($new_password != "" && $new_password != $confirm_password) {
        $error = "Passwords don't match!";
    } else {
        // Check if username or email already exists (excluding current user)
        $check_sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ssi", $name, $email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            // Update user in database
            if($new_password != "") {
                // Update with new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ssssi", $name, $email, $role, $hashed_password, $user_id);
            } else {
                // Update without changing password
                $update_sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sssi", $name, $email, $role, $user_id);
            }
            
            if($update_stmt->execute()) {
                $success = "User updated successfully!";
                // Refresh user data
                $sql = "SELECT * FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error = "Failed to update user!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User - Electronic Ordering System</title>
    <style>
        <?php include 'css/style.css'; ?>
        .user-form {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-cancel {
            background-color: #6c757d;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit User</h1>
        <p>Welcome, <?php echo $username; ?>! | <a href="admin_dashboard.php#users">← Back to Users</a></p>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="form-container user-form">
            <h2>Edit User Details</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="user_name">Username:</label>
                    <input type="text" id="user_name" name="user_name" 
                           value="<?php echo htmlspecialchars($user['username']); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="user_email">Email:</label>
                    <input type="email" id="user_email" name="user_email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="user_role">Role:</label>
                    <select id="user_role" name="user_role" required>
                        <option value="customer" <?php echo $user['role'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password (leave blank to keep current):</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                    <a href="admin_dashboard.php#users" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
        
        <!-- Display current user information -->
        <div class="user-info">
            <h3>Current User Information</h3>
            <table border="1" width="100%">
                <tr>
                    <th>Field</th>
                    <th>Current Value</th>
                </tr>
                <tr>
                    <td><strong>User ID</strong></td>
                    <td><?php echo $user['id']; ?></td>
                </tr>
                <tr>
                    <td><strong>Username</strong></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                </tr>
                <tr>
                    <td><strong>Role</strong></td>
                    <td><?php echo $user['role']; ?></td>
                </tr>
                <tr>
                    <td><strong>Joined Date</strong></td>
                    <td><?php echo $user['created_at']; ?></td>
                </tr>
            </table>
            
            <?php
            // Show user's orders if they are a customer
            if($user['role'] == 'customer') {
                $orders_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at ASC";
                $orders_stmt = $conn->prepare($orders_sql);
                $orders_stmt->bind_param("i", $user_id);
                $orders_stmt->execute();
                $orders_result = $orders_stmt->get_result();
                
                if($orders_result->num_rows > 0) {
                    echo "<h4>User's Orders</h4>";
                    echo "<table border='1' width='100%'>";
                    echo "<tr><th>Order ID</th><th>Total Amount</th><th>Status</th><th>Date</th><th>Actions</th></tr>";
                    
                    while($order = $orders_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $order['id'] . "</td>";
                        echo "<td>TZS " . number_format($order['total_amount'], 0, '.', ',') . "</td>";
                        echo "<td>" . $order['status'] . "</td>";
                        echo "<td>" . $order['created_at'] . "</td>";
                        echo "<td><a href='view_order.php?id=" . $order['id'] . "'>View</a></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>This user has no orders yet.</p>";
                }
            }
            ?>
        </div>
    </div>
    
    <script>
        // Simple JavaScript for form validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Check if passwords match
            function checkPasswords() {
                if(newPassword.value != "" && newPassword.value != confirmPassword.value) {
                    confirmPassword.style.borderColor = '#dc3545';
                    return false;
                } else {
                    confirmPassword.style.borderColor = '';
                    return true;
                }
            }
            
            if(newPassword && confirmPassword) {
                newPassword.addEventListener('input', checkPasswords);
                confirmPassword.addEventListener('input', checkPasswords);
            }
            
            // Form submission validation
            const form = document.querySelector('form');
            if(form) {
                form.addEventListener('submit', function(e) {
                    if(!checkPasswords()) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                    }
                });
            }
        });
    </script>
</body>
</html>