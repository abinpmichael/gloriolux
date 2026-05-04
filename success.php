<?php
require_once 'includes/db.php';
session_start();

$session_id = $_GET['session_id'] ?? '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$cart_session_id = session_id();

// In a real app, you would verify the Stripe session_id with Stripe API here
// For now, we assume if they hit success.php, it's successful

// 1. Calculate total from cart
if ($user_id) {
    $stmt = $pdo->prepare("SELECT c.quantity, p.id, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->prepare("SELECT c.quantity, p.id, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ? AND c.user_id IS NULL");
    $stmt->execute([$cart_session_id]);
}
$cart_items = $stmt->fetchAll();

if (count($cart_items) == 0) {
    // Cart is empty, maybe they refreshed the page
    header("Location: index.php");
    exit;
}

$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += ($item['price'] * $item['quantity']);
}

// 2. Add 'order_status' column to orders table if it doesn't exist
try {
    $pdo->exec("ALTER TABLE orders ADD COLUMN order_status ENUM('Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Processing' AFTER payment_status");
} catch(Exception $e) { /* Column likely exists */ }

// 3. Create the order
$stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, payment_status, stripe_session_id, order_status) VALUES (?, ?, 'completed', ?, 'Processing')");
$stmt->execute([$user_id, $total_amount, $session_id]);
$order_id = $pdo->lastInsertId();

// 4. Create order items and update stock
$item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
$stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

foreach ($cart_items as $item) {
    // Insert item to order
    $item_stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
    
    // Decrease product stock
    $stock_stmt->execute([$item['quantity'], $item['id']]);
}


// 5. Clear the cart
if ($user_id) {
    $del = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $del->execute([$user_id]);
} else {
    $del = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
    $del->execute([$cart_session_id]);
}

// 6. Send Email Notifications
$set_stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'admin_email'");
$admin_email_setting = $set_stmt->fetchColumn();
$admin_email = $admin_email_setting ? $admin_email_setting : "admin@gloriolux.com";

$subject_admin = "New Order Received - #" . str_pad($order_id, 5, '0', STR_PAD_LEFT);
$message_admin = "A new order has been placed on Gloriolux.\n\nOrder ID: #" . $order_id . "\nTotal Amount: $" . number_format($total_amount, 2) . "\n\nPlease check the admin dashboard for details.";
$headers = "From: noreply@gloriolux.com";

// Send to Admin (Uses @ to suppress errors if local sendmail is unconfigured)
@mail($admin_email, $subject_admin, $message_admin, $headers);

// Attempt to send to Customer if logged in
if ($user_id) {
    $u_stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $u_stmt->execute([$user_id]);
    $u_email = $u_stmt->fetchColumn();
    if ($u_email) {
        $subject_cust = "Order Confirmation - Gloriolux #" . str_pad($order_id, 5, '0', STR_PAD_LEFT);
        $message_cust = "Thank you for shopping with Gloriolux!\n\nYour order #" . str_pad($order_id, 5, '0', STR_PAD_LEFT) . " has been received successfully and is now processing.\nTotal Amount: $" . number_format($total_amount, 2) . "\n\nWe will notify you once it ships.";
        @mail($u_email, $subject_cust, $message_cust, $headers);
    }
}

require_once 'includes/header.php';
?>
<main class="main-content" style="padding: 100px 0; text-align: center; min-height: 60vh;">
    <div class="container">
        <i class="fas fa-check-circle" style="font-size: 5rem; color: #28a745; margin-bottom: 2rem;"></i>
        <h1 style="font-family: var(--font-heading); margin-bottom: 1rem;">Payment Successful!</h1>
        <p style="font-size: 1.2rem; color: var(--text-light); margin-bottom: 2rem;">Thank you for your order. Your order number is <strong>#<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?></strong>.</p>
        <a href="index.php" class="btn btn-primary">Return to Homepage</a>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
