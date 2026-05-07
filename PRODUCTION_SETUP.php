<?php
require 'includes/db.php';

echo "<h2>Gloriolux Production Setup</h2>";

try {
    // 1. Create Toppers Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS toppers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        price_addon DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Toppers table created or already exists.<br>";
    
    // 2. Create Product Toppers Mapping Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_toppers (
        product_id INT NOT NULL, 
        topper_id INT NOT NULL, 
        custom_image_url VARCHAR(255) NULL,
        PRIMARY KEY(product_id, topper_id)
    )");
    echo "✅ Product Toppers table created or already exists.<br>";

    // 3. Update Cart Table
    try {
        $pdo->exec("ALTER TABLE cart ADD COLUMN topper_id INT NULL, ADD COLUMN custom_price_addon DECIMAL(10,2) NOT NULL DEFAULT 0.00");
        echo "✅ Cart table updated with topper columns.<br>";
    } catch(Exception $e) { echo "ℹ️ Cart table already had topper columns.<br>"; }

    // 4. Update Order Items Table
    try {
        $pdo->exec("ALTER TABLE order_items ADD COLUMN topper_id INT NULL, ADD COLUMN custom_price_addon DECIMAL(10,2) NOT NULL DEFAULT 0.00");
        echo "✅ Order items table updated with topper columns.<br>";
    } catch(Exception $e) { echo "ℹ️ Order items table already had topper columns.<br>"; }

    // 5. Update Orders Table for Guest Info and Status
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN order_status ENUM('Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Processing' AFTER payment_status");
        echo "✅ Orders table updated with order_status.<br>";
    } catch(Exception $e) { echo "ℹ️ Orders table already had order_status.<br>"; }

    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN guest_email VARCHAR(255) NULL AFTER user_id, 
                    ADD COLUMN guest_name VARCHAR(255) NULL AFTER guest_email,
                    ADD COLUMN guest_phone VARCHAR(50) NULL AFTER guest_name");
        echo "✅ Orders table updated with guest info columns.<br>";
    } catch(Exception $e) { echo "ℹ️ Orders table already had guest info columns.<br>"; }

    // 6. Check if initial toppers exist, if not insert
    $count = $pdo->query("SELECT COUNT(*) FROM toppers")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO toppers (name, image_url, price_addon) VALUES 
            ('Flower Shape', 'assets/img/toppers/flower.png', 5.00),
            ('Mickey Mouse Shape', 'assets/img/toppers/mickey.png', 6.00),
            ('Heart Shape', 'assets/img/toppers/heart.png', 4.00),
            ('Star Shape', 'assets/img/toppers/star.png', 4.00),
            ('Rose Shape', 'assets/img/toppers/rose.png', 6.50),
            ('Snowflake Shape', 'assets/img/toppers/snowflake.png', 5.00)
        ");
        echo "✅ Initial toppers inserted.<br>";
    } else {
        echo "ℹ️ Toppers already present in database.<br>";
    }

    echo "<br><b>Setup Complete! Please DELETE this file (PRODUCTION_SETUP.php) from your server for security.</b>";

} catch (Exception $e) {
    echo "<br><b style='color:red;'>Error during setup:</b> " . $e->getMessage();
}
