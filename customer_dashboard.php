<?php
session_start();
require_once 'includes/db_connection.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle logout
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Handle order placement
if(isset($_POST['place_order'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Get product price
    $sql = "SELECT price, stock_quantity FROM products WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if($product && $quantity <= $product['stock_quantity']) {
        $total = $product['price'] * $quantity;
        
        // Create order
        $order_sql = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("id", $user_id, $total);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        
        // Add order details
        $detail_sql = "INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) 
                      VALUES (?, ?, ?, ?, ?)";
        $detail_stmt = $conn->prepare($detail_sql);
        $detail_stmt->bind_param("iiidd", $order_id, $product_id, $quantity, $product['price'], $total);
        $detail_stmt->execute();
        
        // Update stock
        $update_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $quantity, $product_id);
        $update_stmt->execute();
        
        echo "<script>alert('Order placed successfully!');</script>";
    } else {
        echo "<script>alert('Not enough stock available!');</script>";
    }
}

// Get user's orders
$orders_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
    <style>
        <?php include 'css/style.css'; ?>
        .customer-nav {
            background: #4CAF50;
            padding: 10px;
            margin-bottom: 20px;
        }
        .customer-nav a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Customer Dashboard</h1>
        <p>Welcome, <?php echo $username; ?>!</p>
        
        <div class="customer-nav">
            <a href="customer_dashboard.php">Home</a>
            <a href="#products">Products</a>
            <a href="#orders">My Orders</a>
            <a href="?logout=true">Logout</a>
        </div>
        
        <!-- Products Section -->
        <div id="products">
            <h2>Available Products</h2>
            <div class="product-grid">
                <?php
                $sql = "SELECT * FROM products WHERE stock_quantity > 0";
                $result = $conn->query($sql);
                
                if($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='product-card'>";
                        echo "<h3>" . $row['name'] . "</h3>";
                        echo "<p>" . $row['description'] . "</p>";
                        echo "<p><strong>Price: TZS " . number_format($row['price'], 0, '.', ',') . "</strong></p>";
                        echo "<p>Stock: " . $row['stock_quantity'] . "</p>";
                        echo "<form method='POST' action=''>";
                        echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
                        echo "<input type='number' name='quantity' value='1' min='1' max='" . $row['stock_quantity'] . "'>";
                        echo "<button type='submit' name='place_order'>Order Now</button>";
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No products available at the moment.</p>";
                }
                ?>
            </div>
        </div>
        
        <!-- Orders Section -->
        <div id="orders">
            <h2>My Orders</h2>
            <table border="1" width="100%">
                <tr>
                    <th>Order ID</th>
                    <th>Total Amount (TZS)</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                <?php
                if($orders_result->num_rows > 0) {
                    while($order = $orders_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $order['id'] . "</td>";
                        echo "<td>" . number_format($order['total_amount'], 0, '.', ',') . "</td>";
                        echo "<td>" . $order['status'] . "</td>";
                        echo "<td>" . $order['created_at'] . "</td>";
                        echo "<td><a href='view_order.php?id=" . $order['id'] . "'>View Details</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No orders yet.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>