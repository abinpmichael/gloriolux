<?php
require 'includes/db.php';
$stmt_set = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'stripe_secret_key'");
$stripe_secret = $stmt_set->fetchColumn() ?: 'sk_test_mockkey';
echo "API Key in DB: " . $stripe_secret . "\n";
