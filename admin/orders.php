<?php
session_start();
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

$admin_page_title = 'Orders Management';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Manage Orders</h2>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Order status updated successfully!</div>
<?php endif; ?>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Status</th>
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
                    <form method="POST" class="order-update-form">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <select name="status">
                            <option value="Processing" <?= ($o['order_status'] ?? '') == 'Processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="Shipped"    <?= ($o['order_status'] ?? '') == 'Shipped'    ? 'selected' : '' ?>>Shipped</option>
                            <option value="Delivered"  <?= ($o['order_status'] ?? '') == 'Delivered'  ? 'selected' : '' ?>>Delivered</option>
                            <option value="Cancelled"  <?= ($o['order_status'] ?? '') == 'Cancelled'  ? 'selected' : '' ?>>Cancelled</option>
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

<?php require_once 'includes/admin_footer.php'; ?>
