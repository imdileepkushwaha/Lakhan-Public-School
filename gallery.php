<?php 
require_once 'config/db.php';
include 'shared/header.php';

// Fetch all gallery images
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY title, created_at DESC");
$gallery_images = $stmt->fetchAll();

// Group images by title
$grouped_images = [];
foreach ($gallery_images as $img) {
    if (!isset($grouped_images[$img['title']])) {
        $grouped_images[$img['title']] = [];
    }
    $grouped_images[$img['title']][] = $img;
}
?>

<!-- Page Header with Pattern -->
<div class="page-header gallery-header">
    <div class="header-pattern"></div>
    <div class="container reveal">
        <h1 class="gallery-page-title">School Gallery</h1>
        <p class="gallery-page-subtitle">Capturing our most beautiful moments and memories</p>
    </div>
</div>

<!-- Gallery Section -->
<section class="gallery-page bg-white" style="padding: 80px 0;">
    <div class="container">
        <?php if (empty($gallery_images)): ?>
            <div style="text-align: center; padding: 100px 20px; color: #888; background: #f9fafb; border-radius: 20px; border: 2px dashed #e2e8f0;">
                <i class="fa-solid fa-images" style="font-size: 4rem; margin-bottom: 20px; color: #cbd5e1;"></i>
                <p style="font-size: 1.2rem; font-weight: 500;">Gallery coming soon...</p>
            </div>
        <?php else: ?>
            <?php foreach ($grouped_images as $title => $images): ?>
            <div style="margin-bottom: 30px;">
                <!-- Category Title (Badge Style) -->
                <div style="margin-bottom: 30px;">
                    <div class="category-badge">
                        <i class="fa-solid fa-camera-retro"></i> <?= htmlspecialchars($title) ?>
                        <span class="img-count"><?= count($images) ?> Photos</span>
                    </div>
                </div>
                
                <!-- Gallery Grid -->
                <div class="premium-gallery-grid">
                    <?php foreach ($images as $index => $img): ?>
                    <a href="images/gallery/<?= htmlspecialchars($img['image_filename']) ?>" data-fancybox="gallery-<?= htmlspecialchars($title) ?>" class="premium-gallery-item reveal" style="transition-delay: <?= ($index * 0.1) ?>s;">
                        <img src="images/gallery/<?= htmlspecialchars($img['image_filename']) ?>" alt="<?= htmlspecialchars($img['title']) ?>">
                        <div class="overlay">
                            <div class="zoom-icon">
                                <i class="fa-solid fa-magnifying-glass-plus"></i>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>



<?php include 'shared/footer.php'; ?>

