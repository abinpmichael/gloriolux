<?php
require_once 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE products ADD COLUMN is_hidden TINYINT(1) DEFAULT 0");
    echo "Added is_hidden column successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
