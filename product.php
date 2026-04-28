<?php
require_once 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: " . BASE_URL . "shop.php?error=not_found");
    exit;
}

// Check if hidden
if ($product['is_hidden'] == 1 && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    header("Location: " . BASE_URL . "shop.php?error=product_unavailable");
    exit;
}

// Fetch reviews
$rev_stmt = $pdo->prepare("SELECT r.*, u.name as reviewer_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$rev_stmt->execute([$id]);
$reviews = $rev_stmt->fetchAll();

// Dynamic SEO
$page_meta_title = htmlspecialchars($product['name']) . " | Gloriolux";
$page_meta_desc = strip_tags($product['description']);

require_once 'includes/header.php';
?>
<main class="main-content">
    <div class="container" style="padding: 5rem 0;">
        <div style="display: flex; flex-wrap: wrap; gap: 4rem; align-items: flex-start;">
            
            <!-- Product Image Gallery (Left Side) -->
            <div style="flex: 1; min-width: 300px; background: #f8f9fa; border-radius: 16px; padding: 2rem; display: flex; justify-content: center; align-items: center;">
                <?php if($product['image_url']): ?>
                    <img src="<?= BASE_URL ?><?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 100%; max-width: 500px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <?php else: ?>
                    <div style="width: 100%; height: 400px; background: #ddd; border-radius: 8px;"></div>
                <?php endif; ?>
            </div>

            <!-- Product Details (Right Side) -->
            <div style="flex: 1; min-width: 300px;">
                <p style="color: var(--secondary-color); text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem; margin-bottom: 0.5rem; font-weight: bold;">
                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                </p>
                <h1 style="font-family: var(--font-heading); font-size: 3rem; margin-bottom: 1rem; color: var(--primary-color);">
                    <?= htmlspecialchars($product['name']) ?>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>admin/products.php" class="btn btn-outline" style="font-size: 0.9rem; padding: 0.4rem 0.8rem; vertical-align: middle; margin-left: 1rem; border-color: var(--secondary-color); color: var(--secondary-color);"><i class="fas fa-edit"></i> Edit in CMS</a>
                    <?php endif; ?>
                </h1>
                <p style="font-size: 1.5rem; color: var(--text-color); margin-bottom: 1.5rem; font-weight: 300;">
                    $<?= number_format($product['price'], 2) ?>
                </p>
                
                <div style="font-size: 1.1rem; line-height: 1.7; color: var(--text-light); margin-bottom: 2.5rem;">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>

                <form action="<?= BASE_URL ?>cart_add.php" method="POST" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 2.5rem; padding-bottom: 2.5rem; border-bottom: 1px solid #eee;">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div style="display: flex; border: 1px solid #ddd; border-radius: 30px; overflow: hidden; background: #fff;">
                        <button type="button" onclick="document.getElementById('qty').stepDown()" style="background: none; border: none; padding: 0.8rem 1.2rem; cursor: pointer; color: var(--text-color);"><i class="fas fa-minus"></i></button>
                        <input type="number" name="quantity" id="qty" value="1" min="1" max="<?= $product['stock'] > 0 ? $product['stock'] : 10 ?>" style="width: 50px; text-align: center; border: none; font-size: 1rem; -moz-appearance: textfield; pointer-events: none;">
                        <button type="button" onclick="document.getElementById('qty').stepUp()" style="background: none; border: none; padding: 0.8rem 1.2rem; cursor: pointer; color: var(--text-color);"><i class="fas fa-plus"></i></button>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <button type="submit" class="btn btn-primary" style="flex: 1; padding: 1rem; border-radius: 30px; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">Add to Cart</button>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline" style="flex: 1; padding: 1rem; border-radius: 30px; font-size: 1.1rem; cursor: not-allowed;" disabled>Out of Stock</button>
                    <?php endif; ?>
                </form>

                <!-- Product Features / Guarantees -->
                <ul style="list-style: none; padding: 0; color: var(--text-color); font-size: 0.95rem; line-height: 2;">
                    <li><i class="fas fa-check" style="color: var(--secondary-color); margin-right: 10px;"></i> Hand-poured with 100% natural soy wax</li>
                    <li><i class="fas fa-check" style="color: var(--secondary-color); margin-right: 10px;"></i> Eco-friendly and cruelty-free ingredients</li>
                    <li><i class="fas fa-check" style="color: var(--secondary-color); margin-right: 10px;"></i> Burn time: Approximately 60-80 hours</li>
                </ul>

                <!-- Share Product -->
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                    <p style="font-weight: bold; margin-bottom: 1rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-light);">Share this Product</p>
                    <div style="display: flex; gap: 1rem;">
                        <?php 
                        $current_url = urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                        $share_title = urlencode("Check out " . $product['name'] . " from Gloriolux");
                        ?>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $current_url ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; border-radius: 50%; background: #f8f9fa; color: #3b5998; text-decoration: none; transition: all 0.3s; border: 1px solid #ddd;"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?= $current_url ?>&text=<?= $share_title ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; border-radius: 50%; background: #f8f9fa; color: #1da1f2; text-decoration: none; transition: all 0.3s; border: 1px solid #ddd;"><i class="fab fa-twitter"></i></a>
                        <a href="https://pinterest.com/pin/create/button/?url=<?= $current_url ?>&description=<?= $share_title ?>&media=<?= urlencode(BASE_URL_FULL . $product['image_url']) ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; border-radius: 50%; background: #f8f9fa; color: #bd081c; text-decoration: none; transition: all 0.3s; border: 1px solid #ddd;"><i class="fab fa-pinterest-p"></i></a>
                        <a href="https://instagram.com" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; border-radius: 50%; background: #f8f9fa; color: #c13584; text-decoration: none; transition: all 0.3s; border: 1px solid #ddd;"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div style="margin-top: 5rem; padding-top: 3rem; border-top: 1px solid #eee;">
            <h2 style="font-family: var(--font-heading); margin-bottom: 2rem; text-align: center;">Customer Reviews</h2>
            
            <?php if(count($reviews) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">
                    <?php foreach($reviews as $review): ?>
                        <div style="background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                            <div style="color: #f1c40f; margin-bottom: 1rem; font-size: 1.2rem;">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa<?= $i <= $review['rating'] ? 's' : 'r' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p style="font-style: italic; margin-bottom: 1.5rem; color: var(--text-light);">"<?= htmlspecialchars($review['comment']) ?>"</p>
                            <p style="font-weight: bold; color: var(--primary-color); font-size: 0.9rem;">— <?= htmlspecialchars($review['reviewer_name']) ?></p>
                            <p style="font-size: 0.8rem; color: #999; margin-top: 0.5rem;"><?= date('F j, Y', strtotime($review['created_at'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-light); font-size: 1.1rem;">There are no reviews for this product yet. Be the first to share your experience!</p>
            <?php endif; ?>
        </div>
        
        <!-- Leave a Review Form -->
        <div style="margin-top: 4rem; max-width: 600px; margin-left: auto; margin-right: auto;">
            <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem; text-align: center;">Leave a Review</h3>
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if(isset($_GET['review']) && $_GET['review'] == 'success'): ?>
                    <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; text-align: center;">Thank you for your review!</div>
                <?php endif; ?>
                <form action="<?= BASE_URL ?>submit_review.php" method="POST" style="background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Rating</label>
                        <select name="rating" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="5">5 Stars - Excellent</option>
                            <option value="4">4 Stars - Very Good</option>
                            <option value="3">3 Stars - Good</option>
                            <option value="2">2 Stars - Fair</option>
                            <option value="1">1 Star - Poor</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Your Review</label>
                        <textarea name="comment" rows="4" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px; resize: vertical;" placeholder="Tell us what you think..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Review</button>
                </form>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 8px;">
                    <p style="margin-bottom: 1rem; color: #666;">You must be logged in to leave a review.</p>
                    <a href="<?= BASE_URL ?>login.php" class="btn btn-outline" style="color: var(--primary-color); border-color: var(--primary-color);">Login to Review</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
