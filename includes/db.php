<?php
// Secure Error Logging Configuration
ini_set('display_errors', 0); // Hide errors from users (prevents leaking paths/keys)
ini_set('log_errors', 1);     // Enable error logging
ini_set('error_log', __DIR__ . '/../php_errors.log'); // Write errors to this specific file

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'gloriolux_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

// Determine the correct base URL dynamically
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$current_dir = str_replace('\\', '/', __DIR__);
$app_root = str_replace('\\', '/', realpath($current_dir . '/..'));

// Case-insensitive replacement for Windows
$base_url = preg_replace('/' . preg_quote($doc_root, '/') . '/i', '', $app_root, 1);

// Ensure leading and trailing slashes
$base_url = '/' . ltrim($base_url, '/');
$base_url = rtrim($base_url, '/') . '/';

define('BASE_URL', $base_url);


$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL_FULL', $protocol . '://' . $host . BASE_URL);
define('CAD_TO_USD', 0.73); // Current approximate exchange rate

function formatPrice($price) {
    $currency = $_SESSION['currency'] ?? 'CAD';
    if ($currency === 'USD') {
        $price *= CAD_TO_USD;
    }
    return '$' . number_format($price, 2) . ' ' . $currency;
}





try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
