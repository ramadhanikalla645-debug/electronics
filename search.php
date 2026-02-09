<?php
require_once 'includes/db_connection.php';
// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}
$search ="";
$sql = "SELECT *FROM products";

if(isset($_GET["search"]) && $_GET["search"]!=""){
    $search = strtolower(trim($_GET["search"]));
    // category based
    if($search== "mobile" || $search== "mobile phones" || $search== "phones"){
        $sql .= "SELECT *FROM products WHERE category= 'Mobile Phones'";
    }
    elseif($search =="laptop" || $search== "laptops"){
        $sql .= "SELECT *FROM products WHERE category='Laptops'";
    }
    elseif($search== "accessories"){
        $sql .= "SELECT * FROM products WHERE category='Accessories'";
    }
    elseif($search== "monitors"){
        $sql .= "SELECT *FROM products WHERE category='Monitors'";
    }
    else{
        // product name search
        $stmt = $conn->prepare("SELECT *FROM products WHERE name LIKE ?");
        $value = "%$search%";
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result();
    }
}
// run query 
if(!isset($result)){
$result = mysqli_query($conn, $sql);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products search</title>
</head>
<body>
  <h2>Search products</h2> 
  <form method="get" action="search.php" >
    <input type="text" name="search" placeholder="search products....." value=<php? echo htmlspecialchars($search); ?>>
    <button type="submit">search</button>
  </form>
<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Category</th>
    </tr>
    <?php
    if(mysqli_num_rows($result)>0){
        while($row = mysqli_fetch_assoc($result)){
            echo "<tr>";
            echo "<td>" .$row["id"]."</td>";
             echo "<td>" .$row["name"]."</td>";
              echo "<td>" .$row["price"]."</td>";
               echo "<td>" .$row["stock"]."</td>";
                echo "<td>" .$row["category"]."</td>";
                echo "</tr>";
        }
    }
    else{
        echo "<tr><td colspan='5'>No result found</td></tr>";
    }
    ?>
</table>
</body>
</html>