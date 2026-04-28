<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] === 'admin') {
            header('Location: /Gloriolux/admin/index.php');
        } else {
            header('Location: /Gloriolux/index.php');
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<main class="main-content">
    <section class="login-section" style="max-width:400px;margin:2rem auto;padding:2.5rem;background:rgba(255,255,255,0.9);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.1);backdrop-filter:blur(10px);">
        <h2 style="font-family:var(--font-heading);text-align:center;margin-bottom:1.5rem;color:var(--primary-color);">Welcome Back</h2>
        <?php if ($error): ?>
            <div style="background:#f8d7da;color:#721c24;padding:1rem;border-radius:8px;margin-bottom:1rem;text-align:center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['registered'])): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:8px;margin-bottom:1rem;text-align:center;">
                Registration successful! Please login.
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST" style="display:flex;flex-direction:column;gap:1.2rem;">
            <input type="email" name="email" placeholder="Email Address" required class="form-control" style="padding:1rem;border:1px solid rgba(0,0,0,0.1);border-radius:8px;background:rgba(255,255,255,0.5);">
            <input type="password" name="password" placeholder="Password" required class="form-control" style="padding:1rem;border:1px solid rgba(0,0,0,0.1);border-radius:8px;background:rgba(255,255,255,0.5);">
            <button type="submit" class="btn btn-primary" style="padding:1rem;background:var(--secondary-color);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:bold;letter-spacing:1px;margin-top:0.5rem;">Sign In</button>
        </form>
        <p style="margin-top:1.5rem;text-align:center;color:#666;">Don't have an account? <a href="register.php" style="color:var(--secondary-color);font-weight:600;">Create one</a></p>
    </section>
</main>
<?php
require_once 'includes/footer.php';
?>
