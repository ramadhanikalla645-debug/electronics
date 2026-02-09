<?php
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