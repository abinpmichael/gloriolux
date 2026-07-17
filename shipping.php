<?php
require_once 'includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = 'shipping'");
$stmt->execute();
$page = $stmt->fetch();

if (!$page) {
    $page = ['title' => 'Shipping & Returns', 'content' => '<p>Page content coming soon.</p>'];
}
?>
<div class="container" style="padding-top: 60px; padding-bottom: 80px; max-width: 800px;">
    <h1 style="text-align: center; margin-bottom: 3rem; font-family: var(--font-heading);"><?= htmlspecialchars($page['title']) ?></h1>
    
    <div class="glass-panel" style="padding: 3rem; line-height: 1.8;">
        <?= $page['content'] // Raw HTML allowed from CMS ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
