<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . BASE_URL . 'login.php'); exit; }
require_once '../includes/db.php';

// Handle Delete User
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Prevent self-deletion
    if ($id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
        $stmt->execute([$id]);
        header('Location: users.php?success=1'); 
        exit;
    } else {
        header('Location: users.php?error=1');
        exit;
    }
}



$stmt = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .admin-layout {display:flex; min-height:100vh;}
        .admin-sidebar {width:250px; background:var(--primary-color); color:#fff; padding:2rem 1rem; display:flex; flex-direction:column;}
        .admin-main {flex:1; padding:2rem; background:#f4f6f8;}
        .data-table {width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.05);}
        .data-table th, .data-table td {padding:1rem; border-bottom:1px solid #eee; text-align: left;}
        .admin-sidebar a { display: block; padding: 1rem; color: #ccc; border-radius: 8px; margin-bottom: 0.5rem; text-decoration:none; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background-color: rgba(255,255,255,0.1); color: #fff; }
        .admin-logo { font-family: var(--font-heading); font-size: 1.5rem; text-align: center; margin-bottom: 3rem; color: #fff; }
    </style>
    <link rel="icon" href="<?= BASE_URL ?>assets/img/logo.png" type="image/png">
</head>
<body>
<div class="admin-layout">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Manage Users</h2>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;">
                <?php
                    if($_GET['success'] == 1) echo "User deleted successfully.";
                    if($_GET['success'] == 2) echo "User promoted to admin.";
                    if($_GET['success'] == 3) echo "User demoted to customer.";
                ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div style="background:#f8d7da;color:#721c24;padding:1rem;border-radius:5px;margin-bottom:1rem;">
                <?php
                    if($_GET['error'] == 1) echo "You cannot delete your own account.";
                    if($_GET['error'] == 2) echo "You cannot demote yourself.";
                ?>
            </div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date Joined</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th style="text-align: right;">Actions</th>
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
                            <span style="background: #cce5ff; color: #004085; padding: 3px 8px; border-radius: 4px; font-size: 0.85rem;">Admin</span>
                        <?php else: ?>
                            <span style="background: #e2e3e5; color: #383d41; padding: 3px 8px; border-radius: 4px; font-size: 0.85rem;">Customer</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <?php if($user['id'] !== $_SESSION['user_id']): ?>
                            <a href="?delete=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to completely delete this user and all their data?');" style="color:#ff6b6b;" title="Delete"><i class="fas fa-trash"></i></a>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic;">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($users) == 0): ?>
                    <tr><td colspan="5" style="text-align: center;">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
