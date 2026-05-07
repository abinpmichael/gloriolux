<?php
require_once 'includes/db.php';
$stmt = $pdo->query('SELECT id, shipping_address, guest_name FROM orders ORDER BY id DESC LIMIT 5');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
