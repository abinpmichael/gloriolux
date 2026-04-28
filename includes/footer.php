    

    <footer class="footer">
        <div class="container footer-content" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
            <div class="footer-section brand-section">
                <a href="<?= BASE_URL ?>index.php" class="brand-logo" style="margin-bottom: 1rem;">
                    <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Gloriolux">
                    <span class="logo-text">Glorio<span class="logo-highlight">lux</span></span>
                </a>
                <p>Elevating everyday moments with artisanal, hand-poured luxury soy candles and premium gifting essentials.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>
            <div class="footer-section links-section">
                <h3>Shop</h3>
                <ul>
                    <li><a href="<?= BASE_URL ?>shop.php?category=candles">Candles</a></li>
                    <li><a href="<?= BASE_URL ?>shop.php?category=gifting">Gifting Sets</a></li>
                    <li><a href="<?= BASE_URL ?>shop.php?category=accessories">Accessories</a></li>
                </ul>
            </div>
            <div class="footer-section links-section">
                <h3>Support</h3>
                <ul>
                    <li><a href="<?= BASE_URL ?>contact.php">Contact Us</a></li>
                    <li><a href="<?= BASE_URL ?>faq.php">FAQ</a></li>
                    <li><a href="<?= BASE_URL ?>shipping.php">Shipping & Returns</a></li>
                </ul>
            </div>
            <div class="footer-section newsletter-section">
                <h3>Newsletter</h3>
                <p>Subscribe for exclusive offers and new arrivals.</p>
                <?php
                if (session_status() === PHP_SESSION_NONE) { session_start(); }
                if (isset($_SESSION['subscribe_msg'])): 
                    $msg_color = ($_SESSION['subscribe_status'] === 'success') ? '#d4edda' : '#f8d7da';
                    $text_color = ($_SESSION['subscribe_status'] === 'success') ? '#155724' : '#721c24';
                ?>
                    <div style="background:<?= $msg_color ?>; color:<?= $text_color ?>; padding:0.8rem; border-radius:5px; margin-bottom:1rem; font-size:0.9rem;">
                        <?= htmlspecialchars($_SESSION['subscribe_msg']) ?>
                    </div>
                <?php 
                    unset($_SESSION['subscribe_msg']);
                    unset($_SESSION['subscribe_status']);
                endif; 
                ?>
                <form action="<?= BASE_URL ?>subscribe.php" method="POST" class="newsletter-form">
                    <input type="email" name="email" placeholder="Your Email Address" required>
                    <button type="submit"><i class="fas fa-arrow-right"></i></button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Gloriolux. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
