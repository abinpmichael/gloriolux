<?php require_once 'includes/header.php'; ?>
<?php
// Fetch featured products
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_hidden = 0 ORDER BY p.id DESC LIMIT 4");
$featured_products = $stmt->fetchAll();

// Fetch slides
$stmt_slides = $pdo->query("SELECT * FROM slides ORDER BY display_order ASC");
$slides = $stmt_slides->fetchAll();
?>

    <!-- Hero Slider -->
    <section class="hero-slider" style="position: relative; width: 100%; height: 100vh; overflow: hidden; background: #000;">
        <?php foreach($slides as $index => $slide): ?>
            <div class="slide" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: <?= $index === 0 ? '1' : '0' ?>; transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1); z-index: <?= $index === 0 ? '1' : '0' ?>;" id="slide-<?= $index ?>">
                <img src="<?= BASE_URL ?><?= htmlspecialchars($slide['image_url']) ?>" alt="Gloriolux - <?= htmlspecialchars($slide['title']) ?>" width="1920" height="1080" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.7;" <?= $index === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' ?>>
                <div class="container" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; text-align: center; color: #fff; z-index: 2;">
                    <span class="section-subtitle fade-up" style="color: var(--secondary-color); text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">Handcrafted Excellence</span>
                    <<?= $index === 0 ? 'h1' : 'h2' ?> style="font-size: clamp(2.5rem, 8vw, 4.5rem); margin-bottom: 1.5rem; font-family: var(--font-heading); text-shadow: 0 4px 15px rgba(0,0,0,0.4); font-weight: 700;"><?= htmlspecialchars($slide['title']) ?></<?= $index === 0 ? 'h1' : 'h2' ?>>
                    <p style="font-size: 1.25rem; margin-bottom: 2.5rem; max-width: 650px; margin-left: auto; margin-right: auto; text-shadow: 0 2px 5px rgba(0,0,0,0.4); font-weight: 300; line-height: 1.6;"><?= htmlspecialchars($slide['subtitle']) ?></p>
                    <?php if(!empty($slide['button_text']) && !empty($slide['button_url'])): ?>
                        <a href="<?= htmlspecialchars($slide['button_url']) ?>" class="btn btn-primary" style="padding: 1.2rem 3rem; box-shadow: 0 10px 25px rgba(197, 160, 89, 0.4);"><?= htmlspecialchars($slide['button_text']) ?></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(count($slides) > 1): ?>
            <button onclick="nextSlide()" style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.2); color: #fff; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; z-index: 10; font-size: 1.2rem; transition: all 0.3s;"><i class="fas fa-chevron-right"></i></button>
            <button onclick="prevSlide()" style="position: absolute; left: 30px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.2); color: #fff; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; z-index: 10; font-size: 1.2rem; transition: all 0.3s;"><i class="fas fa-chevron-left"></i></button>
            
        <?php endif; ?>
    </section>

    <!-- Trust / Benefits Section -->
    <section class="benefits-section">
        <div class="container">
            <div class="benefits-grid">
                <div class="benefit-item fade-up">
                    <i class="fas fa-shipping-fast"></i>
                    <h4>Global Shipping</h4>
                    <p>Premium tracked delivery to your doorstep.</p>
                </div>
                <div class="benefit-item fade-up delay-100">
                    <i class="fas fa-leaf"></i>
                    <h4>100% Soy Wax</h4>
                    <p>Eco-friendly, sustainable, and clean burning.</p>
                </div>
                <div class="benefit-item fade-up delay-200">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Secure Checkout</h4>
                    <p>SSL encrypted payments with Stripe.</p>
                </div>
                <div class="benefit-item fade-up delay-300">
                    <i class="fas fa-gift"></i>
                    <h4>Luxury Packaging</h4>
                    <p>Exquisite gift boxes with every order.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Signature Collection -->
    <section class="section bg-light">
        <div class="container">
            <span class="section-subtitle">Exquisite Selection</span>
            <h2 class="section-title fade-up">Signature Collection</h2>
            <div class="product-grid">
                <?php foreach($featured_products as $product): ?>
                    <div class="product-card fade-up">
                        <a href="<?= BASE_URL ?>product?id=<?= $product['id']; ?>">
                            <div class="product-img-wrap">
                                <?php if($product['price'] < 50): ?>
                                    <span class="badge badge-sale">Best Seller</span>
                                <?php endif; ?>
                                <img src="<?= BASE_URL ?><?= htmlspecialchars($product['image_url']); ?>" alt="Gloriolux <?= htmlspecialchars($product['name']); ?> Luxury Soy Candle Hand-Poured in Calgary" width="400" height="400" class="product-img" loading="lazy" fetchpriority="low">
                                <div class="product-overlay">
                                    <form action="<?= BASE_URL ?>cart_add" method="POST" style="width:100%;">
                                        <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary add-to-cart-btn">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                            <div class="product-info">
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-light); letter-spacing: 1px;"><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
                                <h3 class="product-title"><?= htmlspecialchars($product['name']); ?></h3>
                                <div class="product-price"><?= formatPrice($product['price']); ?></div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 4rem;">
                <a href="<?= BASE_URL ?>shop" class="btn btn-outline" style="color: var(--primary-color); border-color: var(--primary-color);">Explore All Products</a>
            </div>
        </div>
    </section>

    <!-- Call to Action / Featured Promo -->
    <section class="section" style="background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('<?= BASE_URL ?>assets/img/gifting.png'); background-size: cover; background-position: center; background-attachment: fixed; color: #fff; padding: 10rem 0;">
        <div class="container" style="text-align: center; max-width: 800px;">
            <span class="section-subtitle" style="color: var(--secondary-color);">Perfect for Gifting</span>
            <h2 style="font-size: clamp(2rem, 5vw, 3.5rem); margin-bottom: 1.5rem; font-family: var(--font-heading);">Crafting Moments, One Scent at a Time</h2>
            <p style="font-size: 1.2rem; color: #ddd; margin-bottom: 3rem; line-height: 1.8; font-weight: 300;">Elevate your space with our curated luxury gifting sets. Designed for those who appreciate the finer things in life.</p>
            <a href="<?= BASE_URL ?>shop?category=2" class="btn btn-primary" style="padding: 1.2rem 3.5rem;">Shop Gifting Sets</a>
        </div>
    </section>


<?php require_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        if (slides.length > 1) {
            function showSlide(index) {
                slides.forEach((s, i) => {
                    s.style.opacity = (i === index) ? '1' : '0';
                    s.style.zIndex = (i === index) ? '1' : '0';
                });
            }
            window.nextSlide = function() {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }
            window.prevSlide = function() {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            }
            setInterval(nextSlide, 7000);
        }
    });
</script>
