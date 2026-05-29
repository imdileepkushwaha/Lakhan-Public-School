<?php
require_once 'includes/session_guard.php';
admin_require_login();
require_once '../config/db.php';

$upload_dir = '../uploads/announcements/';

// Create directory if not exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle Add Announcement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = isset($_POST['status']) ? 'active' : 'inactive';
    $attachment_filename = null;

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed_exts)) {
            $attachment_filename = uniqid('ann_') . '.' . $ext;
            move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $attachment_filename);
        }
    }

    if (!empty($title) && !empty($description)) {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, description, attachment_filename, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $attachment_filename, $status]);
        header("Location: announcements.php?success=1");
        exit;
    } else {
        header("Location: announcements.php?error=1");
        exit;
    }
}

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get file to delete
    $stmt = $pdo->prepare("SELECT attachment_filename FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $ann = $stmt->fetch();
    
    if ($ann && $ann['attachment_filename']) {
        $file_path = $upload_dir . $ann['attachment_filename'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: announcements.php?deleted=1");
    exit;
}

// Handle Status Toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $pdo->prepare("SELECT status FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $ann = $stmt->fetch();
    
    if ($ann) {
        $new_status = ($ann['status'] == 'active') ? 'inactive' : 'active';
        $stmt = $pdo->prepare("UPDATE announcements SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
    }
    header("Location: announcements.php?updated=1");
    exit;
}

// Fetch all announcements
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements - Lakhan Public School Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        .upload-form {
            background: #ffffff;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            margin-bottom: 40px;
            border: 1px solid rgba(0,0,0,0.03);
            position: relative;
            overflow: hidden;
        }
        .upload-form::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6a00, #ee0979);
        }
        .upload-form h3 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #1a1a1a;
            font-weight: 800;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .upload-form h3 i {
            color: #ee0979;
            background: rgba(238, 9, 121, 0.1);
            padding: 12px;
            border-radius: 12px;
        }
        .premium-input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        .premium-input:focus {
            border-color: #ee0979;
            outline: none;
            box-shadow: 0 0 0 4px rgba(238, 9, 121, 0.1);
            background: #fff;
        }
        .file-input-wrapper {
            position: relative;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .file-input-wrapper:hover {
            border-color: #ee0979;
            background: rgba(238, 9, 121, 0.02);
        }
        .file-input-wrapper input[type="file"] {
            width: 100%;
            cursor: pointer;
        }
        .publish-btn {
            background: linear-gradient(135deg, #ff6a00, #ee0979);
            border: none;
            padding: 14px 30px;
            border-radius: 30px;
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(238, 9, 121, 0.3);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .publish-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(238, 9, 121, 0.4);
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #4a5568;
            font-size: 0.95rem;
        }
        
        .desc-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.9rem;
            color: #555;
            margin-top: 5px;
        }
        .attachment-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #eef2f7;
            color: #ee0979;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid rgba(238, 9, 121, 0.1);
        }
        .attachment-badge:hover {
            background: #ee0979;
            color: #fff;
            box-shadow: 0 4px 15px rgba(238, 9, 121, 0.3);
            transform: translateY(-2px);
        }
        .alert-success, .alert-info, .alert-error {
            border-radius: 12px;
            font-weight: 600;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            border-left: 5px solid;
            background: #fff;
        }
        .alert-success { border-left-color: #28a745; color: #155724; }
        .alert-info { border-left-color: #17a2b8; color: #0c5460; }
        .alert-error { border-left-color: #dc3545; color: #721c24; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <div class="dashboard-body">
                
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert-success">
                        <i class="fa-solid fa-check-circle"></i> Announcement added successfully!
                    </div>
                <?php endif; ?>
                <?php if(isset($_GET['updated'])): ?>
                    <div class="alert-info">
                        <i class="fa-solid fa-check-circle"></i> Status updated!
                    </div>
                <?php endif; ?>
                <?php if(isset($_GET['deleted'])): ?>
                    <div class="alert-success">
                        <i class="fa-solid fa-check-circle"></i> Announcement deleted!
                    </div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i> Error occurred or invalid data.
                    </div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div class="upload-form">
                    <h3><i class="fa-solid fa-bullhorn"></i> Add New Announcement</h3>
                    <form action="announcements.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group-full" style="margin-bottom: 20px;">
                            <label class="form-label">Announcement Title <span style="color:#ee0979;">*</span></label>
                            <input type="text" name="title" required class="premium-input" placeholder="E.g. Summer Vacation Dates">
                        </div>
                        
                        <div class="form-group-full" style="margin-bottom: 20px;">
                            <label class="form-label">Short Description <span style="color:#ee0979;">*</span></label>
                            <textarea name="description" required class="premium-input" placeholder="Provide a short description..." rows="3"></textarea>
                        </div>
                        
                        <div class="form-row align-center" style="gap: 20px; align-items: flex-end;">
                            <div class="form-group" style="flex: 2;">
                                <label class="form-label">Optional Attachment (PDF, Image, DOC)</label>
                                <div class="file-input-wrapper">
                                    <input type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
                                </div>
                            </div>
                            
                            <div class="form-group checkbox-group" style="flex: 1; justify-content: flex-start; margin-bottom: 15px; padding-left: 10px;">
                                <input type="checkbox" name="status" id="status" checked class="checkbox-input" style="width: 18px; height: 18px; accent-color: #ee0979;">
                                <label for="status" class="m-0" style="font-weight: 600; color: #1a1a1a; margin-left: 8px;">Publish Immediately</label>
                            </div>
                            
                            <div style="flex: 1; text-align: right; padding-bottom: 5px;">
                                <button type="submit" class="publish-btn">
                                    <i class="fa-solid fa-paper-plane"></i> Publish Now
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Announcements Grid -->
                <div class="data-card">
                    <div class="data-card-header">
                        Manage Announcements
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title & Description</th>
                                    <th>Attachment</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($announcements)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:30px;">No announcements found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach($announcements as $ann): ?>
                                    <tr>
                                        <td style="max-width:300px;">
                                            <div style="font-weight:600; color:#333;"><?= htmlspecialchars($ann['title']) ?></div>
                                            <div class="desc-text"><?= htmlspecialchars($ann['description']) ?></div>
                                        </td>
                                        <td>
                                            <?php if($ann['attachment_filename']): ?>
                                                <a href="../uploads/announcements/<?= htmlspecialchars($ann['attachment_filename']) ?>" target="_blank" class="attachment-badge">
                                                    <i class="fa-solid fa-paperclip"></i> View File
                                                </a>
                                            <?php else: ?>
                                                <span style="color:#aaa; font-size:0.9rem;">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d M Y', strtotime($ann['created_at'])) ?></td>
                                        <td>
                                            <?php if($ann['status'] == 'active'): ?>
                                                <span class="status-badge status-on">Active</span>
                                            <?php else: ?>
                                                <span class="status-badge status-off">Hidden</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-flex">
                                                <a href="announcements.php?toggle=<?= $ann['id'] ?>" class="btn <?= $ann['status']=='active'?'btn-warning':'btn-success' ?>" style="padding:6px 12px; font-size:0.85rem;" title="Toggle Visibility">
                                                    <i class="fa-solid <?= $ann['status']=='active'?'fa-eye-slash':'fa-eye' ?>"></i>
                                                </a>
                                                <a href="announcements.php?delete=<?= $ann['id'] ?>" class="btn btn-danger" style="padding:6px 12px; font-size:0.85rem;" onclick="return confirm('Delete this announcement?');" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
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
