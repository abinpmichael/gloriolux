<?php
session_start();
require_once 'includes/db.php';

// Fetch Google Client ID from settings
$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'google_client_id'");
$google_client_id = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credential'])) {
    $id_token = $_POST['credential'];
    
    // Verify ID Token with Google API
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
    $response = file_get_contents($url);
    $payload = json_decode($response, true);
    
    if (isset($payload['aud']) && $payload['aud'] === $google_client_id) {
        $email = $payload['email'];
        $name = $payload['name'];
        $google_id = $payload['sub'];
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Create new user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), 'customer']);
            $user_id = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
        
        // Log user in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] === 'admin') {
            header('Location: ' . BASE_URL . 'admin/index.php');
        } else {
            header('Location: ' . BASE_URL . 'index.php');
        }
        exit;
    } else {
        header('Location: login.php?error=google_failed');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
