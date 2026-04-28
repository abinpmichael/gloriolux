<?php
require_once 'includes/header.php';

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
<main class="main-content">
    <section class="contact-section" style="max-width:800px;margin:auto;padding:2rem;background:rgba(255,255,255,0.9);border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
        <h1 style="font-family:var(--font-heading);text-align:center;margin-bottom:1.5rem;">Contact Us</h1>
        <?php if ($sent): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;">
                Thank you for reaching out! We'll get back to you shortly.
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div style="background:#f8d7da;color:#721c24;padding:1rem;border-radius:5px;margin-bottom:1rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form action="contact.php" method="POST" style="display:flex;flex-direction:column;gap:1rem;">
                <input type="text" name="name" placeholder="Your Name" required class="form-control" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                <input type="email" name="email" placeholder="Your Email" required class="form-control" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                <input type="text" name="subject" placeholder="Subject" required class="form-control" value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>">
                <textarea name="message" placeholder="Your Message" required class="form-control" rows="5"><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                <button type="submit" class="btn btn-primary" style="padding:0.75rem;background:var(--secondary-color);color:#fff;border:none;border-radius:6px;cursor:pointer;">Send Message</button>
            </form>
        <?php endif; ?>
    </section>
</main>
<?php
require_once 'includes/footer.php';
?>
