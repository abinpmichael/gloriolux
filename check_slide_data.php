<?php
require_once 'includes/db.php';
// Enable error display AFTER requiring db.php (which hides them)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain');

try {
    echo "--- DATABASE CONNECTION INFO ---\n";
    echo "Host: " . ($db_host ?? 'not set') . "\n";
    echo "DB Name: " . ($dbname ?? 'not set') . "\n";
    echo "User: " . ($user ?? 'not set') . "\n";
    
    echo "\n--- TABLE STRUCTURE ---\n";
    $columns = $pdo->query("SHOW COLUMNS FROM slides")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Field: {$col['Field']} | Type: {$col['Type']} | Null: {$col['Null']}\n";
    }
    
    echo "\n--- SLIDES DATA ---\n";
    $stmt = $pdo->query("SELECT * FROM slides");
    $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($slides)) {
        echo "No slides found in database.\n";
    } else {
        print_r($slides);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
