<?php
require_once 'includes/header.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

// Fetch cart items
if ($user_id) {
    $stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ? AND c.user_id IS NULL");
    $stmt->execute([$session_id]);
}
$cart_items = $stmt->fetchAll();
$total = 0;
?>

<div class="container" style="padding-top: 120px; padding-bottom: 80px; min-height: 70vh;">
    <h1 style="text-align: center; margin-bottom: 3rem; font-family: var(--font-heading);">Your Shopping Cart</h1>

    <?php if (count($cart_items) > 0): ?>
        <div style="display: flex; flex-wrap: wrap; gap: 3rem;">
            <!-- Cart Items -->
            <div style="flex: 2; min-width: 300px;">
                <div class="glass-panel" style="padding: 2rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--glass-border); text-align: left;">
                                <th style="padding-bottom: 1rem;">Product</th>
                                <th style="padding-bottom: 1rem;">Price</th>
                                <th style="padding-bottom: 1rem;">Quantity</th>
                                <th style="padding-bottom: 1rem;">Total</th>
                                <th style="padding-bottom: 1rem;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): 
                                $item_total = $item['price'] * $item['quantity'];
                                $total += $item_total;
                            ?>
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <td style="padding: 1rem 0; display: flex; align-items: center; gap: 1rem;">
                                    <img src="/Gloriolux/<?php echo htmlspecialchars($item['image_url']); ?>" alt="Product" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></span>
                                </td>
                                <td style="padding: 1rem 0;">$<?php echo number_format($item['price'], 2); ?></td>
                                <td style="padding: 1rem 0;">
                                    <form action="/Gloriolux/cart_update.php" method="POST" style="display:flex; align-items:center; gap:5px;">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock'] > 0 ? $item['stock'] : 10; ?>" style="width: 50px; text-align: center; border: 1px solid #ddd; border-radius: 4px; padding: 0.3rem;" onchange="this.form.submit()">
                                    </form>
                                </td>
                                <td style="padding: 1rem 0; font-weight: bold;">$<?php echo number_format($item_total, 2); ?></td>
                                <td style="padding: 1rem 0; text-align: right;">
                                    <a href="/Gloriolux/cart_remove.php?id=<?php echo $item['cart_id']; ?>" style="color: red;"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cart Summary -->
            <div style="flex: 1; min-width: 300px;">
                <div class="glass-panel" style="padding: 2rem; position: sticky; top: 100px;">
                    <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">Order Summary</h3>
                    
                    <?php
                        // Fetch tax settings
                        $stmt_tax = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('tax_enabled', 'tax_rate')");
                        $tax_settings = $stmt_tax->fetchAll(PDO::FETCH_KEY_PAIR);
                        $tax_enabled = ($tax_settings['tax_enabled'] ?? '0') === '1';
                        $tax_rate = (float)($tax_settings['tax_rate'] ?? '0');
                        
                        $tax_amount = 0;
                        if ($tax_enabled && $tax_rate > 0) {
                            $tax_amount = $total * ($tax_rate / 100);
                        }
                        $grand_total = $total + $tax_amount;
                    ?>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--text-light);">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>

                    <?php if ($tax_enabled): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--text-light);">
                            <span>Tax (<?= number_format($tax_rate, 2) ?>%)</span>
                            <span>$<?php echo number_format($tax_amount, 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; color: var(--text-light); border-bottom: 1px solid var(--glass-border); padding-bottom: 1.5rem;">
                        <span>Shipping</span>
                        <span>Calculated at checkout</span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; font-weight: bold; font-size: 1.2rem;">
                        <span>Total</span>
                        <span>$<?php echo number_format($grand_total, 2); ?></span>
                    </div>

                    <a href="/Gloriolux/checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; display: block;">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="glass-panel" style="padding: 4rem 2rem; text-align: center;">
            <i class="fas fa-shopping-bag" style="font-size: 4rem; color: var(--glass-border); margin-bottom: 1.5rem;"></i>
            <h2>Your cart is empty</h2>
            <p style="color: var(--text-light); margin-bottom: 2rem;">Looks like you haven't added anything to your cart yet.</p>
            <a href="/Gloriolux/shop.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
