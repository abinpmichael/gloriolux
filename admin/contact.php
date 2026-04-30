<?php
session_start();
require_once '../includes/db.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM contacts WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: contact.php?success=deleted'); exit;
}

// Mark as read
if (isset($_GET['read'])) {
    $stmt = $pdo->prepare("UPDATE contacts SET status = 'read' WHERE id = ?");
    $stmt->execute([(int)$_GET['read']]);
    header('Location: contact.php?success=read'); exit;
}

$stmt = $pdo->query('SELECT * FROM contacts ORDER BY created_at DESC');
$contacts = $stmt->fetchAll();

$admin_page_title = 'Contact Messages';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Contact Messages</h2>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success">
        <i class="fas fa-check-circle"></i>
        <?= $_GET['success'] === 'deleted' ? 'Message deleted successfully.' : 'Message marked as read.' ?>
    </div>
<?php endif; ?>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($contacts as $c): ?>
            <tr style="<?= ($c['status'] ?? 'unread') === 'unread' ? 'font-weight:600;' : '' ?>">
                <td><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['subject']) ?></td>
                <td style="max-width:280px;"><?= nl2br(htmlspecialchars($c['message'])) ?></td>
                <td>
                    <?php if(($c['status'] ?? 'unread') === 'unread'): ?>
                        <span class="status-badge" style="background:#fff3cd;color:#856404;">Unread</span>
                    <?php else: ?>
                        <span class="status-badge" style="background:#d1e7dd;color:#0f5132;">Read</span>
                    <?php endif; ?>
                </td>
                <td style="display:flex;gap:0.75rem;align-items:center;">
                    <?php if(($c['status'] ?? 'unread') === 'unread'): ?>
                        <a href="?read=<?= $c['id'] ?>" title="Mark as Read" style="color:var(--secondary-color);">
                            <i class="fas fa-check"></i>
                        </a>
                    <?php endif; ?>
                    <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('Delete this message?');" title="Delete" style="color:#ff6b6b;">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($contacts) == 0): ?>
                <tr><td colspan="7" style="text-align:center;">No messages yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
