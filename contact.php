<?php
require_once 'includes/header.php';

// Fetch contact settings
$stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('contact_email', 'contact_phone', 'contact_address')");
$settings = [];
foreach ($stmt_settings->fetchAll() as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$c_email = $settings['contact_email'] ?? 'info@gloriolux.ca';
$c_phone = $settings['contact_phone'] ?? '+1 (647) 555-0123';
$c_address = $settings['contact_address'] ?? 'Toronto, Ontario, Canada';

// Simple contact form handling (placeholder)
$sent = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['subject'], $_POST['message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } else {
        // In a real app, you would send an email or store the inquiry.
        // Here we just simulate success.
        $sent = true;
        // Insert contact message into database
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $email, $subject, $message]);
        
        // Send Email Notification to Admin
        $set_stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'admin_email'");
        $admin_email_setting = $set_stmt->fetchColumn();
        $admin_email = $admin_email_setting ? $admin_email_setting : "admin@gloriolux.com";
        
        $email_subject = "New Contact Message: " . $subject;
        $email_body = "You have received a new contact message.\n\nFrom: $name ($email)\nSubject: $subject\nMessage:\n$message";
        $headers = "From: noreply@gloriolux.com";
        
        @mail($admin_email, $email_subject, $email_body, $headers);
    }
}
?>
<div class="contact-hero" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/img/hero-bg.jpg'); background-size: cover; background-position: center; padding: 100px 0; text-align: center; color: #fff; margin-bottom: 4rem;">
    <h1 style="font-family: var(--font-heading); font-size: 3.5rem; margin-bottom: 1rem;">Get In Touch</h1>
    <p style="font-size: 1.2rem; max-width: 600px; margin: auto; opacity: 0.9;">We're here to help you find your perfect scent or answer any questions about our luxury candles.</p>
</div>

<div class="container" style="max-width: 1200px; margin-bottom: 6rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 4rem;">
        
        <!-- Contact Info Column -->
        <div class="contact-info-card" style="background: #fff; padding: 3rem; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;">
            <h2 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 2rem; color: var(--primary-color);">Contact Details</h2>
            <p style="color: #666; line-height: 1.8; margin-bottom: 3rem; font-size: 1.1rem;">Whether you're looking for wholesale opportunities, custom orders, or just want to say hello, we'd love to hear from you.</p>
            
            <div style="display: flex; flex-direction: column; gap: 2.5rem;">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: var(--secondary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 1.2rem; font-family: var(--font-heading);">Email Us</h4>
                        <a href="mailto:<?= htmlspecialchars($c_email) ?>" style="color: var(--secondary-color); text-decoration: none; font-size: 1.1rem; font-weight: 500;"><?= htmlspecialchars($c_email) ?></a>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: var(--secondary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 1.2rem; font-family: var(--font-heading);">Call Us</h4>
                        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $c_phone) ?>" style="color: var(--secondary-color); text-decoration: none; font-size: 1.1rem; font-weight: 500;"><?= htmlspecialchars($c_phone) ?></a>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: var(--secondary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fas fa-location-dot"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 1.2rem; font-family: var(--font-heading);">Visit Us</h4>
                        <p style="margin: 0; color: #666; font-size: 1.1rem;"><?= htmlspecialchars($c_address) ?></p>
                    </div>
                </div>
            </div>

            <div style="margin-top: 4rem; padding-top: 3rem; border-top: 1px solid #f0f0f0;">
                <h4 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Follow Us</h4>
                <div style="display: flex; gap: 1.5rem; font-size: 1.5rem;">
                    <a href="#" style="color: #666; transition: color 0.3s;" onmouseover="this.style.color='var(--secondary-color)'" onmouseout="this.style.color='#666'"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: #666; transition: color 0.3s;" onmouseover="this.style.color='var(--secondary-color)'" onmouseout="this.style.color='#666'"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: #666; transition: color 0.3s;" onmouseover="this.style.color='var(--secondary-color)'" onmouseout="this.style.color='#666'"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
        </div>

        <!-- Contact Form Column -->
        <div style="background: #fff; padding: 3rem; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;">
            <h2 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 2rem;">Send Message</h2>
            
            <?php if ($sent): ?>
                <div style="background: #d4edda; color: #155724; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; border-left: 5px solid #28a745;">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                    <span style="font-weight: 500;">Your message has been sent successfully. We'll be in touch soon!</span>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; border-left: 5px solid #dc3545;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form action="contact.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #444;">Name</label>
                        <input type="text" name="name" required class="form-control" style="width: 100%; padding: 1rem; border: 1px solid #ddd; border-radius: 10px; background: #fafafa; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--secondary-color)'" onblur="this.style.borderColor='#ddd'" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #444;">Email</label>
                        <input type="email" name="email" required class="form-control" style="width: 100%; padding: 1rem; border: 1px solid #ddd; border-radius: 10px; background: #fafafa; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--secondary-color)'" onblur="this.style.borderColor='#ddd'" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #444;">Subject</label>
                        <input type="text" name="subject" required class="form-control" style="width: 100%; padding: 1rem; border: 1px solid #ddd; border-radius: 10px; background: #fafafa; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--secondary-color)'" onblur="this.style.borderColor='#ddd'" value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #444;">Message</label>
                        <textarea name="message" required class="form-control" rows="6" style="width: 100%; padding: 1rem; border: 1px solid #ddd; border-radius: 10px; background: #fafafa; transition: border-color 0.3s; resize: none;" onfocus="this.style.borderColor='var(--secondary-color)'" onblur="this.style.borderColor='#ddd'"><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 1.2rem; background: var(--secondary-color); color: #fff; border: none; border-radius: 30px; cursor: pointer; font-size: 1.1rem; font-weight: 600; margin-top: 1rem; box-shadow: 0 10px 20px rgba(197, 160, 89, 0.3); transition: transform 0.3s, background 0.3s;" onmouseover="this.style.background='#a67c00'; this.style.transform='translateY(-3px)'" onmouseout="this.style.background='var(--secondary-color)'; this.style.transform='translateY(0)'">Send Message</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
require_once 'includes/footer.php';
?>
