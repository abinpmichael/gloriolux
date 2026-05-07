<?php
require 'includes/db.php';
$stmt = $pdo->query('SELECT slug, title FROM pages');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
