<?php
require 'includes/db.php';

try {
    $stmt = $pdo->query('SHOW COLUMNS FROM cart');
    while($row = $stmt->fetch()) echo $row[0] . "\n";
    echo "---\n";
    $stmt = $pdo->query('SHOW COLUMNS FROM order_items');
    while($row = $stmt->fetch()) echo $row[0] . "\n";
} catch (Exception $e) {
    echo $e->getMessage();
}
