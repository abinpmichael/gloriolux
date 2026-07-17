<?php
session_start();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$msg = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $stmt  = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $user_id]);

    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($new_password) && $new_password === $confirm_password) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt2  = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt2->execute([$hashed, $user_id]);
        $msg = "Profile and password updated successfully!";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $msg = "Profile updated successfully!";
    }
    // Refresh session name
    $_SESSION['name'] = $name;
}

$stmt  = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

$admin_page_title = 'Admin Profile';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Admin Profile Settings</h2>
</div>

<?php if($msg): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> <?= $msg ?></div>
<?php endif; ?>
<?php if($error): ?>
    <div class="admin-alert admin-alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<div class="settings-card">
    <form action="profile" method="POST">
        <div class="form-group">
            <label style="display:block;margin-bottom:0.5rem;font-weight:bold;">Display Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label style="display:block;margin-bottom:0.5rem;font-weight:bold;">Email Address (Login)</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="form-control" required>
        </div>

        <hr style="border:0;border-top:1px solid #eee;margin:1.5rem 0;">
        <h4 style="margin-bottom:0.75rem;color:var(--primary-color);">Change Password <small style="font-weight:400;font-size:0.85rem;color:#999;">(optional)</small></h4>

        <div class="form-group">
            <label style="display:block;margin-bottom:0.5rem;font-weight:bold;">New Password</label>
            <input type="password" name="new_password" class="form-control">
        </div>
        <div class="form-group">
            <label style="display:block;margin-bottom:0.5rem;font-weight:bold;">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </form>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
