<?php
// Auto-detect environment (Local vs Live Server)
$http_host = $_SERVER['HTTP_HOST'] ?? '';
$is_local = ($http_host === 'localhost' || $http_host === '127.0.0.1' || strpos($http_host, 'localhost:') === 0);

if ($is_local) {
    // Localhost Database Credentials (XAMPP/WAMP)
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'lps_db';
} else {
    // Live Server Database Credentials (cPanel / Hosting)
    // UPDATE THESE WITH YOUR LIVE SERVER DETAILS
    $host = 'localhost'; 
    $user = 'your_live_db_user'; 
    $pass = 'your_live_db_pass'; 
    $dbname = 'your_live_db_name'; 
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
