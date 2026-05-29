<?php
$page_name = basename($_SERVER['PHP_SELF']);
$page_title = 'Dashboard';

switch($page_name) {
    case 'dashboard.php': $page_title = 'Enquiry Management'; break;
    case 'gallery.php': $page_title = 'Gallery Management'; break;
    case 'popup.php': $page_title = 'Promotional Popup'; break;
    case 'results.php': $page_title = 'Manage Results'; break;
    case 'settings.php': $page_title = 'Site Settings'; break;
    case 'change_password.php': $page_title = 'Security Settings'; break;
    default: $page_title = 'Admin Panel'; break;
}
?>
            <header class="top-header">
                <div class="header-title">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <?= $page_title ?>
                </div>
                <div class="header-actions">
                    <?php if($page_name == 'dashboard.php'): ?>
                    <!-- Notification Icon -->
                    <div class="notification-icon" title="Unread Enquiries">
                        <i class="fa-regular fa-bell"></i>
                        <?php if(isset($unread_count) && $unread_count > 0): ?>
                            <span class="badge"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="user-profile">
                        <img src="https://ui-avatars.com/api/?name=Admin+User&background=38bbf3&color=fff" alt="Admin Profile">
                        <span>Admin</span>
                    </div>
                </div>
            </header>
