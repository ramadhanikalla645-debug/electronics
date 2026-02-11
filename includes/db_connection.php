<?php
<<<<<<< HEAD
// Show errors during development (you can disable later)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get database credentials from Railway
$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$pass = getenv("DB_PASSWORD");
$db   = getenv("DB_NAME");

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
=======
// Database connection settings
$host = "localhost";
$username = "root";
$password = "";
$database = "electronic_ordering_system";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

?>
>>>>>>> ea9b9dbab2bc99c8608c356789beb990d9a9a65e
