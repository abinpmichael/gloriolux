<?php
require_once 'includes/db.php';
try {
    // Insert new slides
    $pdo->exec("INSERT INTO slides (image_url, title, subtitle, button_text, button_url, display_order) VALUES 
        ('assets/img/slide_living_room.png', 'The Sanctuary', 'Transform your living space into a warm, inviting retreat with our signature soy candles.', 'Shop Candles', '/Gloriolux/shop.php', 3),
        ('assets/img/slide_pouring.png', 'Artisanal Craftsmanship', 'Every candle is meticulously hand-poured using the finest sustainable soy wax and premium fragrance oils.', 'Discover Our Story', '/Gloriolux/about.php', 4)");
        
    echo "New slides added to the database.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
