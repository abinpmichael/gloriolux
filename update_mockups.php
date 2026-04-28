<?php
require_once 'includes/db.php';
try {
    // Gift sets
    $pdo->exec("UPDATE products SET image_url = 'assets/img/gift_box.png' WHERE name LIKE '%Set%' OR name LIKE '%Gift%'");
    
    // Some products white candle
    $pdo->exec("UPDATE products SET image_url = 'assets/img/white_candle.png' WHERE id % 2 = 0 AND name NOT LIKE '%Set%' AND name NOT LIKE '%Gift%'");
    
    // Remaining products black candle
    $pdo->exec("UPDATE products SET image_url = 'assets/img/black_candle.png' WHERE id % 2 != 0 AND name NOT LIKE '%Set%' AND name NOT LIKE '%Gift%'");
    
    // Also update a slide
    $pdo->exec("UPDATE slides SET image_url = 'assets/img/black_candle.png' WHERE display_order = 1");
    $pdo->exec("UPDATE slides SET image_url = 'assets/img/gift_box.png' WHERE display_order = 2");
    
    // Also update a blog post
    $pdo->exec("UPDATE blogs SET image_url = 'assets/img/white_candle.png'");
    
    echo "Product, slide, and blog images successfully updated with real mockups.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
