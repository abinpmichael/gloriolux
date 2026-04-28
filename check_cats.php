<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("SELECT id, name FROM categories");
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($cats);
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
