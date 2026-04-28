<?php
$files_to_update = [
    'includes/header.php',
    'includes/footer.php',
    'admin/includes/sidebar.php',
    'login.php',
    'register.php'
];

foreach ($files_to_update as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Match the various text logos used across the app
        $patterns = [
            '/<a href="[^"]+" class="logo">Glorio<span>lux<\/span><\/a>/' => '<a href="/Gloriolux/index.php" class="logo"><img src="/Gloriolux/assets/img/logo.png" alt="Gloriolux" style="height: 50px;"></a>',
            '/<div class="admin-logo"><a href="[^"]+" style="[^"]+">Gloriolux Admin<\/a><\/div>/' => '<div class="admin-logo"><a href="/Gloriolux/admin/index.php"><img src="/Gloriolux/assets/img/logo.png" alt="Gloriolux Admin" style="height: 50px; filter: brightness(0) invert(1);"></a></div>',
            '/<div class="auth-logo">Glorio<span>lux<\/span><\/div>/' => '<div class="auth-logo"><img src="/Gloriolux/assets/img/logo.png" alt="Gloriolux" style="height: 60px; margin-bottom: 2rem;"></div>'
        ];
        
        $new_content = preg_replace(array_keys($patterns), array_values($patterns), $content);
        
        if ($new_content !== $content && $new_content !== null) {
            file_put_contents($path, $new_content);
            echo "Updated logo in $file\n";
        }
    }
}
?>
