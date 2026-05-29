<?php
session_start();
require_once '../config/db.php';
require_once '../shared/get_settings.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$msg_type = '';

// Handle actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Toggle Status
    if (isset($_POST['action']) && $_POST['action'] == 'toggle') {
        $new_status = (get_setting('popup_active') == '1') ? '0' : '1';
        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'popup_active'");
        $stmt->execute([$new_status]);
        $message = "Popup status updated.";
        $msg_type = 'success';
    }
    
    // Delete Image
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $current_image = get_setting('popup_image');
        if ($current_image && file_exists('../images/popup/' . $current_image)) {
            unlink('../images/popup/' . $current_image);
        }
        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = '' WHERE setting_key = 'popup_image'");
        $stmt->execute();
        
        // Also turn off popup
        $stmt2 = $pdo->prepare("UPDATE site_settings SET setting_value = '0' WHERE setting_key = 'popup_active'");
        $stmt2->execute();
        
        $message = "Popup image deleted.";
        $msg_type = 'success';
    }
    
    // Upload Image
    if (isset($_POST['action']) && $_POST['action'] == 'upload' && isset($_FILES['popup_file'])) {
        $file = $_FILES['popup_file'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $file['tmp_name']);
            finfo_close($file_info);
            
            if (in_array($mime_type, $allowed_types)) {
                // Delete old image if exists
                $current_image = get_setting('popup_image');
                if ($current_image && file_exists('../images/popup/' . $current_image)) {
                    unlink('../images/popup/' . $current_image);
                }
                
                // Save new image
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = 'popup_' . time() . '.' . $extension;
                $target_path = '../images/popup/' . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'popup_image'");
                    $stmt->execute([$new_filename]);
                    
                    // Auto turn on
                    $stmt2 = $pdo->prepare("UPDATE site_settings SET setting_value = '1' WHERE setting_key = 'popup_active'");
                    $stmt2->execute();
                    
                    $message = "New popup image uploaded successfully.";
                    $msg_type = 'success';
                } else {
                    $message = "Failed to upload image.";
                    $msg_type = 'error';
                }
            } else {
                $message = "Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed.";
                $msg_type = 'error';
            }
        } else {
            $message = "Please select a valid image file.";
            $msg_type = 'error';
        }
    }
    
    // Refresh settings
    $site_settings = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $site_settings[$row['setting_key']] = $row['setting_value'];
    }
}

$popup_image = get_setting('popup_image');
$is_active = (get_setting('popup_active') == '1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Popup - Lakhan Public School Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <div class="dashboard-body">
                <h2 class="page-title"><i class="fa-solid fa-bullhorn"></i> Manage Homepage Popup</h2>
                
                <?php if($message): ?>
                    <div class="alert-<?= $msg_type ?>">
                        <i class="fa-solid fa-circle-info"></i> <?= $message ?>
                    </div>
                <?php endif; ?>
                
                <div class="popup-manager">
                    <!-- Column 1: Upload & Controls -->
                    <div class="settings-form">
                        <h3><i class="fa-solid fa-upload"></i> Upload New Popup</h3>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload">
                            
                            <div class="upload-area" onclick="document.getElementById('popup_file').click();">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <h4>Click to select an image</h4>
                                <p class="form-hint">(JPG, PNG, WEBP max 2MB)</p>
                            </div>
                            <input type="file" id="popup_file" name="popup_file" hidden accept="image/jpeg, image/png, image/webp, image/gif" onchange="document.getElementById('file-name').textContent = this.files[0].name;">
                            <div id="file-name" class="file-name-display"></div>
                            
                            <button type="submit" class="btn btn-primary btn-full">
                                <i class="fa-solid fa-upload"></i> Upload & Enable
                            </button>
                        </form>
                    </div>

                    <!-- Column 2: Current Status & Preview -->
                    <div class="settings-form">
                        <h3><i class="fa-solid fa-eye"></i> Current Popup</h3>
                        
                        <?php if($popup_image && file_exists('../images/popup/' . $popup_image)): ?>
                            <div class="flex-between">
                                <div class="status-badge <?= $is_active ? 'status-on' : 'status-off' ?>">
                                    <i class="fa-solid <?= $is_active ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> 
                                    Status: <?= $is_active ? 'Active (Showing on Homepage)' : 'Inactive (Hidden)' ?>
                                </div>
                                
                                <div class="flex-gap mb-20">
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="toggle">
                                        <?php if($is_active): ?>
                                            <button type="submit" class="btn btn-danger" title="Turn Off">
                                                <i class="fa-solid fa-power-off"></i> Turn Off
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-success" title="Turn On">
                                                <i class="fa-solid fa-power-off"></i> Turn On
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    
                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this popup image completely?');">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-delete-img" title="Delete Image">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="preview-area">
                                <img src="../images/popup/<?= htmlspecialchars($popup_image) ?>" alt="Current Popup">
                            </div>
                        <?php else: ?>
                            <div class="preview-area preview-empty">
                                <div class="empty-state">
                                    <i class="fa-solid fa-image-slash"></i>
                                    <h4>No Popup Image Uploaded</h4>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
    </script>
</body>
</html>




