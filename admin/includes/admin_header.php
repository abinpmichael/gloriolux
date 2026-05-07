<?php
// Guard: this file should only be included, not called directly
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $redirect = defined('BASE_URL') ? BASE_URL . 'login.php' : '../login.php';
    header("Location: $redirect");
    exit;
}

$admin_page_title = $admin_page_title ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($admin_page_title) ?> | GLORIOLUX Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Site CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>assets/img/logo.png" type="image/png">

    <?php if (isset($admin_extra_css)) echo $admin_extra_css; ?>
</head>
<body>

<!-- Sidebar backdrop (mobile overlay) -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="admin-layout">

    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="admin-main">
        <!-- Top Bar -->
        <div class="admin-topbar">
            <button class="admin-menu-toggle" id="adminMenuToggle" title="Toggle Sidebar" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="admin-topbar-right">
                <span class="admin-topbar-user">
                    <i class="fas fa-user-shield"></i>
                    <span class="username-text"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></span>
                </span>
                <a href="<?= BASE_URL ?>index.php" class="admin-topbar-link" title="View Site" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span class="link-text">View Site</span>
                </a>
                <a href="<?= BASE_URL ?>logout.php" class="admin-topbar-link admin-topbar-logout" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="link-text">Logout</span>
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div class="admin-content">
