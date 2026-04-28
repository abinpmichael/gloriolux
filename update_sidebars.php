<?php
$dir = __DIR__ . '/admin';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    if (basename($file) == 'login.php' || basename($file) == 'logout.php') continue;
    
    $content = file_get_contents($file);
    
    // Regex to match the entire admin-sidebar div block
    // We look for <div class="admin-sidebar"> and everything up to the closing </div>
    // that immediately precedes <div class="admin-main">
    $pattern = '/<div class="admin-sidebar">.*?<\/div>\s*<div class="admin-main">/s';
    
    $replacement = "<?php require_once 'includes/sidebar.php'; ?>\n    <div class=\"admin-main\">";
    
    $new_content = preg_replace($pattern, $replacement, $content);
    
    if ($new_content !== $content && $new_content !== null) {
        file_put_contents($file, $new_content);
        echo "Updated sidebar in " . basename($file) . "\n";
    }
}
?>
