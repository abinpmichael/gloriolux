<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: /Gloriolux/login.php'); exit; }
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Update name and email
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $user_id]);
    
    // Update password if provided
    if (!empty($new_password) && $new_password === $confirm_password) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt2 = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt2->execute([$hashed, $user_id]);
        $msg = "Profile and password updated successfully!";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $msg = "Profile updated successfully!";
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Gloriolux/assets/css/style.css">
    <style>
        .admin-layout {display:flex; min-height:100vh;}
        .admin-sidebar {width:250px; background:var(--primary-color); color:#fff; padding:2rem 1rem; display:flex; flex-direction:column;}
        .admin-main {flex:1; padding:2rem; background:#f4f6f8;}
        .admin-sidebar a { display: block; padding: 1rem; color: #ccc; border-radius: 8px; margin-bottom: 0.5rem; text-decoration:none; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background-color: rgba(255,255,255,0.1); color: #fff; }
        .admin-logo { font-family: var(--font-heading); font-size: 1.5rem; text-align: center; margin-bottom: 3rem; color: #fff; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Admin Profile Settings</h2>
        </div>
        
        <?php if(isset($msg)): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;"><?= $msg ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div style="background:#f8d7da;color:#721c24;padding:1rem;border-radius:5px;margin-bottom:1rem;"><?= $error ?></div>
        <?php endif; ?>

        <div style="background:#fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 600px;">
            <form action="profile.php" method="POST">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; margin-bottom: 0.5rem; font-weight: bold;">Display Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" class="form-control" style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px;" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; margin-bottom: 0.5rem; font-weight: bold;">Email Address (Username)</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="form-control" style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px;" required>
                </div>
                
                <hr style="border: 0; border-top: 1px solid #eee; margin: 2rem 0;">
                <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Change Password (Optional)</h4>
                <p style="color: #666; font-size: 0.9rem; margin-bottom: 1.5rem;">Leave blank if you do not wish to change your current password.</p>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; margin-bottom: 0.5rem; font-weight: bold;">New Password</label>
                    <input type="password" name="new_password" class="form-control" style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label style="display:block; margin-bottom: 0.5rem; font-weight: bold;">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                
                <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; width: 100%;"><i class="fas fa-save"></i> Save Profile Settings</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
