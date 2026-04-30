<?php
require_once 'includes/header.php';
?>
<main class="main-content">
<?php
// Fetch all products or filter by category
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

if ($category_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND is_hidden = 0 ORDER BY id DESC");
    $stmt->execute([$category_id]);
} else {
    $stmt = $pdo->query("SELECT * FROM products WHERE is_hidden = 0 ORDER BY id DESC");
}
$products = $stmt->fetchAll();

// Fetch categories for filter
$cat_stmt = $pdo->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll();
?>
<div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <h1 style="text-align: center; margin-bottom: 2rem; font-family: var(--font-heading);">Our Collection</h1>
    
    <!-- Category Filter -->
    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem; margin-bottom: 3rem;">
        <a href="shop.php" class="btn <?php echo !$category_id ? 'btn-primary' : 'btn-outline'; ?>" style="<?php echo !$category_id ? '' : 'color: var(--primary-color); border-color: var(--primary-color);'; ?>">All</a>
        <?php foreach($categories as $cat): ?>
            <a href="shop.php?category=<?php echo $cat['id']; ?>" class="btn <?php echo $category_id == $cat['id'] ? 'btn-primary' : 'btn-outline'; ?>" style="<?php echo $category_id == $cat['id'] ? '' : 'color: var(--primary-color); border-color: var(--primary-color);'; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
        <?php endforeach; ?>
    </div>


    <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 2.5rem;">
        <?php foreach($products as $product): ?>
            <div class="product-card">
                <a href="<?= BASE_URL ?>product.php?id=<?php echo $product['id']; ?>">
                    <div class="product-img-wrap">
                        <img src="<?= BASE_URL ?><?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img">
                        <div class="product-overlay">
                            <form action="<?= BASE_URL ?>cart_add.php" method="POST">
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
        <?php if(count($products) == 0): ?>
            <p style="text-align: center; width: 100%; grid-column: 1 / -1;">No products found in this category.</p>
        <?php endif; ?>
    </div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
