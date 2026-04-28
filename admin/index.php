<?php
session_start();
// Verify admin role
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: /Gloriolux/login.php"); 
    exit; 
}

require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gloriolux Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Gloriolux/assets/css/style.css">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        .admin-sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: #fff;
            padding: 2rem 1rem;
            display: flex;
            flex-direction: column;
        }
        .admin-main {
            flex: 1;
            padding: 2rem;
            background-color: #f4f6f8;
        }
        .admin-sidebar a {
            display: block;
            padding: 1rem;
            color: #ccc;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        .admin-sidebar a:hover, .admin-sidebar a.active {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        .admin-sidebar a i {
            margin-right: 10px;
            width: 20px;
        }
        .admin-logo {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 3rem;
            color: #fff;
        }
        .admin-logo span { color: var(--secondary-color); }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .stat-card h3 { color: var(--text-light); font-size: 0.9rem; font-family: var(--font-body); }
        .stat-card .value { font-size: 2rem; font-weight: bold; margin-top: 0.5rem; color: var(--primary-color); }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Dashboard Overview</h2>
        </div>
        
        <div class="dashboard-cards">
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="value">
                    <?php 
                        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
                        echo $stmt->fetchColumn();
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="value">
                    <?php 
                        $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
                        echo $stmt->fetchColumn();
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="value">
                    $<?php 
                        $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'completed'");
                        echo number_format((float)$stmt->fetchColumn(), 2);
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Support Tickets</h3>
                <div class="value">
                    <?php 
                        $stmt = $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'unread'");
                        echo $stmt->fetchColumn();
                    ?>
                </div>
            </div>
        </div>
        
        <div class="glass-panel" style="padding: 2rem; background: #fff; overflow-x: auto;">
            <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">Recent Orders</h3>
            <?php
            $stmt = $pdo->query("SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
            $recent_orders = $stmt->fetchAll();
            ?>
            <?php if(count($recent_orders) > 0): ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #f4f6f8; color: var(--text-light);">
                            <th style="padding: 1rem 0.5rem;">Order #</th>
                            <th style="padding: 1rem 0.5rem;">Date</th>
                            <th style="padding: 1rem 0.5rem;">Customer</th>
                            <th style="padding: 1rem 0.5rem;">Total</th>
                            <th style="padding: 1rem 0.5rem;">Status</th>
                            <th style="padding: 1rem 0.5rem; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_orders as $order): ?>
                        <tr style="border-bottom: 1px solid #f4f6f8;">
                            <td style="padding: 1rem 0.5rem; font-weight: bold;">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td style="padding: 1rem 0.5rem;"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            <td style="padding: 1rem 0.5rem;"><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></td>
                            <td style="padding: 1rem 0.5rem;">$<?= number_format($order['total_amount'], 2) ?></td>
                            <td style="padding: 1rem 0.5rem;">
                                <span style="background: <?= $order['order_status'] === 'Delivered' ? '#d4edda' : ($order['order_status'] === 'Processing' ? '#fff3cd' : '#e2e3e5') ?>; color: <?= $order['order_status'] === 'Delivered' ? '#155724' : ($order['order_status'] === 'Processing' ? '#856404' : '#383d41') ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                    <?= htmlspecialchars($order['order_status'] ?? 'Pending') ?>
                                </span>
                            </td>
                            <td style="padding: 1rem 0.5rem; text-align: right;">
                                <a href="/Gloriolux/admin/orders.php" class="btn btn-outline" style="padding: 0.3rem 0.8rem; font-size: 0.8rem; color: var(--secondary-color); border-color: var(--secondary-color);">Manage</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: var(--text-light); text-align: center; padding: 2rem 0;">No recent orders found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
