<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
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
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="success-msg">
                        <i class="fa-solid fa-check"></i> Image deleted successfully.
                    </div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div class="upload-form">
                    <h3 class="gallery-title">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload New Image
                    </h3>
                    <form action="upload_image.php" method="POST" enctype="multipart/form-data">
                        <div class="form-row align-center">
                            <div class="form-group">
                                <label for="title">Image Category / Event Name</label>
                                <input type="text" id="title" name="title" required placeholder="e.g., Annual Sports Meet">
                            </div>
                            <div class="form-group">
                                <label for="image">Select Images <span style="font-size:0.85em; color:#888;">(Max 500KB per image)</span></label>
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

    <script>
        function checkFileSize(input) {
            let msg = input.files.length + ' file(s) selected';
            for(let i = 0; i < input.files.length; i++) {
                if(input.files[i].size > 500 * 1024) {
                    alert('File "' + input.files[i].name + '" exceeds the 500KB limit.');
                    input.value = '';
                    msg = 'Browse or drop images...';
                    break;
                }
            }
            document.getElementById('file-name').textContent = msg;
        }

        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
    </script>
</body>
</html>




