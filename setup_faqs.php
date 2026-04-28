<?php
require_once 'includes/db.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS faqs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question VARCHAR(255) NOT NULL,
        answer TEXT NOT NULL,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert some default FAQs if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM faqs");
    if ($stmt->fetchColumn() == 0) {
        $default_faqs = [
            ['What are your candles made of?', 'Our luxury candles are crafted from 100% natural soy wax, infused with premium essential oils and phthalate-free fragrances. We use lead-free cotton wicks for a clean, even burn.'],
            ['How long do the candles burn?', 'Our signature 8oz candles offer approximately 45-50 hours of burn time. To maximize the life of your candle, allow the wax pool to reach the edges of the jar during the first burn.'],
            ['Do you offer international shipping?', 'Currently, we ship exclusively within the United States and Canada. We are working diligently to expand our shipping capabilities to serve our global customers soon.'],
            ['What is your return policy?', 'We want you to love your Gloriolux experience. If you are not completely satisfied, we accept returns of unused candles in their original packaging within 30 days of purchase. Please refer to our Shipping & Returns page for more details.']
        ];
        
        $insert = $pdo->prepare("INSERT INTO faqs (question, answer, display_order) VALUES (?, ?, ?)");
        foreach ($default_faqs as $index => $faq) {
            $insert->execute([$faq[0], $faq[1], $index]);
        }
    }
    
    echo "FAQ table created and seeded.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
