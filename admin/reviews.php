<?php
session_start();
require_once '../includes/db.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM reviews WHERE id=?');
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: reviews.php?success=deleted'); exit;
}

$stmt = $pdo->query('
    SELECT r.*, p.name as product_name, u.name as reviewer_name 
    FROM reviews r 
    JOIN products p ON r.product_id = p.id 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC
');
$reviews = $stmt->fetchAll();

$admin_page_title = 'Manage Reviews';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Customer Reviews</h2>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Review deleted successfully.</div>
<?php endif; ?>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Customer</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reviews as $r): ?>
            <tr>
                <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                <td style="font-weight:bold;">
                    <a href="<?= BASE_URL ?>product.php?id=<?= $r['product_id'] ?>" target="_blank">
                        <?= htmlspecialchars($r['product_name']) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($r['reviewer_name']) ?></td>
                <td style="color:#f1c40f;">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <i class="fa<?= $i <= $r['rating'] ? 's' : 'r' ?> fa-star"></i>
                    <?php endfor; ?>
                </td>
                <td><div style="max-height: 60px; overflow: hidden;"><?= htmlspecialchars($r['comment']) ?></div></td>
                <td>
                    <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this review?');" style="color:#ff6b6b;">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($reviews) == 0): ?>
                <tr><td colspan="6" style="text-align:center;">No reviews submitted yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
