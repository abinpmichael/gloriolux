<?php
require_once 'includes/header.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

// Fetch cart items
if ($user_id) {
    $stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, c.custom_text, c.custom_image, c.custom_price_addon, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, c.custom_text, c.custom_image, c.custom_price_addon, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ? AND c.user_id IS NULL");
    $stmt->execute([$session_id]);
}
$cart_items = $stmt->fetchAll();
$total = 0;
?>

<style>
    @media (max-width: 600px) {
        .cart-table thead {
            display: none;
        }
        .cart-table tr {
            display: block;
            border-bottom: 2px solid var(--glass-border);
            padding: 1rem 0;
        }
        .cart-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border: none !important;
            text-align: right;
        }
        .cart-table td[data-label]::before {
            content: attr(data-label);
            font-weight: bold;
            color: var(--text-light);
            text-align: left;
            margin-right: 1rem;
        }
        .cart-table td:first-child {
            display: flex;
            justify-content: flex-start;
            text-align: left;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.05) !important;
        }
    }
</style>

<div class="container" style="padding-top: 120px; padding-bottom: 80px; min-height: 70vh;">
    <h1 style="text-align: center; margin-bottom: 3rem; font-family: var(--font-heading);">Your Shopping Cart</h1>

    <?php if (count($cart_items) > 0): ?>
        <div style="display: flex; flex-wrap: wrap; gap: 3rem;">
            <!-- Cart Items -->
            <div style="flex: 2; min-width: 300px;">
                <div class="glass-panel" style="padding: 2rem; overflow-x: auto;">

                    <table class="cart-table" style="width: 100%; border-collapse: collapse;">
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
                                $base_price = $item['price'] + ($item['custom_price_addon'] ?? 0);
                                $item_total = $base_price * $item['quantity'];
                                $total += $item_total;
                                $display_image = $item['custom_image'] ? $item['custom_image'] : $item['image_url'];
                            ?>
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <td style="padding: 1rem 0; display: flex; align-items: center; gap: 1rem;">
                                    <div style="position: relative;">
                                        <img src="<?= BASE_URL ?><?php echo htmlspecialchars($display_image); ?>" alt="Product" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                        <?php if($item['custom_image']): ?>
                                            <span style="position: absolute; top: -5px; right: -5px; background: var(--primary-color); color: white; font-size: 10px; padding: 2px 5px; border-radius: 10px;">Custom</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span style="font-weight: 600; display: block;"><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                 <td style="padding: 1rem 0;" data-label="Price">
                                    <?php echo formatPrice($base_price); ?>
                                    <?php if($item['custom_price_addon'] > 0): ?>
                                        <br><small style="color:var(--text-light);">(includes +$<?= number_format($item['custom_price_addon'], 2) ?> topper)</small>
                                    <?php endif; ?>
                                 </td>
                                <td style="padding: 1rem 0;" data-label="Quantity">
                                    <form action="<?= BASE_URL ?>cart_update.php" method="POST" style="display:flex; align-items:center; gap:5px;">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock'] > 0 ? $item['stock'] : 10; ?>" style="width: 50px; text-align: center; border: 1px solid #ddd; border-radius: 4px; padding: 0.3rem;" onchange="this.form.submit()">
                                    </form>
                                </td>
                                <td style="padding: 1rem 0; font-weight: bold;" data-label="Total"><?php echo formatPrice($item_total); ?></td>

                                <td style="padding: 1rem 0; text-align: right;">
                                    <a href="<?= BASE_URL ?>cart_remove.php?id=<?php echo $item['cart_id']; ?>" style="color: red;"><i class="fas fa-trash"></i></a>
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
                        <span><?php echo formatPrice($total); ?></span>
                    </div>

                    <?php if ($tax_enabled): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--text-light);">
                            <span>Tax (<?= number_format($tax_rate, 2) ?>%)</span>
                            <span><?php echo formatPrice($tax_amount); ?></span>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; color: var(--text-light); border-bottom: 1px solid var(--glass-border); padding-bottom: 1.5rem;">
                        <span>Shipping</span>
                        <span>Calculated at checkout</span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; font-weight: bold; font-size: 1.2rem;">
                        <span>Total</span>
                        <span><?php echo formatPrice($grand_total); ?></span>
                    </div>

                    <a href="<?= BASE_URL ?>checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; display: block;">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="glass-panel" style="padding: 4rem 2rem; text-align: center;">
            <i class="fas fa-shopping-bag" style="font-size: 4rem; color: var(--glass-border); margin-bottom: 1.5rem;"></i>
            <h2>Your cart is empty</h2>
            <p style="color: var(--text-light); margin-bottom: 2rem;">Looks like you haven't added anything to your cart yet.</p>
            <a href="<?= BASE_URL ?>shop.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
