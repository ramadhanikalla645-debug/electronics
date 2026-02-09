<?php
session_start();
require_once 'includes/db_connection.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get order details
$sql = "SELECT o.*, u.username FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

// Check if user has permission to view this order
if($role != 'admin' && $order['user_id'] != $user_id) {
    header("Location: " . ($role == 'admin' ? 'admin_dashboard.php' : 'customer_dashboard.php'));
    exit();
}

// Get order items
$items_sql = "SELECT od.*, p.name FROM order_details od 
              JOIN products p ON od.product_id = p.id 
              WHERE od.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <style><?php include 'css/style.css'; ?></style>
</head>
<body>
    <div class="container">
        <h1>Order Details #<?php echo $order_id; ?></h1>
        <p><a href="<?php echo ($role == 'admin' ? 'admin_dashboard.php' : 'customer_dashboard.php'); ?>">← Back</a></p>
        
        <h2>Order Information</h2>
        <p><strong>Customer:</strong> <?php echo $order['username']; ?></p>
        <p><strong>Total Amount:</strong> TZS <?php echo number_format($order['total_amount'], 0, '.', ','); ?></p>
        <p><strong>Status:</strong> <?php echo $order['status']; ?></p>
        <p><strong>Order Date:</strong> <?php echo $order['created_at']; ?></p>
        
        <h2>Order Items</h2>
        <table border="1" width="100%">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price (TZS)</th>
                <th>Subtotal (TZS)</th>
            </tr>
            <?php
            if($items_result->num_rows > 0) {
                while($item = $items_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $item['name'] . "</td>";
                    echo "<td>" . $item['quantity'] . "</td>";
                    echo "<td>" . number_format($item['unit_price'], 0, '.', ',') . "</td>";
                    echo "<td>" . number_format($item['subtotal'], 0, '.', ',') . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>
    </div>
</body>
</html>