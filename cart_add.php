<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // For simplicity, we use session ID if user is not logged in. 
    // In a full production app, we handle guest vs authenticated carts.
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $session_id = session_id();

    if ($product_id > 0 && $quantity > 0) {
        // Check if product exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        if ($stmt->fetch()) {
            // Check if already in cart
            if ($user_id) {
                $check = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $check->execute([$user_id, $product_id]);
            } else {
                $check = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND user_id IS NULL");
                $check->execute([$session_id, $product_id]);
            }
            
            $existing = $check->fetch();
            
            if ($existing) {
                // Update quantity
                $new_qty = $existing['quantity'] + $quantity;
                $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $update->execute([$new_qty, $existing['id']]);
            } else {
                // Insert new
                $insert = $pdo->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
                $insert->execute([$user_id, $session_id, $product_id, $quantity]);
            }
        }
    }
    
    header("Location: /Gloriolux/cart.php");
    exit;
} else {
    header("Location: /Gloriolux/index.php");
    exit;
}
?>
