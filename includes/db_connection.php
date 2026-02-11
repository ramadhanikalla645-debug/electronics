<?php
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
