<?php
require_once 'includes/session_guard.php';
admin_require_login();
require_once '../config/db.php';

define('GALLERY_MIN_SIZE', 500 * 1024);
define('GALLERY_MAX_SIZE', 2 * 1024 * 1024);
define('GALLERY_UPLOAD_DIR', '../images/gallery/');

// Handle gallery edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_gallery') {
    $id = (int) ($_POST['gallery_id'] ?? 0);
    $title = trim($_POST['edit_title'] ?? '');

    if ($id <= 0 || $title === '') {
        header('Location: gallery.php?edit_error=1');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        header('Location: gallery.php?edit_error=1');
        exit;
    }

    $filename = $row['image_filename'];

    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['edit_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed, true)) {
            header('Location: gallery.php?edit_error=type');
            exit;
        }

        if ($file['size'] < GALLERY_MIN_SIZE || $file['size'] > GALLERY_MAX_SIZE) {
            header('Location: gallery.php?edit_error=size');
            exit;
        }

        $new_filename = 'img_' . uniqid() . '_' . rand(100, 999) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], GALLERY_UPLOAD_DIR . $new_filename)) {
            $old_path = GALLERY_UPLOAD_DIR . $filename;
            if (file_exists($old_path)) {
                unlink($old_path);
            }
            $filename = $new_filename;
        } else {
            header('Location: gallery.php?edit_error=1');
            exit;
        }
    }

    $update = $pdo->prepare("UPDATE gallery SET title = ?, image_filename = ? WHERE id = ?");
    $update->execute([$title, $filename, $id]);
    header('Location: gallery.php?updated=1');
    exit;
}

// Handle image deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT image_filename FROM gallery WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $image = $stmt->fetch();
    
    if ($image) {
        $file_path = "../images/gallery/" . $image['image_filename'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: gallery.php?deleted=1");
        exit;
    }
}

// Fetch all gallery images
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
$gallery_images = $stmt->fetchAll();

// Get image count by title
$stmt = $pdo->query("SELECT title, COUNT(*) as count FROM gallery GROUP BY title ORDER BY title");
$titles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - Lakhan Public School</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <?php include 'includes/header.php'; ?>

            <!-- Dashboard Body -->
            <div class="dashboard-body">
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-msg">
                        <i class="fa-solid fa-check"></i> Image(s) uploaded successfully.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'size'): ?>
                    <div class="error-msg" style="margin-bottom: 20px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Each image must be between 500 KB and 2 MB.
                    </div>
                <?php elseif (isset($_GET['error'])): ?>
                    <div class="error-msg" style="margin-bottom: 20px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Upload failed. Please check file type and size (500 KB – 2 MB).
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['deleted'])): ?>
                    <div class="success-msg">
                        <i class="fa-solid fa-check"></i> Image deleted successfully.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="success-msg">
                        <i class="fa-solid fa-check"></i> Gallery image updated successfully.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['edit_error']) && $_GET['edit_error'] === 'size'): ?>
                    <div class="error-msg" style="margin-bottom: 20px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Replacement image must be between 500 KB and 2 MB.
                    </div>
                <?php elseif (isset($_GET['edit_error']) && $_GET['edit_error'] === 'type'): ?>
                    <div class="error-msg" style="margin-bottom: 20px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Invalid image type. Use JPG, PNG, WEBP or GIF.
                    </div>
                <?php elseif (isset($_GET['edit_error'])): ?>
                    <div class="error-msg" style="margin-bottom: 20px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Failed to update image. Please try again.
                    </div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div class="upload-form">
                    <h3 class="gallery-title">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload New Image
                    </h3>
                    <form action="upload_image.php" method="POST" enctype="multipart/form-data" onsubmit="return validateGalleryUpload(this);">
                        <div class="form-row align-center">
                            <div class="form-group">
                                <label for="title">Image Category / Event Name</label>
                                <input type="text" id="title" name="title" required placeholder="e.g., Annual Sports Meet">
                            </div>
                            <div class="form-group">
                                <label for="image">Select Images <span style="font-size:0.85em; color:#888;">(500 KB – 2 MB per image)</span></label>
                                <div class="upload-wrapper">
                                    <input type="file" id="image" name="image[]" accept="image/*" multiple required class="upload-input"
                                           onchange="checkFileSize(this)">
                                    <div class="upload-box">
                                        <i class="fa-solid fa-images color-primary"></i>
                                        <span id="file-name">Browse or drop images...</span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="upload-btn">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Upload All
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Title Statistics -->
                <?php if (!empty($titles)): ?>
                <div class="mb-40">
                    <h3 class="gallery-title"><i class="fa-solid fa-chart-pie color-primary"></i> Images by Category</h3>
                    <div class="title-stats">
                        <?php foreach ($titles as $title_info): ?>
                        <div class="title-card">
                            <div class="title-name"><?= htmlspecialchars($title_info['title']) ?></div>
                            <div class="title-count"><?= $title_info['count'] ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Gallery Grid -->
                <div class="data-card">
                    <div class="data-card-header">
                        All Images
                        <span class="stat-total">Total: <?= count($gallery_images) ?></span>
                    </div>
                    
                    <?php if (empty($gallery_images)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-image"></i>
                            <p>No images in gallery yet. Upload one to get started!</p>
                        </div>
                    <?php else: ?>
                        <div class="gallery-grid">
                            <?php foreach ($gallery_images as $image): ?>
                            <div class="gallery-item">
                                <img src="../images/gallery/<?= htmlspecialchars($image['image_filename']) ?>" alt="<?= htmlspecialchars($image['title']) ?>">
                                <div class="gallery-item-info">
                                    <div class="gallery-item-title"><?= htmlspecialchars($image['title']) ?></div>
                                    <div class="gallery-item-actions">
                                        <button type="button" class="edit-btn"
                                            data-id="<?= (int) $image['id'] ?>"
                                            data-title="<?= htmlspecialchars($image['title'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-image="../images/gallery/<?= htmlspecialchars($image['image_filename'], ENT_QUOTES, 'UTF-8') ?>"
                                            onclick="openEditModal(this)">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>
                                        <a href="gallery.php?delete=<?= $image['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this image?');">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <!-- Edit Gallery Modal -->
    <div id="editModal" class="modal" onclick="closeEditModalOnBackdrop(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <span class="modal-close" onclick="closeEditModal()">&times;</span>
            <h3 class="modal-title"><i class="fa-solid fa-pen"></i> Edit Gallery Image</h3>
            <form method="POST" action="" enctype="multipart/form-data" id="editGalleryForm" onsubmit="return validateEditImage(this);">
                <input type="hidden" name="action" value="edit_gallery">
                <input type="hidden" name="gallery_id" id="edit_gallery_id" value="">

                <div class="edit-gallery-preview">
                    <img id="edit_preview" src="" alt="Preview">
                </div>

                <div class="form-group-full" style="margin-top: 20px;">
                    <label for="edit_title">Category / Event Name</label>
                    <input type="text" id="edit_title" name="edit_title" required placeholder="e.g., Annual Sports Meet">
                </div>

                <div class="form-group-full">
                    <label for="edit_image">Replace Image <span style="font-weight:400;color:#888;">(optional, 500 KB – 2 MB)</span></label>
                    <input type="file" id="edit_image" name="edit_image" accept="image/jpeg,image/png,image/webp,image/gif" class="upload-input" style="position:static;opacity:1;width:100%;height:auto;padding:10px;border:1px solid #ddd;border-radius:8px;">
                </div>

                <button type="submit" class="settings-btn settings-btn-inline" style="margin-top: 10px;">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

    <script>
        const MIN_IMAGE_SIZE = 500 * 1024;
        const MAX_IMAGE_SIZE = 2 * 1024 * 1024;

        function formatFileSize(bytes) {
            if (bytes >= 1024 * 1024) {
                return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            }
            return Math.round(bytes / 1024) + ' KB';
        }

        function checkFileSize(input) {
            const defaultMsg = 'Browse or drop images...';
            if (!input.files.length) {
                document.getElementById('file-name').textContent = defaultMsg;
                return;
            }

            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                if (file.size < MIN_IMAGE_SIZE) {
                    alert('"' + file.name + '" is too small (' + formatFileSize(file.size) + '). Minimum size is 500 KB.');
                    input.value = '';
                    document.getElementById('file-name').textContent = defaultMsg;
                    return;
                }
                if (file.size > MAX_IMAGE_SIZE) {
                    alert('"' + file.name + '" is too large (' + formatFileSize(file.size) + '). Maximum size is 2 MB.');
                    input.value = '';
                    document.getElementById('file-name').textContent = defaultMsg;
                    return;
                }
            }

            document.getElementById('file-name').textContent = input.files.length + ' file(s) selected';
        }

        function validateGalleryUpload(form) {
            const input = form.querySelector('#image');
            if (!input.files.length) {
                alert('Please select at least one image.');
                return false;
            }
            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                if (file.size < MIN_IMAGE_SIZE) {
                    alert('"' + file.name + '" is too small. Minimum size is 500 KB.');
                    return false;
                }
                if (file.size > MAX_IMAGE_SIZE) {
                    alert('"' + file.name + '" is too large. Maximum size is 2 MB.');
                    return false;
                }
            }
            return true;
        }

        function openEditModal(btn) {
            document.getElementById('edit_gallery_id').value = btn.dataset.id;
            document.getElementById('edit_title').value = btn.dataset.title;
            document.getElementById('edit_preview').src = btn.dataset.image;
            document.getElementById('edit_image').value = '';
            document.getElementById('editModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function closeEditModalOnBackdrop(e) {
            if (e.target.id === 'editModal') {
                closeEditModal();
            }
        }

        function validateEditImage(form) {
            const input = form.querySelector('#edit_image');
            if (!input.files.length) {
                return true;
            }
            const file = input.files[0];
            if (file.size < MIN_IMAGE_SIZE) {
                alert('Image is too small. Minimum size is 500 KB.');
                return false;
            }
            if (file.size > MAX_IMAGE_SIZE) {
                alert('Image is too large. Maximum size is 2 MB.');
                return false;
            }
            return true;
        }

        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
    </script>
</body>
</html>




