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
?>
