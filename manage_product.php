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

?>
<!DOCTYPE html>
<html>
<head>
    <title>Products management</title>
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
        
        
            <!-- Users Management Section -->
        
        </div>
</body>
</html>