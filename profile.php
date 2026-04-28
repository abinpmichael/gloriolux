<?php
session_start();
// Require login to access profile
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/header.php';
require_once 'includes/db.php';

// Fetch latest user info
$stmt = $pdo->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // If user deleted from DB but session exists
    session_destroy();
    header("Location: login.php");
    exit;
}

// Fetch order history
$ord_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$ord_stmt->execute([$_SESSION['user_id']]);
$orders = $ord_stmt->fetchAll();
?>
<main class="main-content">
    <div class="container" style="padding-top: 40px; padding-bottom: 60px;">
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="font-family: var(--font-heading); margin-bottom: 2rem; color: var(--primary-color);">My Profile</h1>
            
            <div style="background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; gap: 2rem; align-items: flex-start; flex-wrap: wrap;">
                
                <!-- Avatar / Info block -->
                <div style="flex: 1; min-width: 250px;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                        <div style="width: 80px; height: 80px; background: var(--secondary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-family: var(--font-heading);">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h2 style="margin: 0; font-size: 1.5rem; color: var(--primary-color);"><?= htmlspecialchars($user['name']) ?></h2>
                            <p style="margin: 0; color: #666;">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 2rem;">
                        <h3 style="font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 1rem;">Account Details</h3>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>Role:</strong> <?= isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User' ?></p>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="/Gloriolux/admin/index.php" class="btn btn-outline" style="color: var(--primary-color); border-color: var(--primary-color); padding: 0.5rem 1.5rem;">Admin Panel</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn" style="background: #ff6b6b; color: #fff; padding: 0.5rem 1.5rem;">Logout</a>
                    </div>
                </div>
                
                <!-- Order History Placeholder -->
                <div style="flex: 2; min-width: 300px;">
                    <h3 style="font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 1rem;">Order History</h3>
                    <?php if (count($orders) > 0): ?>
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 1rem; overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                                <thead>
                                    <tr style="border-bottom: 1px solid #ddd;">
                                        <th style="padding: 0.5rem;">Order #</th>
                                        <th style="padding: 0.5rem;">Date</th>
                                        <th style="padding: 0.5rem;">Total</th>
                                        <th style="padding: 0.5rem;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 0.5rem; font-weight: bold;">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                        <td style="padding: 0.5rem;"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                        <td style="padding: 0.5rem;">$<?= number_format($order['total_amount'], 2) ?></td>
                                        <td style="padding: 0.5rem;">
                                            <span style="background: <?= $order['order_status'] === 'Delivered' ? '#d4edda' : '#e2e3e5' ?>; color: <?= $order['order_status'] === 'Delivered' ? '#155724' : '#383d41' ?>; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;">
                                                <?= htmlspecialchars($order['order_status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 2rem; text-align: center; color: #666;">
                            <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 1rem; color: #ccc;"></i>
                            <p>You haven't placed any orders yet.</p>
                            <a href="shop.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem; margin-top: 1rem;">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
