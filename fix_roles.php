<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("UPDATE users SET role = 'user' WHERE role IS NULL OR role = ''");
    echo "Updated missing roles.";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
