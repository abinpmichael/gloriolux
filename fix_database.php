<?php
// fix_database.php
// Run this on your live server to create missing tables and columns

require_once 'includes/db.php';

try {
    // 1. Create seo_meta
    $pdo->exec("DROP TABLE IF EXISTS seo_meta");
    $pdo->exec("CREATE TABLE seo_meta (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_path VARCHAR(100) NOT NULL UNIQUE,
        meta_title VARCHAR(255),
        meta_description TEXT,
        meta_keywords TEXT
    )");

    // 2. Create slides
    $pdo->exec("DROP TABLE IF EXISTS slides");
    $pdo->exec("CREATE TABLE slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_url VARCHAR(255) NOT NULL,
        title VARCHAR(255),
        subtitle TEXT,
        button_text VARCHAR(100),
        button_url VARCHAR(255),
        display_order INT DEFAULT 0
    )");

    // 3. Create pages (for Pages CMS)
    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(100) UNIQUE NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // 4. Create faq (for FAQ CMS)
    $pdo->exec("CREATE TABLE IF NOT EXISTS faq (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question VARCHAR(255) NOT NULL,
        answer TEXT NOT NULL,
        display_order INT DEFAULT 0
    )");

    // 5. Create subscribers
    $pdo->exec("CREATE TABLE IF NOT EXISTS subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 6. Create contacts
    $pdo->exec("CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 7. Create blogs
    $pdo->exec("CREATE TABLE IF NOT EXISTS blogs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        content TEXT NOT NULL,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 8. Add is_hidden to products
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN is_hidden TINYINT(1) DEFAULT 0");
    } catch(PDOException $e) { /* Column likely exists */ }

    // 9. Add order_status to orders
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN order_status ENUM('Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Processing'");
    } catch(PDOException $e) { /* Column likely exists */ }

    // 10. Seed SEO Meta and Slides
    $pdo->exec("INSERT IGNORE INTO seo_meta (page_path, meta_title, meta_description, meta_keywords) VALUES 
        ('index.php', 'Gloriolux Home | Luxury Soy Candles', 'Premium hand-poured soy candles and gifting sets.', 'candles, luxury, soy wax, gift sets'),
        ('shop.php', 'Shop Collection | Gloriolux', 'Browse our signature collection of luxury candles and accessories.', 'shop, buy candles, home fragrance'),
        ('about.php', 'Our Story | Gloriolux', 'Learn about the craftsmanship behind our artisanal candles.', 'about gloriolux, our story'),
        ('contact.php', 'Contact Us | Gloriolux', 'Get in touch with Gloriolux customer support.', 'contact, support, help'),
        ('faq.php', 'FAQ | Gloriolux', 'Frequently asked questions about our products and shipping.', 'faq, questions, answers')
    ");

    $pdo->exec("INSERT INTO slides (image_url, title, subtitle, button_text, button_url, display_order) VALUES 
        ('assets/img/hero.png', 'Illuminate Your Senses.', 'Hand-poured luxury soy candles crafted with exquisite fragrances to transform your space into a sanctuary of elegance.', 'Shop Collection', '/shop.php', 1),
        ('assets/img/gifting.png', 'The Art of Gifting', 'Discover our curated selection of luxury gifting sets for any occasion.', 'Explore Gifts', '/shop.php?category=2', 2)
    ");

    echo "<h3>Success! The database has been patched with all missing tables and columns.</h3>";
    echo "<p>Your site should now load correctly.</p>";
    echo "<p style='color:red;'>Please delete this script (fix_database.php) from your server for security.</p>";

} catch (PDOException $e) {
    echo "<h3>Error occurred during database patch:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
