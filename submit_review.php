<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $product_id = (int)$_POST['product_id'];
    $rating = (int)$_POST['rating'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];
    
    // Basic validation
    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        // Insert review
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $rating, $comment]);
        
        header("Location: /Gloriolux/product.php?id=" . $product_id . "&review=success");
        exit;
    }
}

header("Location: /Gloriolux/shop.php");
exit;
?>
