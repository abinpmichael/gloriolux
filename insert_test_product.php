<?php
require_once 'includes/db.php';

try {
    $pdo->exec("INSERT INTO products (category_id, name, description, price, stock, image_url) VALUES 
        (1, 'Miniature Scent Sample (Test Item)', 'A tiny $1.00 sample tin of our signature soy wax for testing checkout capabilities.', 1.00, 999, 'assets/img/black_candle.png')
    ");
    
    echo "One dollar test item added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
