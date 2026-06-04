<?php
require_once 'includes/session_guard.php';
admin_require_login();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = filter_var($_POST['title'] ?? '', FILTER_SANITIZE_STRING);
    
    if (empty($title)) {
        header("Location: gallery.php?error=1");
        exit;
    }
    
    if (!isset($_FILES['image']) || empty($_FILES['image']['name'][0])) {
        header("Location: gallery.php?error=1");
        exit;
    }
    
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $upload_dir = '../images/gallery/';
    $min_size = 500 * 1024;
    $max_size = 2 * 1024 * 1024;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $success_count = 0;
    $error_occurred = false;
    $size_error = false;

    // Loop through each uploaded file
    $file_count = count($_FILES['image']['name']);
    for ($i = 0; $i < $file_count; $i++) {
        if ($_FILES['image']['error'][$i] !== UPLOAD_ERR_OK) {
            $error_occurred = true;
            continue;
        }

        $file_name = $_FILES['image']['name'][$i];
        $file_tmp = $_FILES['image']['tmp_name'][$i];
        $file_size = $_FILES['image']['size'][$i];
        
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $error_occurred = true;
            continue;
        }
        
        if ($file_size < $min_size || $file_size > $max_size) {
            $error_occurred = true;
            $size_error = true;
            continue;
        }
        
        $filename = uniqid('img_') . '_' . rand(100,999) . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file_tmp, $upload_path)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO gallery (title, image_filename) VALUES (?, ?)");
                $stmt->execute([$title, $filename]);
                $success_count++;
            } catch(PDOException $e) {
                unlink($upload_path);
                $error_occurred = true;
            }
        } else {
            $error_occurred = true;
        }
    }

    if ($success_count > 0) {
        header("Location: gallery.php?success=1");
    } elseif ($size_error) {
        header("Location: gallery.php?error=size");
    } else {
        header("Location: gallery.php?error=1");
    }
    exit;
} else {
    header("Location: gallery.php");
    exit;
}
?>
