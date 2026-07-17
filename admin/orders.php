<?php
session_start();
require_once '../includes/db.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $pdo->prepare('UPDATE orders SET order_status=? WHERE id=?');
    $stmt->execute([$_POST['status'], (int)$_POST['order_id']]);
    header('Location: orders.php?success=1'); exit;
}

try {
    $pdo->exec("ALTER TABLE orders ADD COLUMN guest_email VARCHAR(255) NULL AFTER user_id, ADD COLUMN guest_name VARCHAR(255) NULL AFTER guest_email");
} catch(Exception $e) { /* Columns likely exist */ }
try {
    $pdo->exec("ALTER TABLE orders ADD COLUMN guest_phone VARCHAR(50) NULL AFTER guest_name");
} catch(Exception $e) { /* Column likely exists */ }

// Fetch all orders
$stmt = $pdo->query('
    SELECT o.*, 
           COALESCE(u.name, o.guest_name) as customer_name, 
           COALESCE(u.email, o.guest_email) as customer_email,
           o.guest_phone as customer_phone
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
');
$orders = $stmt->fetchAll();

$items_stmt = $pdo->prepare("SELECT oi.quantity, oi.custom_text, oi.custom_image, oi.custom_price_addon, t.name as topper_name, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id LEFT JOIN toppers t ON oi.topper_id = t.id WHERE oi.order_id = ?");

$admin_extra_css = '
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .dataTables_wrapper { width: 100%; overflow-x: auto; background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .dataTables_filter input { border: 1px solid #ddd; border-radius: 4px; padding: 5px 10px; margin-left: 5px; }
    table.dataTable.no-footer { border-bottom: none; }
    .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
    .status-Processing { background: #fff3cd; color: #856404; }
    .status-Shipped { background: #d1ecf1; color: #0c5460; }
    .status-Delivered { background: #d4edda; color: #155724; }
    .status-Cancelled { background: #f8d7da; color: #721c24; }
    .details-cell { display: table-cell; }
    @media (max-width: 600px) { .details-cell { display: block !important; } }
</style>
';

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
    <table id="ordersTable" class="data-table" style="width: 100%;">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Update</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $o): ?>
            <tr>
                <td data-label="Order #" style="font-weight:bold;">#<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></td>
                <td data-label="Customer">
                    <?= htmlspecialchars($o['customer_name'] ?? 'Guest') ?><br>
                    <small style="color:#666;"><?= htmlspecialchars($o['customer_email'] ?? '') ?></small>
                </td>
                <td data-label="Date"><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
                <td data-label="Amount">$<?= number_format($o['total_amount'], 2) ?></td>
                <td data-label="Payment">
                    <span style="color: <?= $o['payment_status'] == 'completed' ? 'green' : 'orange' ?>;">
                        <?= ucfirst($o['payment_status']) ?>
                    </span>
                </td>
                <td data-label="Status">
                    <span class="status-badge status-<?= $o['order_status'] ?? 'Processing' ?>">
                        <?= $o['order_status'] ?? 'Processing' ?>
                    </span>
                </td>
                <td data-label="Update">
                    <form method="POST" class="order-update-form" style="display: flex; gap: 5px;">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <select name="status" style="padding: 0.3rem; border-radius: 4px; border: 1px solid #ddd; font-size: 0.85rem;">
                            <option value="Processing" <?= ($o['order_status'] ?? '') == 'Processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="Shipped"    <?= ($o['order_status'] ?? '') == 'Shipped'    ? 'selected' : '' ?>>Shipped</option>
                            <option value="Delivered"  <?= ($o['order_status'] ?? '') == 'Delivered'  ? 'selected' : '' ?>>Delivered</option>
                            <option value="Cancelled"  <?= ($o['order_status'] ?? '') == 'Cancelled'  ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Save</button>
                    </form>
                </td>
                <td data-label="Details">
                    <button type="button" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="showOrderDetails(<?= $o['id'] ?>)">View</button>
                    
                    <!-- Hidden data for Modal -->
                    <template id="order-data-<?= $o['id'] ?>">
                        <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
                            <div style="flex: 1 1 250px;">
                                <h4 style="margin-bottom: 1rem; color: var(--secondary-color); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;"><i class="fas fa-truck"></i> Shipping Information</h4>
                                <p style="margin-bottom: 0.5rem;"><strong>Name:</strong> <?= htmlspecialchars($o['customer_name'] ?? 'Guest') ?></p>
                                <p style="margin-bottom: 0.5rem;"><strong>Email:</strong> <?= htmlspecialchars($o['customer_email'] ?? 'N/A') ?></p>
                                <p style="margin-bottom: 0.5rem;"><strong>Phone:</strong> <?= htmlspecialchars($o['customer_phone'] ?: 'Not provided') ?></p>
                                <p style="margin-bottom: 0.5rem;"><strong>Address:</strong><br><?= nl2br(htmlspecialchars($o['shipping_address'] ?: 'Not provided')) ?></p>
                            </div>
                            <div style="flex: 2 1 350px;">
                                <h4 style="margin-bottom: 1rem; color: var(--secondary-color); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;"><i class="fas fa-box-open"></i> Order Items</h4>
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    <?php 
                                    $items_stmt->execute([$o['id']]);
                                    $items = $items_stmt->fetchAll();
                                    foreach($items as $it): 
                                    ?>
                                        <li style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px dashed #eee;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <strong><?= $it['quantity'] ?>x <?= htmlspecialchars($it['name'] ?: 'Unknown Product') ?></strong>
                                                <span style="font-weight: bold;">$<?= number_format($it['price'] * $it['quantity'], 2) ?></span>
                                            </div>
                                            <?php if($it['topper_name']): ?>
                                                <div style="font-size: 0.85rem; color: #666; margin-top: 5px;">
                                                    <i class="fas fa-shapes"></i> Shape: <?= htmlspecialchars($it['topper_name']) ?> (+$<?= number_format($it['custom_price_addon'], 2) ?>)
                                                </div>
                                            <?php elseif($it['custom_image']): ?>
                                                <div style="margin-top: 5px;">
                                                    <a href="<?= BASE_URL ?><?= htmlspecialchars($it['custom_image']) ?>" target="_blank" style="font-size: 0.85rem; color: var(--secondary-color); text-decoration: underline;"><i class="fas fa-image"></i> View Custom Logo</a>
                                                </div>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div style="margin-top: 1rem; text-align: right; font-size: 1.2rem;">
                                    <strong>Total: $<?= number_format($o['total_amount'], 2) ?></strong>
                                </div>
                            </div>
                        </div>
                    </template>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Details Modal -->
<div id="details-modal" class="admin-modal" style="display:none; align-items:center; justify-content:center; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div class="admin-modal-content" style="background:#fff; width:100%; max-width:800px; padding:2.5rem; border-radius:20px; position:relative; box-shadow:0 20px 50px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <span onclick="document.getElementById('details-modal').style.display='none'" class="admin-modal-close" style="position:absolute; right:1.5rem; top:1rem; font-size:2rem; cursor:pointer; color:#999;">&times;</span>
        <h3 id="modal-title" style="margin-bottom:2rem; font-family:var(--font-heading); font-size:1.8rem;">Order Details</h3>
        <div id="modal-body"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#ordersTable').DataTable({
        "order": [[ 2, "desc" ]],
        "pageLength": 25,
        "language": {
            "search": "Search Orders:"
        }
    });
});

function showOrderDetails(orderId) {
    const content = document.getElementById('order-data-' + orderId).innerHTML;
    document.getElementById('modal-title').innerText = 'Order #' + orderId.toString().padStart(5, '0');
    document.getElementById('modal-body').innerHTML = content;
    document.getElementById('details-modal').style.display = 'flex';
}

window.onclick = function(event) {
    if (event.target.id === 'details-modal') {
        document.getElementById('details-modal').style.display = "none";
    }
}
</script>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
