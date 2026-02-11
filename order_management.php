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
        <h1>View orders</h1>
        <p>Welcome, <?php echo $username; ?>!</p>
          
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
</body>
</html>