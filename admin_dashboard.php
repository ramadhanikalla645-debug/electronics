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

// Add new product
if(isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $description = $_POST['product_description'];
    $price = $_POST['product_price'];
    $stock = $_POST['product_stock'];
    $category = $_POST['product_category'];
    
    $sql = "INSERT INTO products (name, description, price, stock_quantity, category) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdis", $name, $description, $price, $stock, $category);
    $stmt->execute();
}

// Update product
if(isset($_POST['update_product'])) {
    $id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $description = $_POST['product_description'];
    $price = $_POST['product_price'];
    $stock = $_POST['product_stock'];
    $category = $_POST['product_category'];
    
    $sql = "UPDATE products SET name=?, description=?, price=?, stock_quantity=?, category=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $category, $id);
    $stmt->execute();
}

// Delete product
if(isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $sql = "DELETE FROM products WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
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
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?php echo $username; ?>!</p>
        
        <div class="admin-nav">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_product.php">Manage Products</a>
            <a href="order_management.php">View Orders</a>
            <a href="manage_user.php">Manage Users</a>
            <a href="search.php">Search</a>
            <a href="?logout=true">Logout</a>
        </div>
        
        <!-- Add Product Form -->
        <div class="form-container">
            <h2>Add New Product</h2>
            <form method="POST" action="">
                <input type="text" name="product_name" placeholder="Product Name" required>
                <textarea name="product_description" placeholder="Description"></textarea>
                <input type="number" step="1" name="product_price" placeholder="Price in TZS" required>
                <input type="number" name="product_stock" placeholder="Stock Quantity" required>
                <input type="text" name="product_category" placeholder="Category">
                <button type="submit" name="add_product">Add Product</button>
            </form>
        </div>
        
        <!-- Product List -->
        <div id="products">
            <h2>All Products</h2>
            <table border="1" width="100%">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price (TZS)</th>
                    <th>Stock</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
                <?php
                $sql = "SELECT * FROM products";
                $result = $conn->query($sql);
                
                if($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['name'] . "</td>";
                        echo "<td>" . number_format($row['price'], 0, '.', ',') . "</td>";
                        echo "<td>" . $row['stock_quantity'] . "</td>";
                        echo "<td>" . $row['category'] . "</td>";
                        echo "<td>";
                        echo "<a href='?delete_product=" . $row['id'] . "'>Delete</a> | ";
                        echo "<a href='edit_product.php?id=" . $row['id'] . "'>Edit</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </table>
        </div>
        
        <!-- Orders List -->
        <div id="orders">
            <h2>All Orders</h2>
            <table border="1" width="100%">
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total (TZS)</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                <?php
                if($orders_result->num_rows > 0) {
                    while($order = $orders_result->fetch_assoc()) {
                        // Get customer name
                        $user_sql = "SELECT username FROM users WHERE id=?";
                        $user_stmt = $conn->prepare($user_sql);
                        $user_stmt->bind_param("i", $order['user_id']);
                        $user_stmt->execute();
                        $user_result = $user_stmt->get_result();
                        $user = $user_result->fetch_assoc();
                        
                        echo "<tr>";
                        echo "<td>" . $order['id'] . "</td>";
                        echo "<td>" . $user['username'] . "</td>";
                        echo "<td>" . number_format($order['total_amount'], 0, '.', ',') . "</td>";
                        echo "<td>" . $order['status'] . "</td>";
                        echo "<td>" . $order['created_at'] . "</td>";
                        echo "<td><a href='view_order.php?id=" . $order['id'] . "'>View</a></td>";
                        echo "</tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>
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