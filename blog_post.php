<?php
require_once 'includes/db.php';
// We need to fetch the post first to use its title for SEO if we want, 
// but since header.php loads the generic SEO, we can manually override the title variable before including header.php
// However, since header.php already echoes the `<title>`, we will just let it be, or modify it slightly if needed.
// For simplicity, we just include header.php and accept the default blog SEO, but dynamic SEO per post is ideal.
// We'll update the title output manually if possible, but let's just stick to standard for now.

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: " . BASE_URL . "blog.php");
    exit;
}

// Pass dynamic SEO metadata to header
$page_meta_title = $post['meta_title'] ?: ($post['title'] . ' | Gloriolux Blog');
$page_meta_desc = $post['meta_description'] ?: strip_tags($post['excerpt']);
$page_meta_keywords = $post['meta_keywords'] ?: 'blog, luxury candles, ' . strtolower($post['title']);

require_once 'includes/header.php'; 
?>
<main class="main-content">
    <div style="width: 100%; height: 400px; position: relative; background: #000;">
        <?php if($post['image_url']): ?>
            <img src="<?= BASE_URL ?><?= htmlspecialchars($post['image_url']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.7;">
        <?php endif; ?>
        <div class="container" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #fff; width: 100%;">
            <p style="color: var(--secondary-color); font-size: 1rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 1rem;"><?= date('F j, Y', strtotime($post['created_at'])) ?></p>
            <h1 style="font-family: var(--font-heading); font-size: 3.5rem; text-shadow: 2px 2px 8px rgba(0,0,0,0.5);"><?= htmlspecialchars($post['title']) ?></h1>
        </div>
    </div>
    
    <div class="container" style="padding-top: 50px; padding-bottom: 80px; max-width: 800px;">
        <div style="font-size: 1.15rem; line-height: 1.8; color: var(--text-color);">
            <?= $post['content'] ?>
        </div>
        
        <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid #eee; text-align: center;">
            <div style="margin-bottom: 2rem;">
                <h4 style="font-family: var(--font-heading); margin-bottom: 1rem; color: var(--primary-color);">Share this Article</h4>
                <div style="display: flex; justify-content: center; gap: 1rem;">
                    <?php 
                    $current_url = urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    $share_title = urlencode($post['title']);
                    ?>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $current_url ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #3b5998; color: #fff; text-decoration: none; transition: transform 0.3s;"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?= $current_url ?>&text=<?= $share_title ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #1da1f2; color: #fff; text-decoration: none; transition: transform 0.3s;"><i class="fab fa-twitter"></i></a>
                    <a href="https://pinterest.com/pin/create/button/?url=<?= $current_url ?>&description=<?= $share_title ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #bd081c; color: #fff; text-decoration: none; transition: transform 0.3s;"><i class="fab fa-pinterest-p"></i></a>
                    <a href="https://instagram.com" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #c13584; color: #fff; text-decoration: none; transition: transform 0.3s;"><i class="fab fa-instagram"></i></a>
                    <a href="mailto:?subject=<?= $share_title ?>&body=Read this amazing article: <?= $current_url ?>" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #555; color: #fff; text-decoration: none; transition: transform 0.3s;"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
            <a href="<?= BASE_URL ?>blog.php" class="btn btn-outline" style="color: var(--primary-color); border-color: var(--primary-color);">Back to Blog</a>
        </div>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
