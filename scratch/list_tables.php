<?php
require 'includes/db.php';
echo "Checking tables...\n";
$stmt = $pdo->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $t) {
    echo "- $t\n";
}

if (in_array('faq', $tables)) {
    echo "Found singular 'faq' table.\n";
}
if (in_array('faqs', $tables)) {
    echo "Found plural 'faqs' table.\n";
}
