<?php
session_start();
require_once 'includes/db_connection.php';

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// Get product ID from URL
if(!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$product_id = $_GET['id'];

// Get product details from database
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    // Product not found
    header("Location: admin_dashboard.php");
    exit();
}

$product = $result->fetch_assoc();

$error = "";
$success = "";

// Handle form submission to update product
if(isset($_POST['update_product'])) {
    $name = $_POST['product_name'];
    $description = $_POST['product_description'];
    $price = $_POST['product_price'];
    $stock = $_POST['product_stock'];
    $category = $_POST['product_category'];
    
    // Simple validation
    if(empty($name) || empty($price) || empty($stock)) {
        $error = "Product name, price, and stock are required!";
    } else {
        // Update product in database
        $update_sql = "UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, category = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssdisi", $name, $description, $price, $stock, $category, $product_id);
        
        if($update_stmt->execute()) {
            $success = "Product updated successfully!";
            // Refresh product data
            $sql = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
        } else {
            $error = "Failed to update product!";
        }
    }
}

// Handle product deletion
if(isset($_POST['delete_product'])) {
    $delete_sql = "DELETE FROM products WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $product_id);
    
    if($delete_stmt->execute()) {
        header("Location: admin_dashboard.php?message=Product deleted successfully");
        exit();
    } else {
        $error = "Failed to delete product!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - Electronic Ordering System</title>
    <style>
        <?php include 'css/style.css'; ?>
        .product-form {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
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
        <h1>Edit Product</h1>
        <p>Welcome, <?php echo $username; ?>! | <a href="admin_dashboard.php">← Back to Dashboard</a></p>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="form-container product-form">
            <h2>Edit Product Details</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="product_name">Product Name:</label>
                    <input type="text" id="product_name" name="product_name" 
                           value="<?php echo htmlspecialchars($product['name']); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="product_description">Description:</label>
                    <textarea id="product_description" name="product_description" 
                              rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="product_price">Price (TZS):</label>
                    <input type="number" id="product_price" name="product_price" 
                           value="<?php echo $product['price']; ?>" 
                           step="1" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="product_stock">Stock Quantity:</label>
                    <input type="number" id="product_stock" name="product_stock" 
                           value="<?php echo $product['stock_quantity']; ?>" 
                           min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="product_category">Category:</label>
                    <input type="text" id="product_category" name="product_category" 
                           value="<?php echo htmlspecialchars($product['category']); ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                    <button type="submit" name="delete_product" class="btn btn-delete" 
                            onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                        Delete Product
                    </button>
                    <a href="admin_dashboard.php" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
        
        <!-- Display current product information -->
        <div class="product-info">
            <h3>Current Product Information</h3>
            <table border="1" width="100%">
                <tr>
                    <th>Field</th>
                    <th>Current Value</th>
                </tr>
                <tr>
                    <td><strong>Product ID</strong></td>
                    <td><?php echo $product['id']; ?></td>
                </tr>
                <tr>
                    <td><strong>Product Name</strong></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Description</strong></td>
                    <td><?php echo htmlspecialchars($product['description']); ?></td>
                </tr>
                <tr>
                    <td><strong>Price</strong></td>
                    <td>TZS <?php echo number_format($product['price'], 0, '.', ','); ?></td>
                </tr>
                <tr>
                    <td><strong>Stock Quantity</strong></td>
                    <td><?php echo $product['stock_quantity']; ?></td>
                </tr>
                <tr>
                    <td><strong>Category</strong></td>
                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                </tr>
                <tr>
                    <td><strong>Created At</strong></td>
                    <td><?php echo $product['created_at']; ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <script>
        // Simple JavaScript for form validation
        document.addEventListener('DOMContentLoaded', function() {
            // Add confirmation for delete button
            const deleteBtn = document.querySelector('button[name="delete_product"]');
            if(deleteBtn) {
                deleteBtn.addEventListener('click', function(e) {
                    if(!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            }
            
            // Validate price and stock are positive numbers
            const priceInput = document.getElementById('product_price');
            const stockInput = document.getElementById('product_stock');
            
            if(priceInput) {
                priceInput.addEventListener('change', function() {
                    if(this.value < 0) {
                        alert('Price cannot be negative!');
                        this.value = 0;
                    }
                });
            }
            
            if(stockInput) {
                stockInput.addEventListener('change', function() {
                    if(this.value < 0) {
                        alert('Stock quantity cannot be negative!');
                        this.value = 0;
                    }
                });
            }
        });
    </script>
</body>
</html>