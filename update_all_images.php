<?php
require_once 'includes/db.php';
try {
    // Update all products
    $pdo->exec("UPDATE products SET image_url = 'assets/img/logo.png'");
    
    // Update all slides
    $pdo->exec("UPDATE slides SET image_url = 'assets/img/logo.png'");
    
    // Update all blog posts
    $pdo->exec("UPDATE blogs SET image_url = 'assets/img/logo.png'");
    
    echo "All images in the database have been updated to the brand logo.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
