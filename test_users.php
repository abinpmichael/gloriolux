<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("SELECT id, name, email, role, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Users in DB:\n";
    print_r($users);
    
    // Test the hash
    if(count($users) > 0) {
        $hash = $users[0]['password'];
        $match = password_verify('admin123', $hash);
        echo "\nPassword verify for admin123: " . ($match ? "TRUE" : "FALSE") . "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
