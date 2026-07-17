<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'includes/db.php';

// Define the base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace('sitemap.php', '', $_SERVER['PHP_SELF']);

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Static Pages
$static_pages = [
    '' => '1.0',
    'shop' => '0.9',
    'blog' => '0.8',
    'about' => '0.8',
    'contact' => '0.7',
    'faq' => '0.6',
    'shipping' => '0.5'
];

foreach ($static_pages as $page => $priority) {
    echo '<url>';
    echo '<loc>' . $base_url . $page . '</loc>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>' . $priority . '</priority>';
    echo '</url>';
}

// Products
try {
    $stmt = $pdo->query("SELECT id, created_at FROM products WHERE is_hidden = 0");
    while ($row = $stmt->fetch()) {
        echo '<url>';
        echo '<loc>' . $base_url . 'product?id=' . $row['id'] . '</loc>';
        echo '<lastmod>' . date('Y-m-d', strtotime($row['created_at'])) . '</lastmod>';
        echo '<changefreq>monthly</changefreq>';
        echo '<priority>0.8</priority>';
        echo '</url>';
    }
} catch (PDOException $e) {
    // Handle error or skip
}

// Blogs
try {
    $stmt = $pdo->query("SELECT slug, updated_at FROM blogs");
    while ($row = $stmt->fetch()) {
        echo '<url>';
        echo '<loc>' . $base_url . 'blog/' . $row['slug'] . '</loc>';
        echo '<lastmod>' . date('Y-m-d', strtotime($row['updated_at'])) . '</lastmod>';
        echo '<changefreq>monthly</changefreq>';
        echo '<priority>0.7</priority>';
        echo '</url>';
    }
} catch (PDOException $e) {
    // Handle error or skip
}

echo '</urlset>';
?>
