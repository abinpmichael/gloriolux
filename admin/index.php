<?php
session_start();
require_once '../includes/db.php';

$admin_page_title = 'Dashboard';
require_once 'includes/admin_header.php';
?>
<div class="admin-page-header">
    <h2>Dashboard Overview</h2>
</div>

<div class="stat-cards-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-box"></i></div>
        <h3>Total Products</h3>
        <div class="value"><?php $stmt = $pdo->query("SELECT COUNT(*) FROM products"); echo $stmt->fetchColumn(); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
        <h3>Total Orders</h3>
        <div class="value"><?php $stmt = $pdo->query("SELECT COUNT(*) FROM orders"); echo $stmt->fetchColumn(); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <h3>Total Revenue</h3>
        <div class="value">$<?php $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'completed'"); echo number_format((float)$stmt->fetchColumn(), 2); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-life-ring"></i></div>
        <h3>Unread Messages</h3>
        <div class="value"><?php $stmt = $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'unread'"); echo $stmt->fetchColumn(); ?></div>
    </div>
</div>
        
<div class="data-table-wrap">
    <div style="padding: 1.5rem 1.5rem 0;">
        <h3 style="margin: 0 0 1rem; font-family: var(--font-heading);">Recent Orders</h3>
    </div>
    <?php
    $stmt = $pdo->query("SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
    $recent_orders = $stmt->fetchAll();
    ?>
    <?php if(count($recent_orders) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recent_orders as $order): ?>
                <tr>
                    <td style="font-weight:bold;">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                    <td><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></td>
                    <td>$<?= number_format($order['total_amount'], 2) ?></td>
                    <td>
                        <span class="status-badge status-<?= $order['order_status'] ?? 'Processing' ?>">
                            <?= htmlspecialchars($order['order_status'] ?? 'Pending') ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <a href="<?= BASE_URL ?>admin/orders.php" class="btn btn-outline" style="padding:0.3rem 0.8rem; font-size:0.8rem; color:var(--secondary-color); border-color:var(--secondary-color);">Manage</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#999; text-align:center; padding:2rem;">No recent orders found.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
