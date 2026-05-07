<?php
require 'includes/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS toppers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        price_addon DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Toppers table created.\n";
    
    // Add columns to cart
    $pdo->exec("ALTER TABLE cart ADD COLUMN topper_id INT NULL, ADD COLUMN custom_price_addon DECIMAL(10,2) NOT NULL DEFAULT 0.00");
    echo "Cart table updated.\n";
    
    // Add columns to order_items
    $pdo->exec("ALTER TABLE order_items ADD COLUMN topper_id INT NULL, ADD COLUMN custom_price_addon DECIMAL(10,2) NOT NULL DEFAULT 0.00");
    echo "Order items table updated.\n";
    
    // Insert initial toppers
    $pdo->exec("INSERT INTO toppers (name, image_url, price_addon) VALUES 
        ('Flower Shape', 'assets/img/toppers/flower.png', 5.00),
        ('Mickey Mouse Shape', 'assets/img/toppers/mickey.png', 6.00),
        ('Heart Shape', 'assets/img/toppers/heart.png', 4.00)
    ");
    echo "Initial toppers inserted.\n";
    
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}
