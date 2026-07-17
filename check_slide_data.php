<?php
require_once 'includes/db.php';
header('Content-Type: text/plain');

try {
    // 1. Show columns in slides table
    echo "--- TABLE STRUCTURE ---\n";
    $columns = $pdo->query("SHOW COLUMNS FROM slides")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Field: {$col['Field']} | Type: {$col['Type']} | Null: {$col['Null']}\n";
    }
    
    echo "\n--- SLIDES DATA ---\n";
    // 2. Show actual rows in slides table
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
