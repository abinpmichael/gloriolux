<?php
session_start();
require_once 'includes/header.php';

// Fetch page content
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = 'about'");
$stmt->execute();
$page = $stmt->fetch();

if (!$page) {
    // Fallback if not found in DB
    $page = [
        'title' => 'Our Story',
        'content' => '<p>Content coming soon...</p>'
    ];
}
?>
    <section class="about-section" style="max-width:800px;margin:2rem auto;padding:3rem;background:rgba(255,255,255,0.9);border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.05);backdrop-filter:blur(10px);">
        <h1 style="font-family:var(--font-heading);text-align:center;margin-bottom:2.5rem;font-size:2.5rem;color:var(--primary-color);"><?= htmlspecialchars($page['title']) ?></h1>
        <div class="cms-content" style="line-height:1.8;font-size:1.1rem;color:#444;">
            <?= $page['content'] ?>
        </div>
    </section>
<?php
require_once 'includes/footer.php';
?>
