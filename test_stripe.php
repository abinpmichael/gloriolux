<?php
require_once 'includes/db.php';
require_once 'vendor/autoload.php';

$stmt_set = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'stripe_secret_key'");
$stripe_secret = $stmt_set->fetchColumn() ?: 'sk_test_mockkey';
\Stripe\Stripe::setApiKey($stripe_secret);

if (strpos($_SERVER['HTTP_HOST'] ?? 'localhost', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '127.0.0.1', '127.0.0.1') !== false) {
    \Stripe\Stripe::setVerifySslCerts(false);
}

try {
    // We don't have a real session ID, so this will throw an error.
    // Let's just output the Stripe SDK version.
    echo "Stripe SDK Version: " . \Stripe\Stripe::VERSION . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
