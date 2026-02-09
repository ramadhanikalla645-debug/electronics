<?php
session_start();
require_once 'includes/db_connection.php';

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// Handle logout
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Handle add new user
if(isset($_POST['add_user'])) {
    $new_username = $_POST['new_username'];
    $new_email = $_POST['new_email'];
    $new_password = $_POST['new_password'];
    $new_role = $_POST['new_role'];
    
    // Check if user already exists
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $new_username, $new_email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows > 0) {
        echo "<script>alert('Username or email already exists!');</script>";
    } else {
        // Hash password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Insert new user
        $insert_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssss", $new_username, $new_email, $hashed_password, $new_role);
        
        if($insert_stmt->execute()) {
            echo "<script>alert('User added successfully!');</script>";
            // Refresh page to show new user
            echo "<script>window.location.href = 'admin_dashboard.php#users';</script>";
        } else {
            echo "<script>alert('Failed to add user!');</script>";
        }
    }
}

// Handle delete user
if(isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    
    // Prevent admin from deleting themselves
    if($user_id == $_SESSION['user_id']) {
        echo "<script>alert('You cannot delete your own account!');</script>";
    } else {
        // Delete user
        $delete_sql = "DELETE FROM users WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user_id);
        
        if($delete_stmt->execute()) {
            echo "<script>alert('User deleted successfully!');</script>";
            // Refresh page
            echo "<script>window.location.href = 'admin_dashboard.php#users';</script>";
        } else {
            echo "<script>alert('Failed to delete user!');</script>";
        }
    }
}

// View all orders
$orders_sql = "SELECT * FROM orders ORDER BY created_at DESC";
$orders_result = $conn->query($orders_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        <?php include 'css/style.css'; ?>
        .admin-nav {
            background: #255e28;
            padding: 10px;
            margin-bottom: 20px;
        }
        .admin-nav a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User management</h1>
        <p>Welcome, <?php echo $username; ?>!</p>
            <!-- Users Management Section -->
        <div id="users">
            <h2>Manage Users</h2>
            <table border="1" width="100%">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
                <?php
                $users_sql = "SELECT * FROM users ORDER BY created_at DESC";
                $users_result = $conn->query($users_sql);
                
                if($users_result->num_rows > 0) {
                    while($user = $users_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $user['id'] . "</td>";
                        echo "<td>" . $user['username'] . "</td>";
                        echo "<td>" . $user['email'] . "</td>";
                        echo "<td>" . $user['role'] . "</td>";
                        echo "<td>" . $user['created_at'] . "</td>";
                        echo "<td>";
                        echo "<a href='edit_user.php?id=" . $user['id'] . "'>Edit</a> | ";
                        echo "<a href='?delete_user=" . $user['id'] . "' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </table>
            
            <!-- Add New User Form -->
            <div class="form-container" style="margin-top: 30px;">
                <h3>Add New User</h3>
                <form method="POST" action="">
                    <input type="text" name="new_username" placeholder="Username" required>
                    <input type="email" name="new_email" placeholder="Email" required>
                    <input type="password" name="new_password" placeholder="Password" required>
                    <select name="new_role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                    </select>
                    <button type="submit" name="add_user">Add User</button>
                </form>
            </div>
        </div>
</body>
</html>