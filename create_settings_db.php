<?php
require_once 'config/db.php';

try {
    // Create the settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT NOT NULL
    )");

    // Insert default values
    $defaults = [
        'contact_email' => 'info@lakhanpublicschool.com',
        'contact_phone1' => '(+91) 80900 29557',
        'contact_phone2' => '(+91) 8090 2963 62',
        'contact_landline' => '0522-2440999',
        'contact_address' => 'Sainik Nagar, Lane 12, Raibaraily Road, Telibagh, Lucknow, Uttar Pradesh 226002'
    ];

    foreach ($defaults as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = setting_value");
        $stmt->execute([$key, $value]);
    }
    
    echo "Settings table created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
