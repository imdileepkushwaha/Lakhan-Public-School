<?php
// Database configuration
$host = "localhost";
$username = "root"; // Update this with your actual database username
$password = ""; // Update this with your actual database password
$database = "LPS_db"; // The database name you requested

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Optionally, you can set charset
$conn->set_charset("utf8");

// Usage example:
// $sql = "SELECT * FROM users";
// $result = $conn->query($sql);
?>
