<?php
require_once 'includes/db.php';
try {
    // 1. Create slides table
    $pdo->exec("CREATE TABLE IF NOT EXISTS slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_url VARCHAR(255) NOT NULL,
        title VARCHAR(255),
        subtitle TEXT,
        button_text VARCHAR(100),
        button_url VARCHAR(255),
        button2_text VARCHAR(100) NULL,
        button2_url VARCHAR(255) NULL,
        display_order INT DEFAULT 0
    )");
    
    // Seed slides if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM slides");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO slides (image_url, title, subtitle, button_text, button_url, button2_text, button2_url, display_order) VALUES 
        ('assets/img/hero.png', 'Illuminate Your Senses.', 'Hand-poured luxury soy candles crafted with exquisite fragrances to transform your space into a sanctuary of elegance.', 'Shop Collection', '/shop.php', 'Our Story', '/about.php', 1),
        ('assets/img/gifting.png', 'The Art of Gifting', 'Discover our curated selection of luxury gifting sets for any occasion.', 'Explore Gifts', '/shop.php?category=2', NULL, NULL, 2)");
    }

    // 2. Create SEO metadata table
    $pdo->exec("CREATE TABLE IF NOT EXISTS seo_meta (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_path VARCHAR(100) NOT NULL UNIQUE,
        meta_title VARCHAR(255),
        meta_description TEXT,
        meta_keywords TEXT
    )");
    
    // Seed common pages
    $pdo->exec("INSERT IGNORE INTO seo_meta (page_path, meta_title, meta_description, meta_keywords) VALUES 
        ('index.php', 'Gloriolux Home | Luxury Soy Candles', 'Premium hand-poured soy candles and gifting sets.', 'candles, luxury, soy wax, gift sets'),
        ('shop.php', 'Shop Collection | Gloriolux', 'Browse our signature collection of luxury candles and accessories.', 'shop, buy candles, home fragrance'),
        ('about.php', 'Our Story | Gloriolux', 'Learn about the craftsmanship behind our artisanal candles.', 'about gloriolux, our story'),
        ('contact.php', 'Contact Us | Gloriolux', 'Get in touch with Gloriolux customer support.', 'contact, support, help'),
        ('faq.php', 'FAQ | Gloriolux', 'Frequently asked questions about our products and shipping.', 'faq, questions, answers')
    ");
    
    echo "Slides and SEO tables created successfully.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
