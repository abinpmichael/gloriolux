<?php
require_once 'vendor/autoload.php';
require_once 'includes/db.php';
session_start();

// Fetch Stripe Secret Key from settings
$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'stripe_secret_key'");
$stripe_secret = $stmt->fetchColumn() ?: 'sk_test_mockkey';
\Stripe\Stripe::setApiKey($stripe_secret);

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

// Fetch cart items
if ($user_id) {
    $stmt = $pdo->prepare("SELECT c.quantity, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->prepare("SELECT c.quantity, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ? AND c.user_id IS NULL");
    $stmt->execute([$session_id]);
}
$cart_items = $stmt->fetchAll();

if (count($cart_items) === 0) {
    header("Location: /Gloriolux/cart.php");
    exit;
}

$line_items = [];
foreach ($cart_items as $item) {
    $line_items[] = [
        'price_data' => [
            'currency' => 'usd',
            'product_data' => [
                'name' => $item['name'],
            ],
            'unit_amount' => $item['price'] * 100, // Stripe expects cents
        ],
        'quantity' => $item['quantity'],
    ];
}

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => 'http://localhost/Gloriolux/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/Gloriolux/cart.php',
    ]);
    
    // Redirect to Stripe Checkout
    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit;
} catch (Exception $e) {
    echo "Error creating Stripe session: " . $e->getMessage();
}
?>
