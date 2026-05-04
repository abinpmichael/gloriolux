<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '../') . "login.php"); exit;
}
require_once '../includes/db.php';

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $image_url = 'assets/img/product1.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = '../uploads/';
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
            $image_url = 'uploads/' . $filename;
        }
    }
    $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['category_id'], $_POST['name'], $_POST['description'], $_POST['price'], $_POST['stock'], $image_url]);
    header("Location: products.php?success=1"); exit;
}

// Handle product edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $image_url = $_POST['current_image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = '../uploads/';
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
            $image_url = 'uploads/' . $filename;
        }
    }
    $stmt = $pdo->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image_url=? WHERE id=?");
    $stmt->execute([$_POST['category_id'], $_POST['name'], $_POST['description'], $_POST['price'], $_POST['stock'], $image_url, $_POST['id']]);
    header("Location: products.php?success=2"); exit;
}

// Handle visibility toggle
if (isset($_GET['toggle_visibility'])) {
    $stmt = $pdo->prepare("UPDATE products SET is_hidden = NOT is_hidden WHERE id=?");
    $stmt->execute([(int)$_GET['toggle_visibility']]);
    header("Location: products.php?success=4"); exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: products.php?success=3"); exit;
}

// Search Logic
$search = $_GET['search'] ?? '';
$where_clause = "";
$params = [];
if (!empty($search)) {
    $where_clause = " WHERE p.name LIKE ? OR c.name LIKE ? ";
    $params = ["%$search%", "%$search%"];
}

// Fetch products
$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where_clause ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

$admin_page_title = 'Manage Products';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Manage Products</h2>
    <div style="display:flex; gap:10px;">
        <form action="products.php" method="GET" style="display:flex; gap:5px;">
            <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" style="width:200px; padding: 0.5rem 0.8rem; font-size:0.85rem;">
            <button type="submit" class="btn btn-outline" style="padding: 0.5rem;"><i class="fas fa-search"></i></button>
        </form>
        <button onclick="document.getElementById('add-modal').style.display='block'" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add
        </button>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success">
        <i class="fas fa-check-circle"></i>
        <?php
            if ($_GET['success']==1) echo 'Product added successfully!';
            elseif ($_GET['success']==2) echo 'Product updated successfully!';
            elseif ($_GET['success']==3) echo 'Product deleted successfully!';
            elseif ($_GET['success']==4) echo 'Product visibility updated!';
        ?>
    </div>
<?php endif; ?>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($products as $product): ?>
            <tr style="<?= ($product['is_hidden'] ?? 0) ? 'opacity:0.6;' : '' ?>">
                <td>
                    <img src="<?= BASE_URL ?><?= htmlspecialchars($product['image_url']) ?>" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:6px; border:1px solid #eee;">
                </td>
                <td style="font-weight:bold;"><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></td>
                <td>$<?= number_format($product['price'], 2) ?></td>
                <td><?= $product['stock'] ?></td>
                <td>
                    <?php if($product['is_hidden'] ?? 0): ?>
                        <span class="status-badge" style="background:#f8d7da; color:#721c24;">Hidden</span>
                    <?php else: ?>
                        <span class="status-badge" style="background:#d4edda; color:#155724;">Visible</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;">
                    <div style="display:flex; justify-content:flex-end; gap:12px;">
                        <a href="products.php?toggle_visibility=<?= $product['id'] ?>" title="<?= ($product['is_hidden'] ?? 0) ? 'Show' : 'Hide' ?>" style="color: <?= ($product['is_hidden'] ?? 0) ? '#999' : '#27ae60' ?>; font-size:1rem;">
                            <i class="fas <?= ($product['is_hidden'] ?? 0) ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                        </a>
                        <a href="#" class="edit-btn" 
                            data-id="<?= $product['id'] ?>"
                            data-name="<?= htmlspecialchars($product['name']) ?>"
                            data-category="<?= $product['category_id'] ?>"
                            data-price="<?= $product['price'] ?>"
                            data-description="<?= htmlspecialchars($product['description']) ?>"
                            data-stock="<?= $product['stock'] ?>"
                            data-image="<?= htmlspecialchars($product['image_url']) ?>"
                            style="color:var(--secondary-color); font-size:1rem;">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="products.php?delete=<?= $product['id'] ?>" onclick="return confirm('Delete this product?');" style="color:#ff6b6b; font-size:1rem;">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($products) == 0): ?>
                <tr><td colspan="7" style="text-align:center; padding:2rem;">No products found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Product Modal -->
<div id="add-modal" class="admin-modal">
    <div class="admin-modal-content">
        <span onclick="document.getElementById('add-modal').style.display='none'" class="admin-modal-close">&times;</span>
        <h3 style="margin-bottom:1.5rem; font-family:var(--font-heading);">Add New Product</h3>
        <form action="products.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-group"><label>Product Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group"><label>Category</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;"><label>Price ($)</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                <div class="form-group" style="flex:1;"><label>Stock</label><input type="number" name="stock" class="form-control" required></div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
            <div class="form-group"><label>Product Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Save Product</button>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="edit-modal" class="admin-modal">
    <div class="admin-modal-content">
        <span onclick="document.getElementById('edit-modal').style.display='none'" class="admin-modal-close">&times;</span>
        <h3 style="margin-bottom:1.5rem; font-family:var(--font-heading);">Edit Product</h3>
        <form action="products.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <input type="hidden" name="current_image" id="edit-current-image">
            <div class="form-group"><label>Product Name</label><input type="text" name="name" id="edit-name" class="form-control" required></div>
            <div class="form-group"><label>Category</label>
                <select name="category_id" id="edit-category" class="form-control" required>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;"><label>Price ($)</label><input type="number" step="0.01" name="price" id="edit-price" class="form-control" required></div>
                <div class="form-group" style="flex:1;"><label>Stock</label><input type="number" name="stock" id="edit-stock" class="form-control" required></div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" id="edit-description" class="form-control" rows="4"></textarea></div>
            <div class="form-group"><label>Product Image (optional)</label><input type="file" name="image" class="form-control" accept="image/*"></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Update Product</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('edit-id').value          = this.dataset.id;
        document.getElementById('edit-name').value        = this.dataset.name;
        document.getElementById('edit-category').value    = this.dataset.category;
        document.getElementById('edit-price').value       = this.dataset.price;
        document.getElementById('edit-stock').value       = this.dataset.stock;
        document.getElementById('edit-description').value = this.dataset.description;
        document.getElementById('edit-current-image').value = this.dataset.image;
        document.getElementById('edit-modal').style.display = 'block';
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
