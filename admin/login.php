<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Lakhan Public School</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body class="admin-login-page">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-box">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h2>LPS Admin</h2>
            <p>Sign in to manage your school</p>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error-msg">
                    <i class="fa-solid fa-triangle-exclamation"></i> Invalid username or password
                </div>
            <?php endif; ?>

            <form action="auth.php" method="POST">
                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder=" " required autocomplete="off">
                    <label for="username">Username</label>
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <label for="password">Password</label>
                    <i class="fa-solid fa-lock"></i>
                </div>
                <button type="submit" class="btn-login">
                    <span>Secure Login</span>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>
            
            <a href="../index.php" class="back-link"><i class="fa-solid fa-arrow-left-long"></i> Back to Website</a>
        </div>
    </div>
</body>
</html>

