<?php
require_once 'config/db.php';
$pdo->exec("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('popup_image', ''), ('popup_active', '0')");
if (!is_dir('images/popup')) {
    mkdir('images/popup', 0777, true);
}
echo 'Keys added and directory created.';
?>
