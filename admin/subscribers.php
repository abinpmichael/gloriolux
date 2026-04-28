<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}
require_once '../includes/db.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM subscribers WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: subscribers.php?success=deleted');
    exit;
}

// Fetch subscribers
$stmt = $pdo->query('SELECT * FROM subscribers ORDER BY subscribed_at DESC');
$subscribers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Subscribers | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .admin-layout {display:flex; min-height:100vh;}
        .admin-sidebar {width:250px; background:var(--primary-color); color:#fff; padding:2rem 1rem; display:flex; flex-direction:column;}
        .admin-main {flex:1; padding:2rem; background:#f4f6f8;}
        .data-table {width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.05);}
        .data-table th, .data-table td {padding:1rem; border-bottom:1px solid #eee; text-align: left;}
        .data-table th {background:#f8f9fa;}
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
            <h2>Newsletter Subscribers (<?= count($subscribers) ?>)</h2>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;">
                Subscriber deleted successfully.
            </div>
        <?php endif; ?>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
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
                    <td style="font-weight: bold;"><?= htmlspecialchars($s['email']) ?></td>
                    <td><?= date('M j, Y g:i A', strtotime($s['subscribed_at'])) ?></td>
                    <td>
                        <span style="padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.8rem; background: <?= $s['status'] === 'active' ? '#d4edda' : '#f8d7da' ?>; color: <?= $s['status'] === 'active' ? '#155724' : '#721c24' ?>;">
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
                    <tr><td colspan="5" style="text-align: center;">No subscribers yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
