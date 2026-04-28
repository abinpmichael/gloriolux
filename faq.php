<?php
require_once 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM faqs ORDER BY display_order ASC");
$faqs = $stmt->fetchAll();
?>
<main class="main-content">
    <div class="container" style="padding-top: 100px; padding-bottom: 80px; max-width: 800px;">
        <h1 style="text-align: center; margin-bottom: 3rem; font-family: var(--font-heading);">Frequently Asked Questions</h1>
        
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach($faqs as $faq): ?>
                <div class="glass-panel" style="padding: 1.5rem 2rem;">
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.2rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-question-circle" style="color: var(--secondary-color);"></i>
                        <?= htmlspecialchars($faq['question']) ?>
                    </h3>
                    <p style="color: var(--text-light); line-height: 1.6; padding-left: 28px;">
                        <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
            
            <?php if(count($faqs) === 0): ?>
                <p style="text-align: center; color: var(--text-light);">No FAQs available at the moment.</p>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 4rem;">
            <p style="margin-bottom: 1rem;">Still have questions?</p>
            <a href="<?= BASE_URL ?>contact.php" class="btn btn-outline" style="color: var(--primary-color); border-color: var(--primary-color);">Contact Support</a>
        </div>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
