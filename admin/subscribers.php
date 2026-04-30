<?php
session_start();
require_once '../includes/db.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM subscribers WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: subscribers.php?success=deleted'); exit;
}

$stmt = $pdo->query('SELECT * FROM subscribers ORDER BY subscribed_at DESC');
$subscribers = $stmt->fetchAll();

$admin_page_title = 'Newsletter Subscribers';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Newsletter Subscribers <span style="font-size:1rem;color:var(--text-light);font-family:var(--font-body);">(<?= count($subscribers) ?>)</span></h2>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Subscriber deleted successfully.</div>
<?php endif; ?>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Email Address</th>
                <th>Subscribed On</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($subscribers as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td style="font-weight:bold;"><?= htmlspecialchars($s['email']) ?></td>
                <td><?= date('M j, Y g:i A', strtotime($s['subscribed_at'])) ?></td>
                <td>
                    <span class="status-badge" style="background:<?= $s['status'] === 'active' ? '#d1e7dd' : '#f8d7da' ?>;color:<?= $s['status'] === 'active' ? '#0f5132' : '#721c24' ?>;">
                        <?= ucfirst($s['status']) ?>
                    </span>
                </td>
                <td>
                    <a href="?delete=<?= $s['id'] ?>" onclick="return confirm('Delete this subscriber?');" style="color:#ff6b6b;" title="Delete">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($subscribers) === 0): ?>
                <tr><td colspan="5" style="text-align:center;">No subscribers yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
