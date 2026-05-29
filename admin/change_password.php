<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $admin_id = $_SESSION['admin_id'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all fields.";
        $msg_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
        $msg_type = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = "New password must be at least 6 characters long.";
        $msg_type = 'error';
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM admin_users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password'])) {
            // Update to new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            if ($update_stmt->execute([$hashed_password, $admin_id])) {
                $message = "Password updated successfully!";
                $msg_type = 'success';
            } else {
                $message = "Failed to update password. Please try again.";
                $msg_type = 'error';
            }
        } else {
            $message = "Incorrect current password.";
            $msg_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Lakhan Public School Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <?php include 'includes/header.php'; ?>

            <!-- Dashboard Body -->
            <div class="dashboard-body">
                <h2 class="page-title center"><i class="fa-solid fa-lock"></i> Change Password</h2>
                
                <div class="form-container">
                    <?php if($message): ?>
                        <div class="alert-<?= $msg_type ?>">
                            <i class="fa-solid fa-circle-info"></i> <?= $message ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="settings-form">
                        <div class="form-group-full">
                            <label for="current_password">Current Password</label>
                            <div class="password-group">
                                <input type="password" id="current_password" name="current_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('current_password', this)">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group-full">
                            <label for="new_password">New Password</label>
                            <div class="password-group">
                                <input type="password" id="new_password" name="new_password" required minlength="6">
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group-full">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="password-group">
                                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="settings-btn">
                            <i class="fa-solid fa-key"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>
    
    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        // Password Show/Hide Toggle
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>




