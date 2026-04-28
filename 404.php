<?php
http_response_code(404);
require_once 'includes/db.php';
$page_meta_title = "Page Not Found | Gloriolux";
require_once 'includes/header.php';
?>
<main class="main-content" style="padding: 120px 0; text-align: center; min-height: 60vh; display: flex; align-items: center; justify-content: center; background: var(--bg-color);">
    <div class="container fade-up visible">
        <h1 style="font-family: var(--font-heading); font-size: 8rem; color: var(--secondary-color); margin-bottom: 1rem; line-height: 1;">404</h1>
        <h2 style="font-family: var(--font-heading); font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1.5rem;">Page Not Found</h2>
        <p style="font-size: 1.2rem; color: var(--text-light); margin-bottom: 3rem; max-width: 500px; margin-left: auto; margin-right: auto;">We apologize, but the page you are looking for has been moved, deleted, or does not exist.</p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="/Gloriolux/index.php" class="btn btn-primary" style="padding: 1rem 2.5rem;"><i class="fas fa-home" style="margin-right: 8px;"></i> Return Home</a>
            <a href="/Gloriolux/shop.php" class="btn btn-outline" style="padding: 1rem 2.5rem; color: var(--primary-color); border-color: var(--primary-color);"><i class="fas fa-shopping-bag" style="margin-right: 8px;"></i> View Collection</a>
        </div>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
