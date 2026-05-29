<?php
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $source = $_POST['source'] ?? 'index.php';
    $redirect_base = ($source === 'contact.php') ? 'contact.php' : 'index.php';
    $redirect_anchor = ($source === 'contact.php') ? '' : '#contact';
    
    $redirect = function($param) use ($redirect_base, $redirect_anchor) {
        header("Location: {$redirect_base}?{$param}{$redirect_anchor}");
        exit;
    };

    // Sanitize and validate input
    $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
    $message = filter_var($_POST['message'] ?? '', FILTER_SANITIZE_STRING);
    
    $captcha = $_POST['captcha'] ?? '';
    $captcha_hash = $_POST['captcha_hash'] ?? '';

    // Check captcha
    if (md5($captcha . 'LPS_SECRET_SALT') !== $captcha_hash) {
        $redirect('error=captcha');
    }

    if (empty($name) || empty($email) || empty($phone)) {
        $redirect('error=empty');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $redirect('error=email');
    }
    
    if (strlen($phone) !== 10) {
        $redirect('error=phone');
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO enquiries (name, email, phone, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $message]);
        $redirect('success=1');
    } catch(PDOException $e) {
        $redirect('error=db');
    }
} else {
    header("Location: index.php");
    exit;
}
?>
