<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__.'/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php
    if (!isset($page_meta_title)) {
        $current_page = basename($_SERVER['PHP_SELF']);
        $seo_stmt = $pdo->prepare("SELECT * FROM seo_meta WHERE page_path = ?");
        $seo_stmt->execute([$current_page]);
        $seo_data = $seo_stmt->fetch();
        
        $page_meta_title = $seo_data ? $seo_data['meta_title'] : 'Gloriolux | Luxury Soy Candles & Gifting';
        $page_meta_desc = $seo_data ? $seo_data['meta_description'] : 'Premium hand-poured luxury soy candles.';
        $page_meta_keywords = $seo_data ? $seo_data['meta_keywords'] : 'candles, luxury, soy wax';
    }
    ?>
    <title><?php echo htmlspecialchars($page_meta_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_meta_desc); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page_meta_keywords); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="<?= BASE_URL ?>assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <a href="<?= BASE_URL ?>index.php" class="brand-logo">
                <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Gloriolux">
                <span class="logo-text">Glorio<span class="logo-highlight">lux</span></span>
            </a>
            <ul class="nav-links">
                <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
                <li><a href="<?= BASE_URL ?>shop.php">Shop</a></li>
                <li><a href="<?= BASE_URL ?>blog.php">Blog</a></li>
                <li><a href="<?= BASE_URL ?>about.php">Our Story</a></li>
                <li><a href="<?= BASE_URL ?>contact.php">Support</a></li>
            </ul>
            <div class="nav-icons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>admin/index.php" title="Admin Dashboard"><i class="fas fa-cog"></i></a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login.php" title="Login"><i class="fas fa-user"></i></a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>cart.php" class="cart-icon" title="Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count">
                        <?php 
                        if(isset($_SESSION['user_id'])) {
                            $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            echo $stmt->fetchColumn() ?: '0';
                        } else {
                            echo '0';
                        }
                        ?>
                    </span>
                </a>
            </div>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>
    
    <main class="main-content">
