<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fa-solid fa-school"></i> LPS Admin
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>"><i class="fa-solid fa-home"></i> Dashboard</a></li>
                <li><a href="gallery.php" class="<?= ($current_page == 'gallery.php') ? 'active' : '' ?>"><i class="fa-solid fa-images"></i> Manage Gallery</a></li>
                <li><a href="popup.php" class="<?= ($current_page == 'popup.php') ? 'active' : '' ?>"><i class="fa-solid fa-bullhorn"></i> Promotional Popup</a></li>
                <li><a href="results.php" class="<?= ($current_page == 'results.php') ? 'active' : '' ?>"><i class="fa-solid fa-trophy"></i> Outstanding Results</a></li>
                <li><a href="announcements.php" class="<?= ($current_page == 'announcements.php') ? 'active' : '' ?>"><i class="fa-solid fa-bell"></i> Announcements</a></li>
                <li><a href="settings.php" class="<?= ($current_page == 'settings.php') ? 'active' : '' ?>"><i class="fa-solid fa-cog"></i> Global Settings</a></li>
                <li><a href="change_password.php" class="<?= ($current_page == 'change_password.php') ? 'active' : '' ?>"><i class="fa-solid fa-key"></i> Change Password</a></li>
                <li><a href="../index.php" target="_blank"><i class="fa-solid fa-globe"></i> View Website</a></li>
                <li style="margin-top: auto;"><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>
