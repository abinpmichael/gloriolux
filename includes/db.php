<?php
// Secure Error Logging Configuration
ini_set('display_errors', 0); // Hide errors from users (prevents leaking paths/keys)
ini_set('log_errors', 1);     // Enable error logging
ini_set('error_log', __DIR__ . '/../php_errors.log'); // Write errors to this specific file

$host = 'localhost';
$dbname = 'gloriolux_db';
$user = 'root'; // Default XAMPP user
$pass = ''; // Default XAMPP password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
