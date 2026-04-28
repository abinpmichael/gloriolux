<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-sidebar">
    <div class="admin-logo">
        <a href="<?= BASE_URL ?>admin/index.php" class="brand-logo">
            <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Gloriolux Admin">
            <span class="logo-text">Glorio<span class="logo-highlight">lux</span></span>
        </a>
    </div>
    <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Dashboard</a>
    <a href="orders.php" class="<?= $current_page == 'orders.php' ? 'active' : '' ?>"><i class="fas fa-shopping-cart"></i> Orders</a>
    <a href="products.php" class="<?= $current_page == 'products.php' ? 'active' : '' ?>"><i class="fas fa-box"></i> Products</a>
    <a href="reviews.php" class="<?= $current_page == 'reviews.php' ? 'active' : '' ?>"><i class="fas fa-star"></i> Reviews CMS</a>
    <a href="blogs.php" class="<?= $current_page == 'blogs.php' ? 'active' : '' ?>"><i class="fas fa-pen-nib"></i> Blog CMS</a>
    <a href="slides.php" class="<?= $current_page == 'slides.php' ? 'active' : '' ?>"><i class="fas fa-images"></i> Home Slider</a>
    <a href="seo.php" class="<?= $current_page == 'seo.php' ? 'active' : '' ?>"><i class="fas fa-search"></i> SEO CMS</a>
    <a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="fas fa-user-cog"></i> Admin Profile</a>
    <a href="pages.php" class="<?= $current_page == 'pages.php' ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Pages CMS</a>
    <a href="faq.php" class="<?= $current_page == 'faq.php' ? 'active' : '' ?>"><i class="fas fa-question-circle"></i> FAQ CMS</a>
    <a href="subscribers.php" class="<?= $current_page == 'subscribers.php' ? 'active' : '' ?>"><i class="fas fa-envelope"></i> Subscribers</a>
    <a href="users.php" class="<?= $current_page == 'users.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Users</a>
    <a href="contact.php" class="<?= $current_page == 'contact.php' ? 'active' : '' ?>"><i class="fas fa-life-ring"></i> Contact Messages</a>
    <a href="settings.php" class="<?= $current_page == 'settings.php' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Settings</a>
    <div style="margin-top:auto;">
        <a href="<?= BASE_URL ?>index.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
        <a href="<?= BASE_URL ?>logout.php" style="color:#ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
