<?php
require_once '../includes/db.php';
require_once '../vendor/autoload.php';

session_start();
// Admin check (assuming you have one, or just simple script)

$stmt_set = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'stripe_secret_key'");
$stripe_secret = $stmt_set->fetchColumn() ?: 'sk_test_mockkey';
\Stripe\Stripe::setApiKey($stripe_secret);

if (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT stripe_session_id FROM orders WHERE id = ? AND shipping_address = '' OR shipping_address IS NULL");
    $stmt->execute([$order_id]);
    $session_id = $stmt->fetchColumn();

    if ($session_id) {
        try {
            $stripe_session = \Stripe\Checkout\Session::retrieve($session_id);
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

                $update = $pdo->prepare("UPDATE orders SET shipping_address = ? WHERE id = ?");
                $update->execute([$shipping_address, $order_id]);
                echo "Successfully synced shipping address for order #$order_id: $shipping_address";
            } else {
                echo "No address found in Stripe session for order #$order_id.";
            }
        } catch (Exception $e) {
            echo "Stripe Error: " . $e->getMessage();
        }
    } else {
        echo "Order #$order_id either has an address already or has no Stripe session.";
    }
} else {
    echo "Please provide an order ID, e.g., ?id=3";
}
?>
