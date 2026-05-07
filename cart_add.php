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
        // Handle custom shape selection
        $custom_text = null; // Removed per user request
        $topper_id = !empty($_POST['topper_id']) ? (int)$_POST['topper_id'] : null;
        
        $custom_price_addon = 0.00;
        $custom_image = null;
        
        if ($topper_id) {
            $topper_stmt = $pdo->prepare("SELECT t.image_url, t.price_addon, pt.custom_image_url FROM toppers t LEFT JOIN product_toppers pt ON t.id = pt.topper_id AND pt.product_id = ? WHERE t.id = ?");
            $topper_stmt->execute([$product_id, $topper_id]);
            $topper = $topper_stmt->fetch();
            if ($topper) {
                $custom_image = !empty($topper['custom_image_url']) ? $topper['custom_image_url'] : $topper['image_url'];
                $custom_price_addon = $topper['price_addon'];
            } else {
                $topper_id = null;
            }
        }

        // Check if product exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        if ($stmt->fetch()) {
            // Check if already in cart with SAME customization
            if ($user_id) {
                $check = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND IFNULL(topper_id, 0) = ?");
                $check->execute([$user_id, $product_id, $topper_id ?? 0]);
            } else {
                $check = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND user_id IS NULL AND IFNULL(topper_id, 0) = ?");
                $check->execute([$session_id, $product_id, $topper_id ?? 0]);
            }
            
            $existing = $check->fetch();
            
            if ($existing) {
                // Update quantity
                $new_qty = $existing['quantity'] + $quantity;
                $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $update->execute([$new_qty, $existing['id']]);
            } else {
                // Insert new
                $insert = $pdo->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity, custom_text, custom_image, topper_id, custom_price_addon) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $insert->execute([$user_id, $session_id, $product_id, $quantity, $custom_text, $custom_image, $topper_id, $custom_price_addon]);
            }
        }
    }
    
    header("Location: " . BASE_URL . "cart.php");
    exit;
} else {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>
