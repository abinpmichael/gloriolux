<?php
require_once '../includes/db.php';
// Convenience redirect from /admin/login.php to the main login page
header('Location: ' . BASE_URL . 'login');
exit;
?>
