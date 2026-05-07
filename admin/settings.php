<?php
session_start();
// Verify admin role
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '../') . "login.php"); 
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

    // Update Contact Info
    $contact_email = $_POST['contact_email'];
    $contact_phone = $_POST['contact_phone'];
    $contact_address = $_POST['contact_address'];
    
    $stmt6 = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('contact_email', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt6->execute([$contact_email, $contact_email]);
    $stmt7 = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('contact_phone', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt7->execute([$contact_phone, $contact_phone]);
    $stmt8 = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('contact_address', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt8->execute([$contact_address, $contact_address]);

    // Update SEO Scripts
    $gtm_header = $_POST['gtm_header_code'];
    $gtm_body = $_POST['gtm_body_code'];
    $ga_id = $_POST['ga_tracking_id'];
    $footer_scripts = $_POST['custom_footer_scripts'];
    $google_client_id = $_POST['google_client_id'];

    $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('gtm_header_code', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$gtm_header, $gtm_header]);
    $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('gtm_body_code', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$gtm_body, $gtm_body]);
    $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('ga_tracking_id', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$ga_id, $ga_id]);
    $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('custom_footer_scripts', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$footer_scripts, $footer_scripts]);
    $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('google_client_id', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$google_client_id, $google_client_id]);

    header("Location: settings.php?success=1");
    exit;
}

// Fetch current keys
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('stripe_secret_key', 'stripe_publishable_key', 'tax_enabled', 'tax_rate', 'admin_email', 'contact_email', 'contact_phone', 'contact_address', 'gtm_header_code', 'gtm_body_code', 'ga_tracking_id', 'custom_footer_scripts', 'google_client_id')");
$settings_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stripe_secret = $settings_data['stripe_secret_key'] ?? '';
$stripe_publishable = $settings_data['stripe_publishable_key'] ?? '';
$tax_enabled = ($settings_data['tax_enabled'] ?? '0') === '1';
$tax_rate = $settings_data['tax_rate'] ?? '5.00';
$admin_email = $settings_data['admin_email'] ?? 'admin@gloriolux.com';
$contact_email = $settings_data['contact_email'] ?? 'info@gloriolux.ca';
$contact_phone = $settings_data['contact_phone'] ?? '+1 (647) 555-0123';
$contact_address = $settings_data['contact_address'] ?? 'Toronto, Ontario, Canada';

$gtm_header = $settings_data['gtm_header_code'] ?? '';
$gtm_body = $settings_data['gtm_body_code'] ?? '';
$ga_id = $settings_data['ga_tracking_id'] ?? '';
$footer_scripts = $settings_data['custom_footer_scripts'] ?? '';
$google_client_id = $settings_data['google_client_id'] ?? '';

$admin_page_title = 'Store Settings';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Store Settings</h2>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Settings updated successfully!</div>
<?php endif; ?>

<form action="settings.php" method="POST">
    <input type="hidden" name="action" value="update_keys">
    
    <!-- Email Notifications Section -->
    <div class="settings-card">
        <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">System Notifications</h3>
        <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.9rem;">The email address that will receive new order alerts and contact form submissions.</p>
        
        <div class="form-group">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Admin Notification Email</label>
            <input type="email" name="admin_email" class="form-control" required value="<?php echo htmlspecialchars($admin_email); ?>">
        </div>
    </div>

    <!-- Public Contact Information Section -->
    <div class="settings-card">
        <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">Public Contact Information</h3>
        <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.9rem;">This information will be displayed on the public Contact Us page.</p>
        
        <div class="form-group">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Contact Email</label>
            <input type="email" name="contact_email" class="form-control" required value="<?php echo htmlspecialchars($contact_email); ?>">
        </div>

        <div class="form-group" style="margin-top: 1.5rem;">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Contact Phone</label>
            <input type="text" name="contact_phone" class="form-control" required value="<?php echo htmlspecialchars($contact_phone); ?>">
        </div>

        <div class="form-group" style="margin-top: 1.5rem;">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Physical Address / Location</label>
            <input type="text" name="contact_address" class="form-control" required value="<?php echo htmlspecialchars($contact_address); ?>">
        </div>
    </div>

    <!-- Tracking & SEO Scripts Section -->
    <div class="settings-card">
        <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">Tracking & SEO Scripts</h3>
        <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.9rem;">Paste your tracking codes (Google Tag Manager, Analytics, Pixel) here. These will be injected into all pages.</p>
        
        <div class="form-group">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Google Tag Manager (Header) - &lt;head&gt;</label>
            <textarea name="gtm_header_code" class="form-control" rows="4" style="font-family: monospace; font-size: 0.8rem;"><?php echo htmlspecialchars($gtm_header); ?></textarea>
        </div>

        <div class="form-group" style="margin-top: 1.5rem;">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Google Tag Manager (Body) - &lt;body&gt; start</label>
            <textarea name="gtm_body_code" class="form-control" rows="4" style="font-family: monospace; font-size: 0.8rem;"><?php echo htmlspecialchars($gtm_body); ?></textarea>
        </div>

        <div class="form-group" style="margin-top: 1.5rem;">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Google Analytics ID (e.g. G-XXXXXXX)</label>
            <input type="text" name="ga_tracking_id" class="form-control" placeholder="G-XXXXXXXXXX" value="<?php echo htmlspecialchars($ga_id); ?>">
        </div>

        <div class="form-group" style="margin-top: 1.5rem;">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Custom Footer Scripts (Before &lt;/body&gt;)</label>
            <textarea name="custom_footer_scripts" class="form-control" rows="4" style="font-family: monospace; font-size: 0.8rem;"><?php echo htmlspecialchars($footer_scripts); ?></textarea>
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
            <input type="number" step="0.01" min="0" name="tax_rate" class="form-control" value="<?php echo htmlspecialchars($tax_rate); ?>">
        </div>
    </div>

    <!-- Stripe Section -->
    <div class="settings-card">
        <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">Stripe Integration</h3>
        <p style="color: var(--text-light); margin-bottom: 2rem; font-size: 0.9rem;">Enter your Stripe API keys below to process payments. Make sure to use Test keys for development and Live keys for production.</p>
        
        <div class="form-group">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Stripe Publishable Key</label>
            <input type="text" name="stripe_publishable_key" class="form-control" value="<?php echo htmlspecialchars($stripe_publishable); ?>">
        </div>
        
        <div class="form-group">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Stripe Secret Key</label>
            <input type="password" name="stripe_secret_key" class="form-control" value="<?php echo htmlspecialchars($stripe_secret); ?>">
        </div>
    </div>

    <!-- Google Login Section -->
    <div class="settings-card">
        <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">Google Authentication</h3>
        <p style="color: var(--text-light); margin-bottom: 2rem; font-size: 0.9rem;">Enable "Sign in with Google" by providing your Google OAuth Client ID from the Google Cloud Console.</p>
        
        <div class="form-group">
            <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Google Client ID</label>
            <input type="text" name="google_client_id" class="form-control" placeholder="xxxxxxxxxxxx-xxxxxxxxxxxxxxxx.apps.googleusercontent.com" value="<?php echo htmlspecialchars($google_client_id); ?>">
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary" style="margin-bottom: 2rem;">Save All Settings</button>
</form>

<?php require_once 'includes/admin_footer.php'; ?>
