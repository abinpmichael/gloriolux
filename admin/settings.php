<?php
session_start();
// Verify admin role
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: " . BASE_URL . "login.php"); 
    exit; 
}

require_once '../includes/db.php';

// Handle setting update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_keys') {
    $secret_key = $_POST['stripe_secret_key'];
    $publishable_key = $_POST['stripe_publishable_key'];
    $tax_enabled = isset($_POST['tax_enabled']) ? '1' : '0';
    $tax_rate = $_POST['tax_rate'];
    
    // Update Settings
    $stmt1 = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'stripe_secret_key'");
    $stmt1->execute([$secret_key]);

    $stmt2 = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'stripe_publishable_key'");
    $stmt2->execute([$publishable_key]);

    $stmt3 = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'tax_enabled'");
    $stmt3->execute([$tax_enabled]);

    $stmt4 = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'tax_rate'");
    $stmt4->execute([$tax_rate]);
    
    $admin_email = $_POST['admin_email'];
    $stmt5 = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('admin_email', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt5->execute([$admin_email, $admin_email]);

    header("Location: settings.php?success=1");
    exit;
}

// Fetch current keys
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('stripe_secret_key', 'stripe_publishable_key', 'tax_enabled', 'tax_rate', 'admin_email')");
$settings_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stripe_secret = $settings_data['stripe_secret_key'] ?? '';
$stripe_publishable = $settings_data['stripe_publishable_key'] ?? '';
$tax_enabled = ($settings_data['tax_enabled'] ?? '0') === '1';
$tax_rate = $settings_data['tax_rate'] ?? '5.00';
$admin_email = $settings_data['admin_email'] ?? 'admin@gloriolux.com';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Gloriolux Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background-color: var(--primary-color); color: #fff; padding: 2rem 1rem; display: flex; flex-direction: column; }
        .admin-main { flex: 1; padding: 2rem; background-color: #f4f6f8; }
        .admin-sidebar a { display: block; padding: 1rem; color: #ccc; border-radius: 8px; margin-bottom: 0.5rem; text-decoration: none; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background-color: rgba(255,255,255,0.1); color: #fff; }
        .admin-sidebar a i { margin-right: 10px; width: 20px; }
        .admin-logo { font-family: var(--font-heading); font-size: 1.5rem; text-align: center; margin-bottom: 3rem; color: #fff; }
        .admin-logo span { color: var(--secondary-color); }
        .settings-card { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 600px; margin-bottom: 2rem; }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Store Settings</h2>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">Settings updated successfully!</div>
        <?php endif; ?>

        <form action="settings.php" method="POST">
            <input type="hidden" name="action" value="update_keys">
            
            <!-- Email Notifications Section -->
            <div class="settings-card">
                <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">System Notifications</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.9rem;">The email address that will receive new order alerts and contact form submissions.</p>
                
                <div class="form-group">
                    <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Admin Notification Email</label>
                    <input type="email" name="admin_email" class="form-control" required value="<?php echo htmlspecialchars($admin_email); ?>" style="background-color: #f9f9f9; border-color: #ddd;">
                </div>
            </div>
            
            <!-- Taxes Section -->
            <div class="settings-card">
                <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">Tax Configuration</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.9rem;">Enable or disable tax calculation at checkout, and set your local tax rate.</p>
                
                <div class="form-group" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <input type="checkbox" name="tax_enabled" id="tax_enabled" value="1" <?= $tax_enabled ? 'checked' : '' ?> style="width: 20px; height: 20px; cursor: pointer;">
                    <label for="tax_enabled" style="font-weight: bold; cursor: pointer;">Enable Tax Collection</label>
                </div>
                
                <div class="form-group">
                    <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Tax Rate (%)</label>
                    <input type="number" step="0.01" min="0" name="tax_rate" class="form-control" value="<?php echo htmlspecialchars($tax_rate); ?>" style="background-color: #f9f9f9; border-color: #ddd;">
                </div>
            </div>

            <!-- Stripe Section -->
            <div class="settings-card">
                <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">Stripe Integration</h3>
                <p style="color: var(--text-light); margin-bottom: 2rem; font-size: 0.9rem;">Enter your Stripe API keys below to process payments. Make sure to use Test keys for development and Live keys for production.</p>
                
                <div class="form-group">
                    <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Stripe Publishable Key</label>
                    <input type="text" name="stripe_publishable_key" class="form-control" value="<?php echo htmlspecialchars($stripe_publishable); ?>" style="background-color: #f9f9f9; border-color: #ddd;">
                </div>
                
                <div class="form-group">
                    <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Stripe Secret Key</label>
                    <input type="password" name="stripe_secret_key" class="form-control" value="<?php echo htmlspecialchars($stripe_secret); ?>" style="background-color: #f9f9f9; border-color: #ddd;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-bottom: 2rem;">Save All Settings</button>
        </form>
    </div>
</div>

</body>
</html>
