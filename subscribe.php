<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO subscribers (email) VALUES (?)");
            $stmt->execute([$email]);
            $_SESSION['subscribe_msg'] = "Thank you! You have successfully subscribed to our newsletter.";
            $_SESSION['subscribe_status'] = "success";
        } catch(PDOException $e) {
            $_SESSION['subscribe_msg'] = "An error occurred. Please try again.";
            $_SESSION['subscribe_status'] = "error";
        }
    } else {
        $_SESSION['subscribe_msg'] = "Please enter a valid email address.";
        $_SESSION['subscribe_status'] = "error";
    }
}

// Redirect back to the page the user came from, or index.php
$referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'index.php';
header("Location: $referer");
exit;
?>
