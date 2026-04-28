<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . BASE_URL . 'login.php'); exit; }
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reviews | Admin</title>
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
            <h2>Manage Customer Reviews</h2>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;">Review deleted successfully.</div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th style="width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reviews as $r): ?>
                <tr>
                    <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                    <td style="font-weight:bold;"><a href="<?= BASE_URL ?>product.php?id=<?= $r['product_id'] ?>" target="_blank"><?= htmlspecialchars($r['product_name']) ?></a></td>
                    <td><?= htmlspecialchars($r['reviewer_name']) ?></td>
                    <td style="color:#f1c40f;">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i class="fa<?= $i <= $r['rating'] ? 's' : 'r' ?> fa-star"></i>
                        <?php endfor; ?>
                    </td>
                    <td><div style="max-height: 60px; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($r['comment']) ?></div></td>
                    <td>
                        <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this review?');" style="color:#ff6b6b;"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($reviews) == 0): ?>
                    <tr><td colspan="6" style="text-align: center;">No reviews submitted yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
