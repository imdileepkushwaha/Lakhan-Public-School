<?php
// Ensure database is connected before getting settings
if (!isset($pdo)) {
    // If we are inside an admin script, path to config/db.php might be different
    if (file_exists('../config/db.php')) {
        require_once '../config/db.php';
    } elseif (file_exists('config/db.php')) {
        require_once 'config/db.php';
    } else {
        // Fallback for nested directories
        require_once dirname(__DIR__) . '/config/db.php';
    }
}

// Fetch settings from database
$site_settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $site_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    // Fallback to default values if table doesn't exist or error occurs
    $site_settings = [
        'contact_email' => 'info@lakhanpublicschool.com',
        'contact_phone1' => '(+91) 80900 29557',
        'contact_phone2' => '(+91) 8090 2963 62',
        'contact_landline' => '0522-2440999',
        'contact_address' => 'Sainik Nagar, Lane 12, Raibaraily Road, Telibagh, Lucknow, Uttar Pradesh 226002'
    ];
}

// Helper function to easily retrieve a setting
function get_setting($key) {
    global $site_settings;
    return $site_settings[$key] ?? '';
}

function get_site_logo_url() {
    $logo_file = get_setting('site_logo');
    $site_dir = dirname(__DIR__) . '/images/site/';

    if ($logo_file && file_exists($site_dir . $logo_file)) {
        return 'images/site/' . $logo_file;
    }

    if (file_exists(dirname(__DIR__) . '/images/logo-small.png')) {
        return 'images/logo-small.png';
    }

    return 'images/logo-small.png';
}

function ensure_site_logo_setting($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = 'site_logo'");
    $stmt->execute();
    if ((int) $stmt->fetchColumn() === 0) {
        $insert = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('site_logo', '')");
        $insert->execute();
    }
}

function ensure_hero_slides_table($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS hero_slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_filename VARCHAR(255) NOT NULL,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function sync_hero_slides_from_disk($pdo) {
    ensure_hero_slides_table($pdo);

    $slider_dir = dirname(__DIR__) . '/images/main-slider/';
    if (!is_dir($slider_dir)) {
        return;
    }

    $existing = $pdo->query("SELECT image_filename FROM hero_slides")->fetchAll(PDO::FETCH_COLUMN);
    $existing_set = array_fill_keys($existing, true);

    $preferred_order = ['image-4.jpg', 'image-1.jpg', 'image-2.jpg', 'image-3.jpg'];
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $to_import = [];

    foreach ($preferred_order as $file) {
        if (file_exists($slider_dir . $file) && !isset($existing_set[$file])) {
            $to_import[] = $file;
        }
    }

    $others = [];
    foreach (scandir($slider_dir) as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext, true)) {
            continue;
        }
        if (!isset($existing_set[$file]) && !in_array($file, $to_import, true)) {
            $others[] = $file;
        }
    }
    sort($others);
    $to_import = array_merge($to_import, $others);

    if (empty($to_import)) {
        return;
    }

    $max_order = (int) $pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM hero_slides")->fetchColumn();
    $insert = $pdo->prepare("INSERT INTO hero_slides (image_filename, sort_order) VALUES (?, ?)");

    foreach ($to_import as $file) {
        $max_order++;
        $insert->execute([$file, $max_order]);
    }
}

function get_hero_slides() {
    global $pdo;
    $slides = [];

    try {
        ensure_hero_slides_table($pdo);

        $count = (int) $pdo->query("SELECT COUNT(*) FROM hero_slides")->fetchColumn();
        if ($count === 0) {
            sync_hero_slides_from_disk($pdo);
        }

        $stmt = $pdo->query("SELECT image_filename FROM hero_slides ORDER BY sort_order ASC, id ASC");
        $slides = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $slides = [];
    }

    return $slides;
}

function get_default_salient_features_data() {
    return [
        'badge' => 'Why Choose Us',
        'title' => 'Salient Features',
        'description' => 'Every child has to follow all the disciplinary rules of the institution to maintain the school decorum.',
        'items' => [
            ['title' => 'Expert Teachers', 'description' => 'We have a team of child education professionals, each with more than a decade of experience.', 'icon' => 'fa-solid fa-face-smile', 'accent' => ''],
            ['title' => 'Active Learning', 'description' => 'If you want your child to catch up or get ahead, give Luckhan Public Highschool a call!', 'icon' => 'fa-solid fa-calculator', 'accent' => ''],
            ['title' => 'English Medium', 'description' => 'English is the primary language of communication on campus, with special focus on speaking skills.', 'icon' => 'fa-solid fa-language', 'accent' => ''],
            ['title' => 'Fullday Programs', 'description' => 'To provide a high-quality education that prepares all students to achieve their full potential', 'icon' => 'fa-solid fa-dumbbell', 'accent' => ''],
            ['title' => 'Clear Approach', 'description' => 'All children can reach their learning potential and they can achieve everything.', 'icon' => 'fa-solid fa-seedling', 'accent' => ''],
            ['title' => 'Social Upliftment', 'description' => 'We focus on upliftment of marginalized and less privileged students.', 'icon' => 'fa-solid fa-hands-holding-child', 'accent' => ''],
            ['title' => 'Sports', 'description' => 'We encourage active participation in sports and physical activities to build fitness, teamwork, discipline, and confidence among students.', 'icon' => 'fa-solid fa-futbol', 'accent' => 'sports'],
        ],
    ];
}

function ensure_salient_features_setting($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = 'salient_features'");
    $stmt->execute();
    if ((int) $stmt->fetchColumn() === 0) {
        $json = json_encode(get_default_salient_features_data(), JSON_UNESCAPED_UNICODE);
        $insert = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('salient_features', ?)");
        $insert->execute([$json]);
    }
}

function get_salient_features_data() {
    $raw = get_setting('salient_features');
    $defaults = get_default_salient_features_data();

    if ($raw) {
        $data = json_decode($raw, true);
        if (is_array($data) && !empty($data['items'])) {
            $data['badge'] = $data['badge'] ?? $defaults['badge'];
            $data['title'] = $data['title'] ?? $defaults['title'];
            $data['description'] = $data['description'] ?? $defaults['description'];
            return $data;
        }
    }

    return $defaults;
}

function save_salient_features_data($pdo, array $data) {
    ensure_salient_features_setting($pdo);
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = 'salient_features'");
    $stmt->execute();
    if ((int) $stmt->fetchColumn() > 0) {
        $update = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'salient_features'");
        $update->execute([$json]);
    } else {
        $insert = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('salient_features', ?)");
        $insert->execute([$json]);
    }
    global $site_settings;
    $site_settings['salient_features'] = $json;
}

function sanitize_feature_icon($icon) {
    $icon = trim($icon);
    if (preg_match('/^fa-(solid|regular|brands)\s+fa-[a-z0-9-]+$/', $icon)) {
        return $icon;
    }
    return 'fa-solid fa-star';
}

function get_default_general_rules_data() {
    return [
        'badge' => 'Guidelines',
        'title' => 'General Rules',
        'description' => '',
        'items' => [
            ['title' => 'Punctuality', 'description' => 'Punctual and regular attendance is strictly insisted upon. Students must arrive at school before the morning assembly.', 'icon' => 'fa-solid fa-clock'],
            ['title' => 'Uniform Code', 'description' => 'Students must wear the prescribed clean and neat school uniform daily. Strict action will be taken for non-compliance.', 'icon' => 'fa-solid fa-shirt'],
            ['title' => 'Discipline', 'description' => 'Every student must possess willingness to comply with school rules and maintain decorum within the campus.', 'icon' => 'fa-solid fa-scale-balanced'],
            ['title' => 'Assignments', 'description' => 'Earnestness in home assignments and projects is required. Parents must monitor their child\'s daily progress.', 'icon' => 'fa-solid fa-book-open'],
        ],
    ];
}

function ensure_general_rules_setting($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = 'general_rules'");
    $stmt->execute();
    if ((int) $stmt->fetchColumn() === 0) {
        $json = json_encode(get_default_general_rules_data(), JSON_UNESCAPED_UNICODE);
        $insert = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('general_rules', ?)");
        $insert->execute([$json]);
    }
}

function get_general_rules_data() {
    $raw = get_setting('general_rules');
    $defaults = get_default_general_rules_data();

    if ($raw) {
        $data = json_decode($raw, true);
        if (is_array($data) && !empty($data['items'])) {
            $data['badge'] = $data['badge'] ?? $defaults['badge'];
            $data['title'] = $data['title'] ?? $defaults['title'];
            $data['description'] = $data['description'] ?? $defaults['description'];
            return $data;
        }
    }

    return $defaults;
}

function save_general_rules_data($pdo, array $data) {
    ensure_general_rules_setting($pdo);
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = 'general_rules'");
    $stmt->execute();
    if ((int) $stmt->fetchColumn() > 0) {
        $update = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'general_rules'");
        $update->execute([$json]);
    } else {
        $insert = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('general_rules', ?)");
        $insert->execute([$json]);
    }
    global $site_settings;
    $site_settings['general_rules'] = $json;
}
?>
