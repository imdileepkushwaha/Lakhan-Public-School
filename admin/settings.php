<?php
require_once 'includes/session_guard.php';
admin_require_login();
require_once '../config/db.php';
require_once '../shared/get_settings.php';

$message = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact_email = filter_var($_POST['contact_email'] ?? '', FILTER_SANITIZE_EMAIL);
    $contact_phone1 = filter_var($_POST['contact_phone1'] ?? '', FILTER_SANITIZE_STRING);
    $contact_phone2 = filter_var($_POST['contact_phone2'] ?? '', FILTER_SANITIZE_STRING);
    $contact_landline = filter_var($_POST['contact_landline'] ?? '', FILTER_SANITIZE_STRING);
    $contact_address = filter_var($_POST['contact_address'] ?? '', FILTER_SANITIZE_STRING);
    
    $social_facebook = filter_var($_POST['social_facebook'] ?? '', FILTER_SANITIZE_URL);
    $social_instagram = filter_var($_POST['social_instagram'] ?? '', FILTER_SANITIZE_URL);
    $social_twitter = filter_var($_POST['social_twitter'] ?? '', FILTER_SANITIZE_URL);
    $social_youtube = filter_var($_POST['social_youtube'] ?? '', FILTER_SANITIZE_URL);

    try {
        $update_stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
        $update_stmt->execute([$contact_email, 'contact_email']);
        $update_stmt->execute([$contact_phone1, 'contact_phone1']);
        $update_stmt->execute([$contact_phone2, 'contact_phone2']);
        $update_stmt->execute([$contact_landline, 'contact_landline']);
        $update_stmt->execute([$contact_address, 'contact_address']);
        
        $update_stmt->execute([$social_facebook, 'social_facebook']);
        $update_stmt->execute([$social_instagram, 'social_instagram']);
        $update_stmt->execute([$social_twitter, 'social_twitter']);
        $update_stmt->execute([$social_youtube, 'social_youtube']);
        
        $message = 'Settings updated successfully.';
        $msg_type = 'success';
        
        // Refresh settings
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $site_settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch(PDOException $e) {
        $message = 'Error updating settings: ' . $e->getMessage();
        $msg_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Settings - Lakhan Public School Admin</title>
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
                <h2 class="page-title"><i class="fa-solid fa-sliders"></i> Global Settings</h2>
                
                <?php if($message): ?>
                    <div class="alert-<?= $msg_type ?>">
                        <i class="fa-solid fa-circle-info"></i> <?= $message ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="settings-grid">
                        <!-- Column 1: Contact Info (2/3 width) -->
                        <div class="settings-form">
                            <h3><i class="fa-solid fa-address-book"></i> Contact Information</h3>
                            
                            <div class="form-group-full">
                                <label>Email Address</label>
                                <input type="email" name="contact_email" value="<?= htmlspecialchars(get_setting('contact_email')) ?>" required>
                            </div>
                            <div class="phone-grid">
                                <div class="form-group-full">
                                    <label>Primary Mobile Number</label>
                                    <input type="text" name="contact_phone1" value="<?= htmlspecialchars(get_setting('contact_phone1')) ?>" required>
                                </div>
                                <div class="form-group-full">
                                    <label>Secondary Mobile Number</label>
                                    <input type="text" name="contact_phone2" value="<?= htmlspecialchars(get_setting('contact_phone2')) ?>">
                                </div>
                            </div>
                            <div class="form-group-full">
                                <label>Landline Number</label>
                                <input type="text" name="contact_landline" value="<?= htmlspecialchars(get_setting('contact_landline')) ?>">
                            </div>
                            <div class="form-group-full">
                                <label>Physical Address</label>
                                <textarea name="contact_address" rows="3" required><?= htmlspecialchars(get_setting('contact_address')) ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Column 2: Social Links (1/3 width) -->
                        <div>
                            <div class="settings-form">
                                <h3><i class="fa-solid fa-share-nodes"></i> Social Links</h3>
                                
                                <div class="form-group-full">
                                    <label>Facebook URL</label>
                                    <div class="input-with-icon">
                                        <i class="fa-brands fa-facebook color-fb"></i>
                                        <input type="text" name="social_facebook" value="<?= htmlspecialchars(get_setting('social_facebook')) ?>" placeholder="https://facebook.com/...">
                                    </div>
                                </div>
                                
                                <div class="form-group-full">
                                    <label>Instagram URL</label>
                                    <div class="input-with-icon">
                                        <i class="fa-brands fa-instagram color-ig"></i>
                                        <input type="text" name="social_instagram" value="<?= htmlspecialchars(get_setting('social_instagram')) ?>" placeholder="https://instagram.com/...">
                                    </div>
                                </div>
                                
                                <div class="form-group-full">
                                    <label>Twitter / X URL</label>
                                    <div class="input-with-icon">
                                        <i class="fa-brands fa-twitter color-tw"></i>
                                        <input type="text" name="social_twitter" value="<?= htmlspecialchars(get_setting('social_twitter')) ?>" placeholder="https://twitter.com/...">
                                    </div>
                                </div>
                                
                                <div class="form-group-full">
                                    <label>YouTube URL</label>
                                    <div class="input-with-icon">
                                        <i class="fa-brands fa-youtube color-yt"></i>
                                        <input type="text" name="social_youtube" value="<?= htmlspecialchars(get_setting('social_youtube')) ?>" placeholder="https://youtube.com/...">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Save Button outside so it aligns well or below -->
                            <button type="submit" class="settings-btn">
                                <i class="fa-solid fa-save"></i> Save All Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <?php include 'includes/footer.php'; ?>
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




