<?php
session_start();
if (isset($_GET['currency'])) {
    $currency = strtoupper($_GET['currency']);
    if (in_array($currency, ['CAD', 'USD'])) {
        $_SESSION['currency'] = $currency;
    }
}
$referrer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: $referrer");
exit;
