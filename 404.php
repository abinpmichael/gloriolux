<?php
http_response_code(404);
require_once 'includes/db.php';
$page_meta_title = "Lost in Luxury | Page Not Found | Gloriolux";
require_once 'includes/header.php';
?>
<main class="main-content" style="padding: 150px 0; background: radial-gradient(circle at center, #fff 0%, #f9f9f9 100%); min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div class="container" style="text-align: center; max-width: 700px;">
        <div class="error-visual" style="position: relative; margin-bottom: 3rem;">
            <h1 style="font-family: var(--font-heading); font-size: 12rem; color: #f0f0f0; margin: 0; line-height: 1; letter-spacing: -5px;">404</h1>
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%;">
                <h2 style="font-family: var(--font-heading); font-size: 3rem; color: var(--primary-color); margin: 0; text-transform: uppercase; letter-spacing: 4px;">Lost in Luxury</h2>
            </div>
        </div>
        
        <p style="font-size: 1.25rem; color: var(--text-light); margin-bottom: 4rem; line-height: 1.8; font-weight: 300;">
            The page you are looking for has vanished into thin air, much like the delicate scent of a fading candle. Let us guide you back to our collection.
        </p>
        
        <div style="display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>index.php" class="btn btn-primary" style="padding: 1.2rem 3rem; border-radius: 50px; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                <i class="fas fa-home" style="margin-right: 10px;"></i> Back to Home
            </a>
            <a href="<?= BASE_URL ?>shop.php" class="btn btn-outline" style="padding: 1.2rem 3rem; border-radius: 50px; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; color: var(--primary-color); border: 2px solid var(--primary-color);">
                <i class="fas fa-shopping-bag" style="margin-right: 10px;"></i> Shop Collection
            </a>
        </div>
        
        <div style="margin-top: 5rem; padding-top: 3rem; border-top: 1px solid #eee;">
            <p style="font-size: 0.9rem; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;">Popular Categories</p>
            <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
                <?php
                try {
                    $stmt_cats = $pdo->query("SELECT * FROM categories LIMIT 3");
                    while ($cat = $stmt_cats->fetch()) {
                        echo '<a href="' . BASE_URL . 'shop.php?category=' . $cat['id'] . '" style="color: var(--secondary-color); text-decoration: none; font-weight: bold; font-size: 0.9rem; transition: color 0.3s;">' . htmlspecialchars($cat['name']) . '</a>';
                    }
                } catch (Exception $e) {}
                ?>
            </div>
        </div>
    </div>
</main>

<style>
    .error-visual h1 {
        animation: float 6s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
    }
</style>

<?php require_once 'includes/footer.php'; ?>
