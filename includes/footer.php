    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-section brand-section">
                <a href="<?= BASE_URL ?>index.php" class="brand-logo" style="margin-bottom: 1.5rem;">
                    <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Gloriolux - Luxury Soy Candles Calgary">
                    <span class="logo-text"><span class="logo-initial">G</span>LORIOLUX</span>
                </a>
                <p style="line-height: 1.8; opacity: 0.8; margin-top: 1rem;">Elevating everyday moments with artisanal, hand-poured luxury soy candles and premium gifting essentials in Calgary, AB.</p>
                <div class="social-links" style="margin-top: 2rem;">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>
            <div class="footer-section links-section">
                <h3 style="color: var(--secondary-color); font-size: 1rem; text-transform: uppercase; letter-spacing: 2px;">Shop Collection</h3>
                <ul>
                    <li><a href="<?= BASE_URL ?>shop?category=1">Luxury Candles</a></li>
                    <li><a href="<?= BASE_URL ?>shop?category=2">Gifting Sets</a></li>
                    <li><a href="<?= BASE_URL ?>blog">Our Journal</a></li>
                </ul>
            </div>
            <div class="footer-section links-section">
                <h3 style="color: var(--secondary-color); font-size: 1rem; text-transform: uppercase; letter-spacing: 2px;">Support</h3>
                <ul>
                    <li><a href="<?= BASE_URL ?>shipping">Shipping Info</a></li>
                    <li><a href="<?= BASE_URL ?>faq">FAQ & Help</a></li>
                    <li><a href="<?= BASE_URL ?>contact">Contact Us</a></li>
                    <li><a href="<?= BASE_URL ?>about">About Us</a></li>
                </ul>
            </div>
            <div class="footer-section links-section">
                <h3 style="color: var(--secondary-color); font-size: 1rem; text-transform: uppercase; letter-spacing: 2px;">Contact</h3>
                <ul style="font-size: 0.9rem; opacity: 0.8;">
                    <li style="margin-bottom: 0.8rem;"><i class="fas fa-phone-alt" style="margin-right: 10px; color: var(--secondary-color);"></i> <?= htmlspecialchars($contact_phone) ?></li>
                    <li style="margin-bottom: 0.8rem;"><i class="fas fa-envelope" style="margin-right: 10px; color: var(--secondary-color);"></i> <?= htmlspecialchars($contact_email) ?></li>
                    <li style="margin-bottom: 0.8rem;"><i class="fas fa-map-marker-alt" style="margin-right: 10px; color: var(--secondary-color);"></i> <?= htmlspecialchars($contact_address) ?></li>
                </ul>
            </div>
            <div class="footer-section newsletter-section" style="padding: 0;">
                <h3 style="color: var(--secondary-color); font-size: 1rem; text-transform: uppercase; letter-spacing: 2px;">Newsletter</h3>
                <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 1.5rem;">Join for exclusive launches and scenting tips.</p>
                <?php
                if (session_status() === PHP_SESSION_NONE) { session_start(); }
                if (isset($_SESSION['subscribe_msg'])): 
                    $msg_color = ($_SESSION['subscribe_status'] === 'success') ? '#d4edda' : '#f8d7da';
                    $text_color = ($_SESSION['subscribe_status'] === 'success') ? '#155724' : '#721c24';
                ?>
                    <div style="background:<?= $msg_color ?>; color:<?= $text_color ?>; padding:0.8rem; border-radius:5px; margin-bottom:1rem; font-size:0.85rem;">
                        <?= htmlspecialchars($_SESSION['subscribe_msg']) ?>
                    </div>
                <?php 
                    unset($_SESSION['subscribe_msg']);
                    unset($_SESSION['subscribe_status']);
                endif; 
                ?>
                <form action="<?= BASE_URL ?>subscribe" method="POST" class="newsletter-form">
                    <input type="email" name="email" placeholder="Email address" required style="font-size: 0.9rem;">
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p style="font-size: 0.8rem; opacity: 0.5;">&copy; <?php echo date('Y'); ?> Gloriolux Artisans. Serving Calgary, Airdrie, and surrounding Alberta regions. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>assets/js/main.js?v=<?php echo time(); ?>"></script>
    <?php 
    // Fetch custom footer scripts
    $stmt_f = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'custom_footer_scripts'");
    $f_scripts = $stmt_f->fetchColumn();
    if(!empty($f_scripts)) echo $f_scripts;
    ?>
</body>
</html>
