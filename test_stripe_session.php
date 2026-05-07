<?php
require_once 'includes/db.php';
require_once 'vendor/autoload.php';

$stmt = $pdo->query('SELECT stripe_session_id FROM orders WHERE stripe_session_id IS NOT NULL ORDER BY id DESC LIMIT 1');
$session_id = $stmt->fetchColumn();

if (!$session_id) {
    die("No session ID found in orders table.\n");
}

$stmt_set = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'stripe_secret_key'");
$stripe_secret = $stmt_set->fetchColumn() ?: 'sk_test_mockkey';
\Stripe\Stripe::setApiKey($stripe_secret);

if (strpos($_SERVER['HTTP_HOST'] ?? 'localhost', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '127.0.0.1', '127.0.0.1') !== false) {
    \Stripe\Stripe::setVerifySslCerts(false);
}

try {
    echo "Retrieving Session ID: " . $session_id . "\n";
    $stripe_session = \Stripe\Checkout\Session::retrieve($session_id);
    echo "Customer Details:\n";
    print_r($stripe_session->customer_details);
    echo "\nShipping Details:\n";
    print_r($stripe_session->shipping_details);
    echo "\nShipping (legacy):\n";
    print_r($stripe_session->shipping);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
