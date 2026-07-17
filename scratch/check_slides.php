<?php
require 'includes/db.php';
$stmt = $pdo->query("SELECT * FROM slides");
while($row = $stmt->fetch()) {
    echo "ID: " . $row['id'] . "\n";
    echo "Title: " . $row['title'] . "\n";
    echo "Subtitle: " . $row['subtitle'] . "\n";
    echo "---\n";
}
