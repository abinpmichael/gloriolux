<?php
require 'includes/db.php';

$content = '
<p>Gloriolux was born from a passion for creating luxurious, hand‑poured soy candles that transform everyday moments into unforgettable experiences. Our artisans blend premium waxes, elegant fragrances, and timeless designs to bring a touch of serenity and style to your home.</p>
<p>Each candle is crafted with love, using sustainable ingredients and eco‑friendly packaging. From the soft glow of our signature candle collections to bespoke gifting sets, we aim to elevate your rituals and celebrate the art of living beautifully.</p>
';

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO pages (slug, title, content) VALUES (?, ?, ?)");
    $stmt->execute(['about', 'Our Story', trim($content)]);
    echo "About page added to CMS.";
} catch (Exception $e) {
    echo $e->getMessage();
}
