<?php
require_once 'config/db.php';
$pdo->exec("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('social_facebook', '#'), ('social_instagram', '#'), ('social_twitter', '#'), ('social_youtube', '#')");
echo 'Done';
?>
