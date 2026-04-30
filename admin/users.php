<?php
session_start();
require_once '../includes/db.php';

// Handle Delete User
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
        $stmt->execute([$id]);
        header('Location: users.php?success=1'); exit;
    } else {
        header('Location: users.php?error=1'); exit;
    }
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$admin_page_title = 'Manage Users';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Manage Users</h2>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success">
        <i class="fas fa-check-circle"></i>
        <?php
            if($_GET['success'] == 1) echo "User deleted successfully.";
            if($_GET['success'] == 2) echo "User promoted to admin.";
            if($_GET['success'] == 3) echo "User demoted to customer.";
        ?>
    </div>
<?php endif; ?>
<?php if(isset($_GET['error'])): ?>
    <div class="admin-alert admin-alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php
            if($_GET['error'] == 1) echo "You cannot delete your own account.";
            if($_GET['error'] == 2) echo "You cannot demote yourself.";
        ?>
    </div>
<?php endif; ?>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date Joined</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user): ?>
            <tr>
                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                <td style="font-weight:bold;"><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                    <?php if($user['role'] === 'admin'): ?>
                        <span class="status-badge" style="background:#cce5ff;color:#004085;">Admin</span>
                    <?php else: ?>
                        <span class="status-badge" style="background:#e2e3e5;color:#383d41;">Customer</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;">
                    <?php if($user['id'] !== $_SESSION['user_id']): ?>
                        <a href="?delete=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');" style="color:#ff6b6b;" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    <?php else: ?>
                        <span style="color:#999; font-style:italic;">You</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($users) == 0): ?>
                <tr><td colspan="5" style="text-align:center;">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
