<?php
require_once 'includes/db.php';
try {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE email = 'admin@gloriolux.com'");
    $stmt->execute([$password]);
    echo "Admin password updated to 'admin123'";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
