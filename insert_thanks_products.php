<?php
require_once 'includes/db.php';

try {
    $pdo->exec("INSERT INTO products (category_id, name, description, price, stock, image_url) VALUES 
        (1, 'Autumn Harvest (Thanksgiving Special)', 'A rich, comforting blend of pumpkin spice, amber, and roasted pecans. The perfect centerpiece for your Thanksgiving table.', 55.00, 100, 'assets/img/thanksgiving_candle.png'),
        (2, 'The Gratitude Gift Set', 'Express your deepest thanks with our premium velvet-wrapped gift box. Contains a curated selection of our finest miniature soy candles.', 145.00, 30, 'assets/img/thanks_gifting.png')
    ");
    
    echo "Thanksgiving and Gifting products added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
