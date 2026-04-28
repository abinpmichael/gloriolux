<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . BASE_URL . 'login.php'); exit; }
require_once '../includes/db.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $pdo->prepare('UPDATE orders SET order_status=? WHERE id=?');
    $stmt->execute([$_POST['status'], (int)$_POST['order_id']]);
    header('Location: orders.php?success=1'); exit;
}

// Fetch all orders
$stmt = $pdo->query('
    SELECT o.*, u.name as customer_name, u.email as customer_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
');
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders Management | Admin</title>
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
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; }
        .status-Processing { background: #ffeeba; color: #856404; }
        .status-Shipped { background: #b8daff; color: #004085; }
        .status-Delivered { background: #d4edda; color: #155724; }
        .status-Cancelled { background: #f8d7da; color: #721c24; }
    </style>
    <link rel="icon" href="<?= BASE_URL ?>assets/img/logo.png" type="image/png">
</head>
<body>
<div class="admin-layout">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Manage Orders</h2>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;">Order status updated!</div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Fulfillment Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o): ?>
                <tr>
                    <td style="font-weight:bold;">#<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td>
                        <?= htmlspecialchars($o['customer_name'] ?? 'Guest') ?><br>
                        <small style="color:#666;"><?= htmlspecialchars($o['customer_email'] ?? '') ?></small>
                    </td>
                    <td><?= date('M j, Y g:i A', strtotime($o['created_at'])) ?></td>
                    <td>$<?= number_format($o['total_amount'], 2) ?></td>
                    <td>
                        <span style="color: <?= $o['payment_status'] == 'completed' ? 'green' : 'orange' ?>;">
                            <?= ucfirst($o['payment_status']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?= $o['order_status'] ?? 'Processing' ?>">
                            <?= $o['order_status'] ?? 'Processing' ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" style="padding: 0.3rem; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="Processing" <?= ($o['order_status'] ?? '') == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="Shipped" <?= ($o['order_status'] ?? '') == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="Delivered" <?= ($o['order_status'] ?? '') == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="Cancelled" <?= ($o['order_status'] ?? '') == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($orders) == 0): ?>
                    <tr><td colspan="7" style="text-align: center;">No orders yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
