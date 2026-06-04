<?php
/**
 * One-time server database & folders setup.
 * Upload site files, set config/db.php, then open:
 *   https://yoursite.com/server_setup.php?key=CHANGE_THIS_SECRET
 * DELETE this file after setup completes.
 */
$SETUP_KEY = 'lps_setup_2026'; // Change before uploading, then delete file after use

if (($_GET['key'] ?? '') !== $SETUP_KEY) {
    http_response_code(403);
    die('Forbidden. Use: server_setup.php?key=YOUR_SECRET');
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/shared/get_settings.php';

$logs = [];
$has_error = false;

function log_step(array &$logs, string $message, bool $ok = true): void {
    $logs[] = ['ok' => $ok, 'message' => $message];
}

function table_exists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$table]);
    return (bool) $stmt->fetchColumn();
}

function column_exists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return (bool) $stmt->fetchColumn();
}

function ensure_setting(PDO $pdo, string $key, string $default = ''): void {
    global $site_settings;
    $check = $pdo->prepare('SELECT COUNT(*) FROM site_settings WHERE setting_key = ?');
    $check->execute([$key]);
    if ((int) $check->fetchColumn() === 0) {
        $ins = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
        $ins->execute([$key, $default]);
        $site_settings[$key] = $default;
    }
}

try {
    log_step($logs, 'Database connected: ' . ($GLOBALS['dbname'] ?? 'OK'));

    $tables_sql = [
        'admin_users' => "CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'enquiries' => "CREATE TABLE IF NOT EXISTS enquiries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            message TEXT NOT NULL,
            status ENUM('unread', 'read') DEFAULT 'unread',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'gallery' => "CREATE TABLE IF NOT EXISTS gallery (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(150) NOT NULL,
            image_filename VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'hero_slides' => "CREATE TABLE IF NOT EXISTS hero_slides (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image_filename VARCHAR(255) NOT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'site_settings' => "CREATE TABLE IF NOT EXISTS site_settings (
            setting_key VARCHAR(50) NOT NULL PRIMARY KEY,
            setting_value TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'announcements' => "CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            attachment_filename VARCHAR(255) DEFAULT NULL,
            status ENUM('active','inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'student_results' => "CREATE TABLE IF NOT EXISTS student_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_name VARCHAR(255) NOT NULL,
            class_name VARCHAR(100) NOT NULL,
            percentage VARCHAR(10) NOT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            is_visible TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($tables_sql as $name => $sql) {
        $pdo->exec($sql);
        log_step($logs, "Table ready: $name");
    }

    if (table_exists($pdo, 'enquiries') && !column_exists($pdo, 'enquiries', 'phone')) {
        $pdo->exec('ALTER TABLE enquiries ADD phone VARCHAR(20) DEFAULT NULL AFTER email');
        log_step($logs, 'Column added: enquiries.phone');
    }

    $default_settings = [
        'contact_email' => 'info@lakhanpublicschool.com',
        'contact_phone1' => '(+91) 80900 29557',
        'contact_phone2' => '(+91) 8090 2963 62',
        'contact_landline' => '0522-2440999',
        'contact_address' => 'Sainik Nagar, Lane 12, Raibaraily Road, Telibagh, Lucknow, Uttar Pradesh 226002',
        'social_facebook' => '#',
        'social_instagram' => '#',
        'social_twitter' => '#',
        'social_youtube' => '#',
        'popup_active' => '0',
        'popup_image' => '',
        'site_logo' => '',
        'salient_features' => '',
        'general_rules' => '',
    ];

    foreach ($default_settings as $key => $value) {
        ensure_setting($pdo, $key, $value);
    }
    log_step($logs, 'Site settings keys checked');

    ensure_salient_features_setting($pdo);
    ensure_general_rules_setting($pdo);
    ensure_site_logo_setting($pdo);
    log_step($logs, 'Salient Features, General Rules, Logo settings initialized');

    sync_hero_slides_from_disk($pdo);
    log_step($logs, 'Hero slides synced from images/main-slider/ (if any)');

    $admin_count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    if ($admin_count === 0 && isset($_GET['create_admin'])) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $ins = $pdo->prepare('INSERT INTO admin_users (username, password) VALUES (?, ?)');
        $ins->execute(['admin', $hash]);
        log_step($logs, 'Admin user created: admin / admin123 — change password after login!');
    } elseif ($admin_count === 0) {
        log_step($logs, 'No admin user found. Add ?create_admin=1 to URL to create admin/admin123', false);
    } else {
        log_step($logs, "Admin user(s) exist: $admin_count (password not changed)");
    }

    $dirs = [
        'images/gallery',
        'images/main-slider',
        'images/site',
        'images/popup',
        'images/results',
        'uploads/announcements',
    ];
    foreach ($dirs as $dir) {
        $path = __DIR__ . '/' . $dir;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
            log_step($logs, "Folder created: $dir");
        } else {
            log_step($logs, "Folder exists: $dir");
        }
    }

    log_step($logs, 'Setup finished. Delete server_setup.php from server now!');

} catch (Throwable $e) {
    $has_error = true;
    log_step($logs, 'Error: ' . $e->getMessage(), false);
}

$host_label = $_SERVER['HTTP_HOST'] ?? 'server';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LPS Server Setup</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 30px 15px; }
        .box { max-width: 720px; margin: 0 auto; background: #fff; color: #1e293b; border-radius: 16px; padding: 30px; box-shadow: 0 20px 50px rgba(0,0,0,.3); }
        h1 { margin: 0 0 8px; color: #00122e; font-size: 1.5rem; }
        .sub { color: #64748b; margin-bottom: 24px; font-size: 0.95rem; }
        .log { list-style: none; padding: 0; margin: 0 0 24px; }
        .log li { padding: 10px 14px; border-radius: 8px; margin-bottom: 8px; font-size: 0.9rem; display: flex; gap: 10px; align-items: flex-start; }
        .log li.ok { background: #ecfdf5; color: #065f46; }
        .log li.fail { background: #fef2f2; color: #991b1b; }
        .options { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .options h2 { margin: 0 0 12px; font-size: 1.1rem; color: #00122e; }
        .options ol { margin: 0; padding-left: 20px; line-height: 1.7; }
        .options code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem; }
        .btn { display: inline-block; margin-top: 10px; padding: 10px 18px; background: #38bbf3; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; }
        .warn { background: #fff7ed; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 8px; color: #92400e; font-size: 0.9rem; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Lakhan Public School — Server Setup</h1>
        <p class="sub">Host: <?= htmlspecialchars($host_label) ?> · <?= $has_error ? 'Completed with errors' : 'Database check complete' ?></p>

        <div class="options">
            <h2>Deploy steps (recommended)</h2>
            <ol>
                <li>cPanel mein database + user banao, <code>config/db.php</code> mein credentials daalo</li>
                <li>Saari website files server par upload karo (FTP / File Manager)</li>
                <li>Yeh page chalao (tables auto ban jayengi) — ya phpMyAdmin se <code>db/server_migrate_only.sql</code> import karo</li>
                <li>Admin login: <code>admin/login.php</code> — password change karo</li>
                <li><strong>Is file ko delete kar do:</strong> <code>server_setup.php</code></li>
            </ol>
            <?php if (!$has_error): ?>
            <a class="btn" href="index.php">Open Website</a>
            <a class="btn" href="admin/login.php" style="background:#00122e;margin-left:8px;">Admin Login</a>
            <?php endif; ?>
        </div>

        <ul class="log">
            <?php foreach ($logs as $entry): ?>
            <li class="<?= $entry['ok'] ? 'ok' : 'fail' ?>">
                <span><?= $entry['ok'] ? '✓' : '✗' ?></span>
                <span><?= htmlspecialchars($entry['message']) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="warn">
            <strong>Security:</strong> Setup ke baad <code>server_setup.php</code> aur <code>admin/reset_admin.php</code> server se hata dena.
            Admin banane ke liye URL mein <code>&amp;create_admin=1</code> lagao (sirf agar koi admin user nahi hai).
        </div>
    </div>
</body>
</html>
