<?php
/**
 * One-time admin password reset (localhost only).
 * Delete this file after use on production.
 */
require_once '../config/db.php';

$http_host = $_SERVER['HTTP_HOST'] ?? '';
$is_local = ($http_host === 'localhost' || $http_host === '127.0.0.1' || strpos($http_host, 'localhost:') === 0);

if (!$is_local) {
    http_response_code(403);
    die('Password reset is only allowed on localhost.');
}

$username = 'admin';
$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin) {
    $update = $pdo->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
    $update->execute([$hash, $username]);
    $message = 'Password updated successfully.';
} else {
    $insert = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
    $insert->execute([$username, $hash]);
    $message = 'Admin user created successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body class="admin-login-page">
    <div class="login-wrapper">
        <div class="login-card">
            <h2>Admin Reset</h2>
            <p style="color:#155724;background:#d4edda;padding:12px;border-radius:8px;"><?= htmlspecialchars($message) ?></p>
            <p><strong>Username:</strong> admin<br><strong>Password:</strong> admin123</p>
            <a href="login.php" class="btn-login" style="display:inline-block;text-decoration:none;margin-top:15px;">Go to Login</a>
        </div>
    </div>
</body>
</html>
