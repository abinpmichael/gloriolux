<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
        ('tax_enabled', '0'),
        ('tax_rate', '5.00')
    ");
    $stmt->execute();
    echo "Tax settings added.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
