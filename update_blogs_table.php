<?php
require_once 'includes/db.php';
try {
    // Add SEO columns to blogs table
    $pdo->exec("ALTER TABLE blogs ADD COLUMN meta_title VARCHAR(255) AFTER image_url");
    $pdo->exec("ALTER TABLE blogs ADD COLUMN meta_description TEXT AFTER meta_title");
    $pdo->exec("ALTER TABLE blogs ADD COLUMN meta_keywords TEXT AFTER meta_description");
    
    // Update existing seed post
    $pdo->exec("UPDATE blogs SET meta_title = title, meta_description = excerpt WHERE meta_title IS NULL");
    
    echo "Added SEO columns to blogs table successfully.";
} catch(PDOException $e) {
    // If columns already exist, it will throw an error, which is fine
    echo "Message: " . $e->getMessage();
}
?>
