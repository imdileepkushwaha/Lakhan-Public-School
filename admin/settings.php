<?php
require_once 'includes/session_guard.php';
admin_require_login();
require_once '../config/db.php';
require_once '../shared/get_settings.php';

ensure_hero_slides_table($pdo);
ensure_site_logo_setting($pdo);
ensure_salient_features_setting($pdo);
ensure_general_rules_setting($pdo);
sync_hero_slides_from_disk($pdo);

$message = '';
$msg_type = '';
$active_tab = $_GET['tab'] ?? 'contact';
$allowed_tabs = ['contact', 'social', 'hero', 'logo', 'features', 'rules'];
if (!in_array($active_tab, $allowed_tabs, true)) {
    $active_tab = 'contact';
}

$upload_dir = '../images/main-slider/';
$logo_dir = '../images/site/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if (!is_dir($logo_dir)) {
    mkdir($logo_dir, 0755, true);
}

// Delete site logo
if (isset($_GET['delete_logo']) && $_GET['delete_logo'] === '1') {
    $current_logo = get_setting('site_logo');
    if ($current_logo && file_exists($logo_dir . $current_logo)) {
        unlink($logo_dir . $current_logo);
    }
    $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = '' WHERE setting_key = 'site_logo'");
    $stmt->execute();
    $site_settings['site_logo'] = '';
    header('Location: settings.php?tab=logo&logo_deleted=1');
    exit;
}

// Delete hero slide
if (isset($_GET['delete_hero']) && is_numeric($_GET['delete_hero'])) {
    $stmt = $pdo->prepare("SELECT image_filename FROM hero_slides WHERE id = ?");
    $stmt->execute([$_GET['delete_hero']]);
    $slide = $stmt->fetch();

    if ($slide) {
        $file_path = $upload_dir . $slide['image_filename'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $del = $pdo->prepare("DELETE FROM hero_slides WHERE id = ?");
        $del->execute([$_GET['delete_hero']]);
        header('Location: settings.php?tab=hero&deleted=1');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save_settings';

    if ($action === 'upload_logo') {
        $active_tab = 'logo';
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $max_size = 2 * 1024 * 1024;

        if (!isset($_FILES['site_logo_file']) || $_FILES['site_logo_file']['error'] !== UPLOAD_ERR_OK) {
            $message = 'Please select a valid logo image.';
            $msg_type = 'error';
        } else {
            $file = $_FILES['site_logo_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_extensions, true)) {
                $message = 'Invalid file type. Use JPG, PNG, WEBP or GIF.';
                $msg_type = 'error';
            } elseif ($file['size'] > $max_size) {
                $message = 'Logo must be 2 MB or smaller.';
                $msg_type = 'error';
            } else {
                $current_logo = get_setting('site_logo');
                if ($current_logo && file_exists($logo_dir . $current_logo)) {
                    unlink($logo_dir . $current_logo);
                }

                $filename = 'site_logo_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $logo_dir . $filename)) {
                    $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'site_logo'");
                    $stmt->execute([$filename]);
                    header('Location: settings.php?tab=logo&logo_uploaded=1');
                    exit;
                }
                $message = 'Failed to upload logo.';
                $msg_type = 'error';
            }
        }
    } elseif ($action === 'upload_hero') {
        $active_tab = 'hero';
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $max_size = 2 * 1024 * 1024;
        $uploaded = 0;
        $size_error = false;

        if (!isset($_FILES['hero_images']) || empty($_FILES['hero_images']['name'][0])) {
            $message = 'Please select at least one image.';
            $msg_type = 'error';
        } else {
            $count = count($_FILES['hero_images']['name']);
            $max_order = (int) $pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM hero_slides")->fetchColumn();

            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['hero_images']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $file_size = $_FILES['hero_images']['size'][$i];
                $ext = strtolower(pathinfo($_FILES['hero_images']['name'][$i], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_extensions, true)) {
                    continue;
                }

                if ($file_size > $max_size) {
                    $size_error = true;
                    continue;
                }

                $filename = 'hero_' . uniqid() . '.' . $ext;
                $path = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['hero_images']['tmp_name'][$i], $path)) {
                    $max_order++;
                    $stmt = $pdo->prepare("INSERT INTO hero_slides (image_filename, sort_order) VALUES (?, ?)");
                    $stmt->execute([$filename, $max_order]);
                    $uploaded++;
                }
            }

            if ($uploaded > 0) {
                header('Location: settings.php?tab=hero&uploaded=' . $uploaded);
                exit;
            }

            $message = $size_error
                ? 'Some images exceeded 2 MB limit.'
                : 'Upload failed. Use JPG, PNG, WEBP or GIF (max 2 MB each).';
            $msg_type = 'error';
        }
    } else {
        $active_tab = $_POST['active_tab'] ?? 'contact';
        if (!in_array($active_tab, $allowed_tabs, true)) {
            $active_tab = 'contact';
        }

        $contact_email = filter_var($_POST['contact_email'] ?? '', FILTER_SANITIZE_EMAIL);
        $contact_phone1 = filter_var($_POST['contact_phone1'] ?? '', FILTER_SANITIZE_STRING);
        $contact_phone2 = filter_var($_POST['contact_phone2'] ?? '', FILTER_SANITIZE_STRING);
        $contact_landline = filter_var($_POST['contact_landline'] ?? '', FILTER_SANITIZE_STRING);
        $contact_address = filter_var($_POST['contact_address'] ?? '', FILTER_SANITIZE_STRING);

        $social_facebook = filter_var($_POST['social_facebook'] ?? '', FILTER_SANITIZE_URL);
        $social_instagram = filter_var($_POST['social_instagram'] ?? '', FILTER_SANITIZE_URL);
        $social_twitter = filter_var($_POST['social_twitter'] ?? '', FILTER_SANITIZE_URL);
        $social_youtube = filter_var($_POST['social_youtube'] ?? '', FILTER_SANITIZE_URL);

        try {
            $update_stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");

            if ($active_tab === 'contact') {
                $update_stmt->execute([$contact_email, 'contact_email']);
                $update_stmt->execute([$contact_phone1, 'contact_phone1']);
                $update_stmt->execute([$contact_phone2, 'contact_phone2']);
                $update_stmt->execute([$contact_landline, 'contact_landline']);
                $update_stmt->execute([$contact_address, 'contact_address']);
            } elseif ($active_tab === 'social') {
                $update_stmt->execute([$social_facebook, 'social_facebook']);
                $update_stmt->execute([$social_instagram, 'social_instagram']);
                $update_stmt->execute([$social_twitter, 'social_twitter']);
                $update_stmt->execute([$social_youtube, 'social_youtube']);
            } elseif ($active_tab === 'features') {
                $badge = trim($_POST['features_badge'] ?? '');
                $title = trim($_POST['features_title'] ?? '');
                $description = trim($_POST['features_description'] ?? '');
                $titles = $_POST['feature_title'] ?? [];
                $descriptions = $_POST['feature_description'] ?? [];
                $icons = $_POST['feature_icon'] ?? [];
                $accents = $_POST['feature_accent'] ?? [];

                $items = [];
                $count = is_array($titles) ? count($titles) : 0;
                for ($i = 0; $i < $count; $i++) {
                    $feature_title = trim($titles[$i] ?? '');
                    if ($feature_title === '') {
                        continue;
                    }
                    $items[] = [
                        'title' => $feature_title,
                        'description' => trim($descriptions[$i] ?? ''),
                        'icon' => sanitize_feature_icon($icons[$i] ?? 'fa-solid fa-star'),
                        'accent' => (($accents[$i] ?? '') === 'sports') ? 'sports' : '',
                    ];
                }

                if (empty($items)) {
                    $message = 'Add at least one feature with a title.';
                    $msg_type = 'error';
                    $active_tab = 'features';
                } else {
                    save_salient_features_data($pdo, [
                        'badge' => $badge ?: 'Why Choose Us',
                        'title' => $title ?: 'Salient Features',
                        'description' => $description ?: '',
                        'items' => $items,
                    ]);
                    header('Location: settings.php?tab=features&saved=1');
                    exit;
                }
            } elseif ($active_tab === 'rules') {
                $badge = trim($_POST['rules_badge'] ?? '');
                $title = trim($_POST['rules_title'] ?? '');
                $description = trim($_POST['rules_description'] ?? '');
                $titles = $_POST['rule_title'] ?? [];
                $descriptions = $_POST['rule_description'] ?? [];
                $icons = $_POST['rule_icon'] ?? [];

                $items = [];
                $count = is_array($titles) ? count($titles) : 0;
                for ($i = 0; $i < $count; $i++) {
                    $rule_title = trim($titles[$i] ?? '');
                    if ($rule_title === '') {
                        continue;
                    }
                    $items[] = [
                        'title' => $rule_title,
                        'description' => trim($descriptions[$i] ?? ''),
                        'icon' => sanitize_feature_icon($icons[$i] ?? 'fa-solid fa-star'),
                    ];
                }

                if (empty($items)) {
                    $message = 'Add at least one rule with a title.';
                    $msg_type = 'error';
                    $active_tab = 'rules';
                } else {
                    save_general_rules_data($pdo, [
                        'badge' => $badge ?: 'Guidelines',
                        'title' => $title ?: 'General Rules',
                        'description' => $description,
                        'items' => $items,
                    ]);
                    header('Location: settings.php?tab=rules&saved=1');
                    exit;
                }
            }

            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $site_settings[$row['setting_key']] = $row['setting_value'];
            }

            header('Location: settings.php?tab=' . urlencode($active_tab) . '&saved=1');
            exit;
        } catch (PDOException $e) {
            $message = 'Error updating settings: ' . $e->getMessage();
            $msg_type = 'error';
        }
    }
}

if (isset($_GET['saved'])) {
    $message = 'Settings updated successfully.';
    $msg_type = 'success';
    if (isset($_GET['tab']) && in_array($_GET['tab'], $allowed_tabs, true)) {
        $active_tab = $_GET['tab'];
    }
}
if (isset($_GET['deleted'])) {
    $message = 'Hero slide deleted successfully.';
    $msg_type = 'success';
    $active_tab = 'hero';
}
if (isset($_GET['uploaded'])) {
    $message = (int) $_GET['uploaded'] . ' hero image(s) uploaded. They will appear on the homepage slider.';
    $msg_type = 'success';
    $active_tab = 'hero';
}
if (isset($_GET['logo_uploaded'])) {
    $message = 'Logo uploaded successfully. It will appear in the website header and footer.';
    $msg_type = 'success';
    $active_tab = 'logo';
}
if (isset($_GET['logo_deleted'])) {
    $message = 'Custom logo removed. Default logo is now used.';
    $msg_type = 'success';
    $active_tab = 'logo';
}

$hero_slides = $pdo->query("SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC")->fetchAll();
$current_logo_url = '../' . get_site_logo_url();
$has_custom_logo = !empty(get_setting('site_logo')) && file_exists($logo_dir . get_setting('site_logo'));
$salient_features = get_salient_features_data();
$general_rules = get_general_rules_data();
$feature_icon_options = [
    'fa-solid fa-face-smile' => 'Smile / Teachers',
    'fa-solid fa-calculator' => 'Calculator / Learning',
    'fa-solid fa-language' => 'Language',
    'fa-solid fa-dumbbell' => 'Dumbbell / Programs',
    'fa-solid fa-seedling' => 'Seedling / Growth',
    'fa-solid fa-hands-holding-child' => 'Child Care',
    'fa-solid fa-futbol' => 'Sports / Football',
    'fa-solid fa-book-open' => 'Book',
    'fa-solid fa-microscope' => 'Science',
    'fa-solid fa-music' => 'Music',
    'fa-solid fa-bus' => 'Transport',
    'fa-solid fa-shield-halved' => 'Safety',
    'fa-solid fa-star' => 'Star',
    'fa-solid fa-clock' => 'Clock / Punctuality',
    'fa-solid fa-shirt' => 'Uniform',
    'fa-solid fa-scale-balanced' => 'Discipline / Justice',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Settings - Lakhan Public School Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <div class="dashboard-body">
                <h2 class="page-title"><i class="fa-solid fa-sliders"></i> Global Settings</h2>

                <?php if ($message): ?>
                    <div class="alert-<?= $msg_type ?>">
                        <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <div class="settings-tabs-layout">
                    <nav class="settings-tabs-nav" role="tablist">
                        <button type="button" class="settings-tab-btn <?= $active_tab === 'contact' ? 'active' : '' ?>" data-tab="contact" role="tab">
                            <i class="fa-solid fa-address-book"></i> Contact Information
                        </button>
                        <button type="button" class="settings-tab-btn <?= $active_tab === 'social' ? 'active' : '' ?>" data-tab="social" role="tab">
                            <i class="fa-solid fa-share-nodes"></i> Social Links
                        </button>
                        <button type="button" class="settings-tab-btn <?= $active_tab === 'hero' ? 'active' : '' ?>" data-tab="hero" role="tab">
                            <i class="fa-solid fa-images"></i> Hero Slider
                        </button>
                        <button type="button" class="settings-tab-btn <?= $active_tab === 'logo' ? 'active' : '' ?>" data-tab="logo" role="tab">
                            <i class="fa-solid fa-school"></i> Site Logo
                        </button>
                        <button type="button" class="settings-tab-btn <?= $active_tab === 'features' ? 'active' : '' ?>" data-tab="features" role="tab">
                            <i class="fa-solid fa-list-check"></i> Salient Features
                        </button>
                        <button type="button" class="settings-tab-btn <?= $active_tab === 'rules' ? 'active' : '' ?>" data-tab="rules" role="tab">
                            <i class="fa-solid fa-gavel"></i> General Rules
                        </button>
                    </nav>

                    <div class="settings-tabs-content">
                        <!-- Contact Tab -->
                        <div id="tab-contact" class="settings-tab-panel <?= $active_tab === 'contact' ? 'active' : '' ?>" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="active_tab" value="contact">
                                <h3 class="settings-panel-title"><i class="fa-solid fa-address-book"></i> Contact Information</h3>

                                <div class="form-group-full">
                                    <label>Email Address</label>
                                    <input type="email" name="contact_email" value="<?= htmlspecialchars(get_setting('contact_email')) ?>" required>
                                </div>
                                <div class="phone-grid">
                                    <div class="form-group-full">
                                        <label>Primary Mobile Number</label>
                                        <input type="text" name="contact_phone1" value="<?= htmlspecialchars(get_setting('contact_phone1')) ?>" required>
                                    </div>
                                    <div class="form-group-full">
                                        <label>Secondary Mobile Number</label>
                                        <input type="text" name="contact_phone2" value="<?= htmlspecialchars(get_setting('contact_phone2')) ?>">
                                    </div>
                                </div>
                                <div class="form-group-full">
                                    <label>Landline Number</label>
                                    <input type="text" name="contact_landline" value="<?= htmlspecialchars(get_setting('contact_landline')) ?>">
                                </div>
                                <div class="form-group-full">
                                    <label>Physical Address</label>
                                    <textarea name="contact_address" rows="3" required><?= htmlspecialchars(get_setting('contact_address')) ?></textarea>
                                </div>

                                <button type="submit" class="settings-btn settings-btn-inline">
                                    <i class="fa-solid fa-save"></i> Save Contact Info
                                </button>
                            </form>
                        </div>

                        <!-- Social Tab -->
                        <div id="tab-social" class="settings-tab-panel <?= $active_tab === 'social' ? 'active' : '' ?>" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="active_tab" value="social">
                                <h3 class="settings-panel-title"><i class="fa-solid fa-share-nodes"></i> Social Links</h3>

                                <div class="form-group-full">
                                    <label>Facebook URL</label>
                                    <div class="input-with-icon">
                                        <i class="fa-brands fa-facebook color-fb"></i>
                                        <input type="text" name="social_facebook" value="<?= htmlspecialchars(get_setting('social_facebook')) ?>" placeholder="https://facebook.com/...">
                                    </div>
                                </div>
                                <div class="form-group-full">
                                    <label>Instagram URL</label>
                                    <div class="input-with-icon">
                                        <i class="fa-brands fa-instagram color-ig"></i>
                                        <input type="text" name="social_instagram" value="<?= htmlspecialchars(get_setting('social_instagram')) ?>" placeholder="https://instagram.com/...">
                                    </div>
                                </div>
                                <div class="form-group-full">
                                    <label>Twitter / X URL</label>
                                    <div class="input-with-icon">
                                        <i class="fa-brands fa-twitter color-tw"></i>
                                        <input type="text" name="social_twitter" value="<?= htmlspecialchars(get_setting('social_twitter')) ?>" placeholder="https://twitter.com/...">
                                    </div>
                                </div>
                                <div class="form-group-full">
                                    <label>YouTube URL</label>
                                    <div class="input-with-icon">
                                        <i class="fa-brands fa-youtube color-yt"></i>
                                        <input type="text" name="social_youtube" value="<?= htmlspecialchars(get_setting('social_youtube')) ?>" placeholder="https://youtube.com/...">
                                    </div>
                                </div>

                                <button type="submit" class="settings-btn settings-btn-inline">
                                    <i class="fa-solid fa-save"></i> Save Social Links
                                </button>
                            </form>
                        </div>

                        <!-- Hero Slider Tab -->
                        <div id="tab-hero" class="settings-tab-panel <?= $active_tab === 'hero' ? 'active' : '' ?>" role="tabpanel">
                            <h3 class="settings-panel-title"><i class="fa-solid fa-panorama"></i> Hero Slider Images</h3>
                            <p class="settings-hint">Images uploaded here appear on the website homepage hero section. Recommended: wide landscape photos (max 2 MB each).</p>

                            <form method="POST" action="" enctype="multipart/form-data" class="hero-upload-form">
                                <input type="hidden" name="action" value="upload_hero">
                                <div class="form-group-full">
                                    <label>Upload Slider Images</label>
                                    <div class="upload-wrapper">
                                        <input type="file" id="hero_images" name="hero_images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required class="upload-input" onchange="updateHeroFileLabel(this)">
                                        <div class="upload-box">
                                            <i class="fa-solid fa-cloud-arrow-up color-primary"></i>
                                            <span id="hero-file-name">Browse or drop images...</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="settings-btn settings-btn-inline">
                                    <i class="fa-solid fa-upload"></i> Upload to Hero Slider
                                </button>
                            </form>

                            <div class="hero-slides-section">
                                <h4>Current Hero Slides <span class="stat-total">(<?= count($hero_slides) ?>)</span></h4>
                                <?php if (empty($hero_slides)): ?>
                                    <div class="empty-state" style="padding: 30px;">
                                        <i class="fa-solid fa-image"></i>
                                        <p>No hero slides yet. Upload images above to show them on the homepage.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="gallery-grid hero-slides-grid">
                                        <?php foreach ($hero_slides as $slide): ?>
                                        <div class="gallery-item">
                                            <img src="../images/main-slider/<?= htmlspecialchars($slide['image_filename']) ?>" alt="Hero slide">
                                            <div class="gallery-item-info">
                                                <div class="gallery-item-title"><?= htmlspecialchars($slide['image_filename']) ?></div>
                                                <div class="gallery-item-actions">
                                                    <a href="settings.php?tab=hero&delete_hero=<?= (int) $slide['id'] ?>" class="delete-btn" onclick="return confirm('Delete this hero slide from the website?');">
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

                        <!-- Site Logo Tab -->
                        <div id="tab-logo" class="settings-tab-panel <?= $active_tab === 'logo' ? 'active' : '' ?>" role="tabpanel">
                            <h3 class="settings-panel-title"><i class="fa-solid fa-image"></i> Site Logo</h3>
                            <p class="settings-hint">Upload your school logo here. It will appear in the website <strong>header</strong> and <strong>footer</strong>. Recommended: PNG with transparent background (max 2 MB).</p>

                            <div class="logo-preview-card">
                                <h4>Current Logo Preview</h4>
                                <div class="logo-preview-box">
                                    <img src="<?= htmlspecialchars($current_logo_url) ?>" alt="Current site logo" class="site-logo-preview-img">
                                </div>
                                <?php if ($has_custom_logo): ?>
                                    <p class="settings-hint" style="margin-top: 10px;">Custom logo is active on the website.</p>
                                <?php else: ?>
                                    <p class="settings-hint" style="margin-top: 10px;">Using default logo. Upload a new one below.</p>
                                <?php endif; ?>
                            </div>

                            <form method="POST" action="" enctype="multipart/form-data" class="hero-upload-form">
                                <input type="hidden" name="action" value="upload_logo">
                                <div class="form-group-full">
                                    <label>Upload New Logo</label>
                                    <div class="upload-wrapper">
                                        <input type="file" id="site_logo_file" name="site_logo_file" accept="image/jpeg,image/png,image/webp,image/gif" required class="upload-input" onchange="updateLogoFileLabel(this)">
                                        <div class="upload-box">
                                            <i class="fa-solid fa-cloud-arrow-up color-primary"></i>
                                            <span id="logo-file-name">Browse logo image...</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="settings-btn settings-btn-inline">
                                    <i class="fa-solid fa-upload"></i> Upload Logo
                                </button>
                            </form>

                            <?php if ($has_custom_logo): ?>
                            <div style="margin-top: 20px;">
                                <a href="settings.php?tab=logo&delete_logo=1" class="delete-btn" style="display: inline-flex;" onclick="return confirm('Remove custom logo and use default?');">
                                    <i class="fa-solid fa-trash"></i> Remove Custom Logo
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Salient Features Tab -->
                        <div id="tab-features" class="settings-tab-panel <?= $active_tab === 'features' ? 'active' : '' ?>" role="tabpanel">
                            <form method="POST" action="" id="features-form">
                                <input type="hidden" name="active_tab" value="features">
                                <h3 class="settings-panel-title"><i class="fa-solid fa-list-check"></i> Salient Features</h3>
                                <p class="settings-hint">Edit the homepage <strong>Salient Features</strong> section. Changes appear on the website immediately after saving.</p>

                                <div class="features-section-meta">
                                    <div class="form-group-full">
                                        <label>Section Badge Text</label>
                                        <input type="text" name="features_badge" value="<?= htmlspecialchars($salient_features['badge']) ?>" placeholder="Why Choose Us">
                                    </div>
                                    <div class="form-group-full">
                                        <label>Section Title</label>
                                        <input type="text" name="features_title" value="<?= htmlspecialchars($salient_features['title']) ?>" required>
                                    </div>
                                    <div class="form-group-full">
                                        <label>Section Description</label>
                                        <textarea name="features_description" rows="2"><?= htmlspecialchars($salient_features['description']) ?></textarea>
                                    </div>
                                </div>

                                <h4 class="features-edit-heading">Feature Cards</h4>
                                <div id="features-list-editor">
                                    <?php foreach ($salient_features['items'] as $idx => $feature): ?>
                                    <div class="feature-edit-card">
                                        <div class="feature-edit-card-header">
                                            <span><i class="<?= htmlspecialchars(sanitize_feature_icon($feature['icon'])) ?>"></i> Feature <?= $idx + 1 ?></span>
                                            <button type="button" class="btn-remove-feature" onclick="removeFeatureRow(this)" title="Remove feature">&times;</button>
                                        </div>
                                        <div class="form-group-full">
                                            <label>Title</label>
                                            <input type="text" name="feature_title[]" value="<?= htmlspecialchars($feature['title']) ?>" required>
                                        </div>
                                        <div class="form-group-full">
                                            <label>Description</label>
                                            <textarea name="feature_description[]" rows="2" required><?= htmlspecialchars($feature['description']) ?></textarea>
                                        </div>
                                        <div class="phone-grid">
                                            <div class="form-group-full">
                                                <label>Icon (Font Awesome class)</label>
                                                <select name="feature_icon[]" class="feature-icon-select">
                                                    <?php foreach ($feature_icon_options as $icon_val => $icon_label): ?>
                                                    <option value="<?= htmlspecialchars($icon_val) ?>" <?= ($feature['icon'] === $icon_val) ? 'selected' : '' ?>><?= htmlspecialchars($icon_label) ?></option>
                                                    <?php endforeach; ?>
                                                    <?php if (!isset($feature_icon_options[$feature['icon']])): ?>
                                                    <option value="<?= htmlspecialchars($feature['icon']) ?>" selected><?= htmlspecialchars($feature['icon']) ?> (custom)</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div class="form-group-full">
                                                <label>Icon Color Style</label>
                                                <select name="feature_accent[]">
                                                    <option value="" <?= ($feature['accent'] ?? '') !== 'sports' ? 'selected' : '' ?>>Blue (Default)</option>
                                                    <option value="sports" <?= ($feature['accent'] ?? '') === 'sports' ? 'selected' : '' ?>>Pink (Sports)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <button type="button" class="settings-btn settings-btn-secondary" onclick="addFeatureRow()">
                                    <i class="fa-solid fa-plus"></i> Add Feature
                                </button>

                                <button type="submit" class="settings-btn settings-btn-inline" style="margin-top: 20px;">
                                    <i class="fa-solid fa-save"></i> Save Salient Features
                                </button>
                            </form>
                        </div>

                        <!-- General Rules Tab -->
                        <div id="tab-rules" class="settings-tab-panel <?= $active_tab === 'rules' ? 'active' : '' ?>" role="tabpanel">
                            <form method="POST" action="" id="rules-form">
                                <input type="hidden" name="active_tab" value="rules">
                                <h3 class="settings-panel-title"><i class="fa-solid fa-gavel"></i> General Rules</h3>
                                <p class="settings-hint">Edit the homepage <strong>General Rules</strong> section. Changes appear on the website after saving.</p>

                                <div class="features-section-meta">
                                    <div class="form-group-full">
                                        <label>Section Badge Text</label>
                                        <input type="text" name="rules_badge" value="<?= htmlspecialchars($general_rules['badge']) ?>" placeholder="Guidelines">
                                    </div>
                                    <div class="form-group-full">
                                        <label>Section Title</label>
                                        <input type="text" name="rules_title" value="<?= htmlspecialchars($general_rules['title']) ?>" required>
                                    </div>
                                    <div class="form-group-full">
                                        <label>Section Description <span style="font-weight:400;color:#888;">(optional)</span></label>
                                        <textarea name="rules_description" rows="2"><?= htmlspecialchars($general_rules['description']) ?></textarea>
                                    </div>
                                </div>

                                <h4 class="features-edit-heading">Rule Cards</h4>
                                <div id="rules-list-editor">
                                    <?php foreach ($general_rules['items'] as $idx => $rule): ?>
                                    <div class="feature-edit-card">
                                        <div class="feature-edit-card-header">
                                            <span><i class="<?= htmlspecialchars(sanitize_feature_icon($rule['icon'])) ?>"></i> Rule <?= $idx + 1 ?></span>
                                            <button type="button" class="btn-remove-feature" onclick="removeRuleRow(this)" title="Remove rule">&times;</button>
                                        </div>
                                        <div class="form-group-full">
                                            <label>Title</label>
                                            <input type="text" name="rule_title[]" value="<?= htmlspecialchars($rule['title']) ?>" required>
                                        </div>
                                        <div class="form-group-full">
                                            <label>Description</label>
                                            <textarea name="rule_description[]" rows="2" required><?= htmlspecialchars($rule['description']) ?></textarea>
                                        </div>
                                        <div class="form-group-full">
                                            <label>Icon (Font Awesome class)</label>
                                            <select name="rule_icon[]" class="feature-icon-select">
                                                <?php foreach ($feature_icon_options as $icon_val => $icon_label): ?>
                                                <option value="<?= htmlspecialchars($icon_val) ?>" <?= ($rule['icon'] === $icon_val) ? 'selected' : '' ?>><?= htmlspecialchars($icon_label) ?></option>
                                                <?php endforeach; ?>
                                                <?php if (!isset($feature_icon_options[$rule['icon']])): ?>
                                                <option value="<?= htmlspecialchars($rule['icon']) ?>" selected><?= htmlspecialchars($rule['icon']) ?> (custom)</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <button type="button" class="settings-btn settings-btn-secondary" onclick="addRuleRow()">
                                    <i class="fa-solid fa-plus"></i> Add Rule
                                </button>

                                <button type="submit" class="settings-btn settings-btn-inline" style="margin-top: 20px;">
                                    <i class="fa-solid fa-save"></i> Save General Rules
                                </button>
                            </form>
                        </div>
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

        document.querySelectorAll('.settings-tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tab = this.getAttribute('data-tab');
                document.querySelectorAll('.settings-tab-btn').forEach(function (b) { b.classList.remove('active'); });
                document.querySelectorAll('.settings-tab-panel').forEach(function (p) { p.classList.remove('active'); });
                this.classList.add('active');
                document.getElementById('tab-' + tab).classList.add('active');
                history.replaceState(null, '', 'settings.php?tab=' + tab);
            });
        });

        function updateHeroFileLabel(input) {
            var label = document.getElementById('hero-file-name');
            if (!input.files.length) {
                label.textContent = 'Browse or drop images...';
                return;
            }
            label.textContent = input.files.length + ' image(s) selected';
        }

        function updateLogoFileLabel(input) {
            var label = document.getElementById('logo-file-name');
            if (!input.files.length) {
                label.textContent = 'Browse logo image...';
                return;
            }
            label.textContent = input.files[0].name;
        }

        var featureIconOptions = <?= json_encode($feature_icon_options) ?>;

        function buildIconSelectOptions() {
            var html = '';
            for (var val in featureIconOptions) {
                if (featureIconOptions.hasOwnProperty(val)) {
                    html += '<option value="' + val + '">' + featureIconOptions[val] + '</option>';
                }
            }
            return html;
        }

        function addFeatureRow() {
            var list = document.getElementById('features-list-editor');
            var count = list.querySelectorAll('.feature-edit-card').length + 1;
            var card = document.createElement('div');
            card.className = 'feature-edit-card';
            card.innerHTML =
                '<div class="feature-edit-card-header">' +
                    '<span><i class="fa-solid fa-star"></i> Feature ' + count + '</span>' +
                    '<button type="button" class="btn-remove-feature" onclick="removeFeatureRow(this)" title="Remove feature">&times;</button>' +
                '</div>' +
                '<div class="form-group-full"><label>Title</label><input type="text" name="feature_title[]" required></div>' +
                '<div class="form-group-full"><label>Description</label><textarea name="feature_description[]" rows="2" required></textarea></div>' +
                '<div class="phone-grid">' +
                    '<div class="form-group-full"><label>Icon (Font Awesome class)</label>' +
                    '<select name="feature_icon[]" class="feature-icon-select">' + buildIconSelectOptions() + '</select></div>' +
                    '<div class="form-group-full"><label>Icon Color Style</label>' +
                    '<select name="feature_accent[]"><option value="">Blue (Default)</option><option value="sports">Pink (Sports)</option></select></div>' +
                '</div>';
            list.appendChild(card);
        }

        function removeFeatureRow(btn) {
            var list = document.getElementById('features-list-editor');
            if (list.querySelectorAll('.feature-edit-card').length <= 1) {
                alert('At least one feature is required.');
                return;
            }
            btn.closest('.feature-edit-card').remove();
            list.querySelectorAll('.feature-edit-card').forEach(function (card, index) {
                var span = card.querySelector('.feature-edit-card-header span');
                if (span) {
                    var icon = card.querySelector('.feature-icon-select');
                    var iconClass = icon ? icon.value : 'fa-solid fa-star';
                    span.innerHTML = '<i class="' + iconClass + '"></i> Feature ' + (index + 1);
                }
            });
        }

        var ruleIconOptions = featureIconOptions;

        function addRuleRow() {
            var list = document.getElementById('rules-list-editor');
            var count = list.querySelectorAll('.feature-edit-card').length + 1;
            var card = document.createElement('div');
            card.className = 'feature-edit-card';
            card.innerHTML =
                '<div class="feature-edit-card-header">' +
                    '<span><i class="fa-solid fa-star"></i> Rule ' + count + '</span>' +
                    '<button type="button" class="btn-remove-feature" onclick="removeRuleRow(this)" title="Remove rule">&times;</button>' +
                '</div>' +
                '<div class="form-group-full"><label>Title</label><input type="text" name="rule_title[]" required></div>' +
                '<div class="form-group-full"><label>Description</label><textarea name="rule_description[]" rows="2" required></textarea></div>' +
                '<div class="form-group-full"><label>Icon (Font Awesome class)</label>' +
                '<select name="rule_icon[]" class="feature-icon-select">' + buildIconSelectOptions() + '</select></div>';
            list.appendChild(card);
        }

        function removeRuleRow(btn) {
            var list = document.getElementById('rules-list-editor');
            if (list.querySelectorAll('.feature-edit-card').length <= 1) {
                alert('At least one rule is required.');
                return;
            }
            btn.closest('.feature-edit-card').remove();
            list.querySelectorAll('.feature-edit-card').forEach(function (card, index) {
                var span = card.querySelector('.feature-edit-card-header span');
                if (span) {
                    var icon = card.querySelector('.feature-icon-select');
                    var iconClass = icon ? icon.value : 'fa-solid fa-star';
                    span.innerHTML = '<i class="' + iconClass + '"></i> Rule ' + (index + 1);
                }
            });
        }
    </script>
</body>
</html>
