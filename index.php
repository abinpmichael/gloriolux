<?php require_once 'includes/header.php'; ?>
<?php
// Fetch featured products
$stmt = $pdo->query("SELECT * FROM products WHERE is_hidden = 0 ORDER BY id DESC LIMIT 4");
$featured_products = $stmt->fetchAll();
?>
<?php
// Fetch slides
$stmt_slides = $pdo->query("SELECT * FROM slides ORDER BY display_order ASC");
$slides = $stmt_slides->fetchAll();
?>
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-slider" style="position: relative; width: 100%; height: 100vh; overflow: hidden; background: #000;">
            <?php foreach($slides as $index => $slide): ?>
                <div class="slide" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: <?= $index === 0 ? '1' : '0' ?>; transition: opacity 1s ease-in-out; z-index: <?= $index === 0 ? '1' : '0' ?>;" id="slide-<?= $index ?>">
                    <img src="/Gloriolux/<?= htmlspecialchars($slide['image_url']) ?>" alt="Slide" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.6;">
                    <div class="container" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; text-align: center; color: #fff; z-index: 2;">
                        <h1 style="font-size: 4rem; margin-bottom: 1rem; font-family: var(--font-heading); text-shadow: 2px 2px 8px rgba(0,0,0,0.6);"><?= htmlspecialchars($slide['title']) ?></h1>
                        <p style="font-size: 1.2rem; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; text-shadow: 1px 1px 4px rgba(0,0,0,0.6);"><?= htmlspecialchars($slide['subtitle']) ?></p>
                        <?php if(!empty($slide['button_text']) && !empty($slide['button_url'])): ?>
                            <a href="<?= htmlspecialchars($slide['button_url']) ?>" class="btn btn-primary" style="padding: 1rem 2.5rem; border-radius: 30px;"><?= htmlspecialchars($slide['button_text']) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if(count($slides) > 1): ?>
                <button onclick="nextSlide()" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); border: none; color: #fff; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; z-index: 10; font-size: 1.5rem;"><i class="fas fa-chevron-right"></i></button>
                <button onclick="prevSlide()" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); border: none; color: #fff; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; z-index: 10; font-size: 1.5rem;"><i class="fas fa-chevron-left"></i></button>
                
                <script>
                    let currentSlide = 0;
                    const slides = document.querySelectorAll('.slide');
                    function showSlide(index) {
                        slides.forEach((s, i) => {
                            s.style.opacity = (i === index) ? '1' : '0';
                            s.style.zIndex = (i === index) ? '1' : '0';
                        });
                    }
                    function nextSlide() {
                        currentSlide = (currentSlide + 1) % slides.length;
                        showSlide(currentSlide);
                    }
                    function prevSlide() {
                        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                        showSlide(currentSlide);
                    }
                    setInterval(nextSlide, 6000); // Auto-advance every 6 seconds
                </script>
            <?php endif; ?>
        </section>
        <!-- Featured Collection Section -->
        <section class="section bg-light">
            <div class="container">
                <h2 class="section-title fade-up">Signature Collection</h2>
                <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 2.5rem;">
                    <?php foreach($featured_products as $product): ?>
                        <div class="product-card fade-up">
                            <a href="/Gloriolux/product.php?id=<?php echo $product['id']; ?>">
                                <div class="product-img-wrap">
                                    <img src="/Gloriolux/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img">
                                    <div class="product-overlay">
                                        <form action="/Gloriolux/cart_add.php" method="POST">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-primary add-to-cart-btn" style="padding: 0.5rem 1.5rem;">Add to Cart</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="text-align: center; margin-top: 3rem;">
                    <a href="/Gloriolux/shop.php" class="btn btn-outline" style="color: var(--primary-color); border-color: var(--primary-color);">View All Products</a>
                </div>
            </div>
        </section>
        <!-- About / Concept Section -->
        <section class="section" style="background-color: var(--primary-color); color: #fff;">
            <div class="container" style="display: flex; flex-wrap: wrap; align-items: center; gap: 4rem;">
                <div class="fade-up" style="flex: 1; min-width: 300px;">
                    <img src="/Gloriolux/assets/img/gifting.png" alt="Gifting" style="width: 100%; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
                </div>
                <div class="fade-up delay-200" style="flex: 1; min-width: 300px;">
                    <h2 style="font-family: var(--font-heading); font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--secondary-color);">The Art of Gifting</h2>
                    <p style="font-size: 1.1rem; margin-bottom: 1.5rem; font-weight: 300; color: #ccc;">Discover our curated selection of luxury gifting sets. Perfect for any occasion, our sets are beautifully packaged and ready to delight your loved ones.</p>
                    <p style="font-size: 1.1rem; margin-bottom: 2.5rem; font-weight: 300; color: #ccc;">Each set is thoughtfully composed to create an unforgettable olfactory experience.</p>
                    <a href="/Gloriolux/shop.php?category=2" class="btn btn-primary">Explore Gifts</a>
                </div>
            </div>
        </section>
    </main>
    <?php require_once 'includes/footer.php'; ?>
