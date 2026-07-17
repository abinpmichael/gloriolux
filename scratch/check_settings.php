<?php
require 'includes/db.php';
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while($row = $stmt->fetch()) {
    echo $row['setting_key'] . ": " . $row['setting_value'] . "\n";
}
