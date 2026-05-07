<?php
require 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE cart ADD COLUMN custom_image VARCHAR(255) NULL, ADD COLUMN custom_text TEXT NULL");
    echo "Cart altered.\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE order_items ADD COLUMN custom_image VARCHAR(255) NULL, ADD COLUMN custom_text TEXT NULL");
    echo "Order items altered.\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }
