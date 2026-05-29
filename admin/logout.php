<?php
require_once 'includes/session_guard.php';
admin_destroy_session();

$redirect = isset($_GET['expired']) ? 'login.php?expired=1' : 'login.php';
header('Location: ' . $redirect);
exit;
?>
