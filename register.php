<?php
session_start();
require_once 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['password'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Email address is already registered.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        if ($stmt->execute([$name, $email, $hashed_password])) {
            header('Location: login.php?registered=1');
            exit;
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}

require_once 'includes/header.php';
?>
<main class="main-content">
    <section class="register-section" style="max-width:450px;margin:2rem auto;padding:2.5rem;background:rgba(255,255,255,0.9);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.1);backdrop-filter:blur(10px);">
        <h2 style="font-family:var(--font-heading);text-align:center;margin-bottom:1.5rem;color:var(--primary-color);">Create an Account</h2>
        <?php if ($error): ?>
            <div style="background:#f8d7da;color:#721c24;padding:1rem;border-radius:8px;margin-bottom:1rem;text-align:center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form action="register" method="POST" style="display:flex;flex-direction:column;gap:1.2rem;">
            <input type="text" name="name" placeholder="Full Name" required class="form-control" style="padding:1rem;border:1px solid rgba(0,0,0,0.1);border-radius:8px;background:rgba(255,255,255,0.5);" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
            <input type="email" name="email" placeholder="Email Address" required class="form-control" style="padding:1rem;border:1px solid rgba(0,0,0,0.1);border-radius:8px;background:rgba(255,255,255,0.5);" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            <input type="password" name="password" placeholder="Password" required class="form-control" style="padding:1rem;border:1px solid rgba(0,0,0,0.1);border-radius:8px;background:rgba(255,255,255,0.5);">
            <button type="submit" class="btn btn-primary" style="padding:1rem;background:var(--secondary-color);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:bold;letter-spacing:1px;margin-top:0.5rem;">Register</button>
        </form>
        <p style="margin-top:1.5rem;text-align:center;color:#666;">Already have an account? <a href="login.php" style="color:var(--secondary-color);font-weight:600;">Sign in here</a></p>
    </section>
</main>
<?php
require_once 'includes/footer.php';
?>
