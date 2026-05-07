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
    $stmt = $pdo->prepare("SELECT c.quantity, c.custom_text, c.custom_image, c.custom_price_addon, c.topper_id, p.id, p.name, p.price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->prepare("SELECT c.quantity, c.custom_text, c.custom_image, c.custom_price_addon, c.topper_id, p.id, p.name, p.price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ? AND c.user_id IS NULL");
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
    $base_price = $item['price'] + ($item['custom_price_addon'] ?? 0);
    $total_amount += ($base_price * $item['quantity']);
}

// 2. Add 'order_status', 'guest_email', 'guest_name' columns to orders table if they don't exist
try {
    $pdo->exec("ALTER TABLE orders ADD COLUMN order_status ENUM('Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Processing' AFTER payment_status");
} catch(Exception $e) { /* Column likely exists */ }
try {
    $pdo->exec("ALTER TABLE orders ADD COLUMN guest_email VARCHAR(255) NULL AFTER user_id, ADD COLUMN guest_name VARCHAR(255) NULL AFTER guest_email");
} catch(Exception $e) { /* Columns likely exist */ }
try {
    $pdo->exec("ALTER TABLE orders ADD COLUMN guest_phone VARCHAR(50) NULL AFTER guest_name");
} catch(Exception $e) { /* Column likely exists */ }

// Attempt to get shipping address from Stripe
$shipping_address = '';
if ($session_id) {
    try {
        require_once 'vendor/autoload.php';
        $stmt_set = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'stripe_secret_key'");
        $stripe_secret = $stmt_set->fetchColumn() ?: 'sk_test_mockkey';
        \Stripe\Stripe::setApiKey($stripe_secret);
        
        // Disable SSL cert verification for local Windows XAMPP environments
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
            \Stripe\Stripe::setVerifySslCerts(false);
        }
        
        $stripe_session = \Stripe\Checkout\Session::retrieve($session_id);
        $customer_email = $stripe_session->customer_details->email ?? null;
        $customer_name = $stripe_session->customer_details->name ?? null;
        $customer_phone = $stripe_session->customer_details->phone ?? null;

        $addr = null;
        if (!empty($stripe_session->shipping_details->address)) {
            $addr = $stripe_session->shipping_details->address;
        } elseif (!empty($stripe_session->customer_details->address)) {
            $addr = $stripe_session->customer_details->address;
        } elseif (!empty($stripe_session->shipping->address)) {
            $addr = $stripe_session->shipping->address;
        }

        if ($addr) {
            $parts = [];
            $line1 = $addr->line1 ?? $addr['line1'] ?? '';
            $line2 = $addr->line2 ?? $addr['line2'] ?? '';
            $city = $addr->city ?? $addr['city'] ?? '';
            $state = $addr->state ?? $addr['state'] ?? '';
            $postal_code = $addr->postal_code ?? $addr['postal_code'] ?? '';
            $country = $addr->country ?? $addr['country'] ?? '';

            if (!empty($line1)) $parts[] = $line1;
            if (!empty($line2)) $parts[] = $line2;
            if (!empty($city)) $parts[] = $city;
            if (!empty($state)) $parts[] = $state;
            if (!empty($postal_code)) $parts[] = $postal_code;
            if (!empty($country)) $parts[] = $country;
            
            $shipping_address = implode(', ', $parts);
        }
    } catch(Exception $e) {
        $shipping_address = "Stripe Error: " . $e->getMessage();
    }
} else {
    $customer_email = null;
    $customer_name = null;
    $customer_phone = null;
    $shipping_address = "No session ID provided.";
}

// 3. Create the order
$stmt = $pdo->prepare("INSERT INTO orders (user_id, guest_email, guest_name, guest_phone, total_amount, payment_status, stripe_session_id, order_status, shipping_address) VALUES (?, ?, ?, ?, ?, 'completed', ?, 'Processing', ?)");
$stmt->execute([$user_id, $customer_email, $customer_name, $customer_phone, $total_amount, $session_id, $shipping_address]);
$order_id = $pdo->lastInsertId();

// 4. Create order items and update stock
$item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, custom_text, custom_image, topper_id, custom_price_addon) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stock_stmt = $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");

$out_of_stock_alerts = [];

foreach ($cart_items as $item) {
    // Insert item to order
    $item_price = $item['price'] + ($item['custom_price_addon'] ?? 0);
    $item_stmt->execute([$order_id, $item['id'], $item['quantity'], $item_price, $item['custom_text'], $item['custom_image'], $item['topper_id'], $item['custom_price_addon']]);
    
    // Check if it's going out of stock
    if ($item['stock'] > 0 && ($item['stock'] - $item['quantity']) <= 0) {
        $out_of_stock_alerts[] = $item['name'];
    }
    
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
$message_admin = "A new order has been placed on Gloriolux.\n\n" .
                 "Order ID: #" . $order_id . "\n" .
                 "Total Amount: $" . number_format($total_amount, 2) . "\n" .
                 "Customer: " . ($customer_name ?: 'Guest') . "\n" .
                 "Email: " . ($customer_email ?: 'Not provided') . "\n" .
                 "Phone: " . ($customer_phone ?: 'Not provided') . "\n" .
                 "Log in to the admin panel to view full order details including shipping address.";

$headers = "From: no-reply@gloriolux.com\r\n";
$headers .= "Reply-To: no-reply@gloriolux.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

@mail($admin_email, $subject_admin, $message_admin, $headers);

if (!empty($out_of_stock_alerts)) {
    $subject_stock = "ACTION REQUIRED: Product(s) Out of Stock - Gloriolux";
    $message_stock = "The following products have just run out of stock and need your attention:\n\n";
    foreach($out_of_stock_alerts as $alert_item) {
        $message_stock .= "- " . $alert_item . "\n";
    }
    $message_stock .= "\nPlease log in to the admin panel to update your inventory.";
    @mail($admin_email, $subject_stock, $message_stock, $headers);
}

// 7. Send to Customer if logged in
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
