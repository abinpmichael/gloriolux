<?php require_once 'includes/header.php'; ?>
<?php
$stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogs = $stmt->fetchAll();
?>
    <div class="container" style="padding-top: 60px; padding-bottom: 50px;">
        <h1 style="text-align: center; margin-bottom: 3rem; font-family: var(--font-heading);">Our Blog</h1>
        
        <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2.5rem;">
            <?php foreach($blogs as $blog): ?>
                <div class="product-card" style="display: flex; flex-direction: column;">
                    <a href="<?= BASE_URL ?>blog/<?= htmlspecialchars($blog['slug']) ?>" style="display: block; flex: 1;">
                        <div style="height: 250px; overflow: hidden; position: relative;">
                            <?php if($blog['image_url']): ?>
                                <img src="<?= BASE_URL ?><?= htmlspecialchars($blog['image_url']) ?>" alt="Gloriolux Journal: <?= htmlspecialchars($blog['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: var(--primary-color);"></div>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                            <p style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;"><?= date('F j, Y', strtotime($blog['created_at'])) ?></p>
                            <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 1rem; color: var(--primary-color);"><?= htmlspecialchars($blog['title']) ?></h3>
                            <span style="color: var(--secondary-color); font-weight: bold; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">Read Article &rarr;</span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(count($blogs) == 0): ?>
            <p style="text-align: center; padding: 3rem;">No journal entries yet. Check back soon!</p>
        <?php endif; ?>
    </div>
<?php require_once 'includes/footer.php'; ?>
