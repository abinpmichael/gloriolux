<?php
require_once 'includes/db.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS blogs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        excerpt TEXT,
        content TEXT NOT NULL,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Seed an initial blog post if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM blogs");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO blogs (title, slug, excerpt, content, image_url) VALUES 
        ('The Art of Soy Wax Candles', 'art-of-soy-wax-candles', 'Discover why luxury soy wax candles are the perfect addition to your home sanctuary.', '<p>When it comes to creating a truly relaxing atmosphere, few things compare to the warm, flickering light and inviting fragrance of a luxury candle.</p><p>Unlike traditional paraffin wax, which is derived from petroleum, our <strong>soy wax</strong> is made from natural soybeans. This not only makes it a renewable resource but also ensures a cleaner, longer-lasting burn without the black soot commonly associated with cheaper candles.</p><h3>Why Choose Soy?</h3><ul><li>Eco-friendly and sustainable</li><li>Burns up to 50% longer than paraffin</li><li>Superior fragrance throw</li></ul><p>Explore our latest collection and elevate your space today.</p>', 'assets/img/hero.png')");
    }

    // Add blog.php to SEO meta
    $pdo->exec("INSERT IGNORE INTO seo_meta (page_path, meta_title, meta_description, meta_keywords) VALUES 
        ('blog.php', 'Gloriolux Blog | Fragrance & Lifestyle', 'Read our latest articles on luxury candles, home decor, and lifestyle.', 'blog, candle care, lifestyle')
    ");
    
    echo "Blogs table created and seeded.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
