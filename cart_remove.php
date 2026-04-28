<?php
session_start();
require_once 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

if ($id > 0) {
    if ($user_id) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND session_id = ? AND user_id IS NULL");
        $stmt->execute([$id, $session_id]);
    }
}

header("Location: /Gloriolux/cart.php");
exit;
?>
