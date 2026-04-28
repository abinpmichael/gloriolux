<?php
require_once 'includes/db.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(100) NOT NULL UNIQUE,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Insert default shipping page
    $stmt = $pdo->prepare("INSERT IGNORE INTO pages (slug, title, content) VALUES ('shipping', 'Shipping & Returns', '<h3>Shipping Policy</h3><p>We process all orders within 1-2 business days. Standard shipping takes 3-5 business days. Expedited options are available at checkout.</p><h3>Return Policy</h3><p>We accept returns within 30 days of the original purchase date. Items must be unused and in original packaging. Contact support to initiate a return.</p>')");
    $stmt->execute();
    
    echo "Pages table created and seeded.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
