<?php
require 'includes/db.php';
$pdo->exec('CREATE TABLE IF NOT EXISTS product_images (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, image_url VARCHAR(255) NOT NULL, FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE)');
echo 'Table created';
