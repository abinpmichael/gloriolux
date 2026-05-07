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
    $stmt = $pdo->prepare("SELECT c.quantity, c.custom_price_addon, c.topper_id, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->prepare("SELECT c.quantity, c.custom_price_addon, c.topper_id, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ? AND c.user_id IS NULL");
    $stmt->execute([$session_id]);
}
$cart_items = $stmt->fetchAll();

if (count($cart_items) === 0) {
    header("Location: " . BASE_URL . "cart.php");
    exit;
}

$line_items = [];
foreach ($cart_items as $item) {
    $item_name = $item['name'];
    if (!empty($item['topper_id'])) {
        $topper_stmt = $pdo->prepare("SELECT name FROM toppers WHERE id = ?");
        $topper_stmt->execute([$item['topper_id']]);
        $t_name = $topper_stmt->fetchColumn();
        if ($t_name) {
            $item_name .= " (w/ " . $t_name . ")";
        }
    }

    $final_price = $item['price'] + ($item['custom_price_addon'] ?? 0);

    $line_items[] = [
        'price_data' => [
            'currency' => strtolower($_SESSION['currency'] ?? 'cad'),
            'product_data' => [
                'name' => $item_name,
            ],
            'unit_amount' => (($_SESSION['currency'] ?? 'CAD') === 'USD' ? ($final_price * CAD_TO_USD) : $final_price) * 100, // Stripe expects cents
        ],
        'quantity' => $item['quantity'],
    ];
}

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'shipping_address_collection' => [
            'allowed_countries' => ['US', 'CA'],
        ],
        'phone_number_collection' => [
            'enabled' => true,
        ],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => BASE_URL_FULL . 'success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => BASE_URL_FULL . 'cart.php',
    ]);
    
    // Redirect to Stripe Checkout
    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit;
} catch (Exception $e) {
    echo "Error creating Stripe session: " . $e->getMessage();
}
?>
