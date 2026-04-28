<?php
session_start();
// Verify admin role
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: " . BASE_URL . "login.php"); 
    exit; 
}
require_once '../includes/db.php';

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    
    // Simple mock image upload handling
    $image_url = 'assets/img/product1.png'; // default fallback
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $destination = '../uploads/' . $filename;
        if (move_uploaded_file($tmp_name, $destination)) {
            $image_url = 'uploads/' . $filename;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$category_id, $name, $description, $price, $stock, $image_url]);
    
    header("Location: products.php?success=1");
    exit;
}

// Handle product edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];

    // Image handling (optional replace)
    $image_url = $_POST['current_image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $destination = '../uploads/' . $filename;
        if (move_uploaded_file($tmp_name, $destination)) {
            $image_url = 'uploads/' . $filename;
        }
    }

    $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, image_url = ? WHERE id = ?");
    $stmt->execute([$category_id, $name, $description, $price, $stock, $image_url, $id]);
    header("Location: products.php?success=2");
    exit;
}

// Handle visibility toggle via GET
if (isset($_GET['toggle_visibility'])) {
    $id = (int)$_GET['toggle_visibility'];
    $stmt = $pdo->prepare("UPDATE products SET is_hidden = NOT is_hidden WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: products.php?success=4");
    exit;
}

// Handle product delete via GET
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: products.php?success=3");
    exit;
}

// Fetch products
$products_stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $products_stmt->fetchAll();

// Fetch categories for form
$cat_stmt = $pdo->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | Gloriolux Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background-color: var(--primary-color); color: #fff; padding: 2rem 1rem; }
        .admin-main { flex: 1; padding: 2rem; background-color: #f4f6f8; }
        .admin-sidebar a { display: block; padding: 1rem; color: #ccc; border-radius: 8px; margin-bottom: 0.5rem; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background-color: rgba(255,255,255,0.1); color: #fff; }
        .admin-logo { font-family: var(--font-heading); font-size: 1.5rem; text-align: center; margin-bottom: 3rem; color: #fff; }
        .admin-logo span { color: var(--secondary-color); }
        .data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        .data-table th { background-color: #f8f9fa; font-weight: 600; color: var(--text-light); }
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:1000; overflow-y:auto; }
        .modal-content { background:#fff; width:100%; max-width:600px; margin:50px auto; padding:2rem; border-radius:12px; position:relative; }
        .modal-close { position:absolute; right:20px; top:20px; cursor:pointer; font-size:1.5rem; }
    </style>
    <link rel="icon" href="<?= BASE_URL ?>assets/img/logo.png" type="image/png">
</head>
<body>
<div class="admin-layout">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
            <h2>Manage Products</h2>
            <button onclick="document.getElementById('add-modal').style.display='block'" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</button>
        </div>
        <?php if(isset($_GET['success'])): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;">
                <?php
                    $msg = '';
                    if ($_GET['success']==1) $msg='Product added successfully!';
                    elseif ($_GET['success']==2) $msg='Product updated successfully!';
                    elseif ($_GET['success']==3) $msg='Product deleted successfully!';
                    elseif ($_GET['success']==4) $msg='Product visibility updated!';
                    echo $msg;
                ?>
            </div>
        <?php endif; ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
            <?php foreach($products as $product): ?>
            <div style="background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05); display: flex; flex-direction: column; <?= (isset($product['is_hidden']) && $product['is_hidden'] == 1) ? 'opacity: 0.6;' : '' ?>">
                <img src="<?= BASE_URL ?><?php echo htmlspecialchars($product['image_url']); ?>" alt="" style="width: 100%; height: 200px; object-fit: cover;">
                <div style="padding: 1.2rem; flex-grow: 1; display: flex; flex-direction: column;">
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; color: var(--primary-color);"><?php echo htmlspecialchars($product['name']); ?> <?= (isset($product['is_hidden']) && $product['is_hidden'] == 1) ? '<span style="color:red; font-size:0.8rem;">(Hidden)</span>' : '' ?></h3>
                    <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?></p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 1rem; border-top: 1px solid #eee;">
                        <div>
                            <span style="font-weight: bold; color: var(--secondary-color);">$<?php echo number_format($product['price'], 2); ?></span>
                            <br><small style="color: #888;">Stock: <?php echo $product['stock']; ?></small>
                        </div>
                        <div>
                            <?php if(isset($product['is_hidden']) && $product['is_hidden'] == 1): ?>
                                <a href="products.php?toggle_visibility=<?php echo $product['id']; ?>" style="color: #999; margin-right: 10px; font-size: 1.2rem;" title="Currently Hidden. Click to Show"><i class="fas fa-eye-slash"></i></a>
                            <?php else: ?>
                                <a href="products.php?toggle_visibility=<?php echo $product['id']; ?>" style="color: #27ae60; margin-right: 10px; font-size: 1.2rem;" title="Currently Visible. Click to Hide"><i class="fas fa-eye"></i></a>
                            <?php endif; ?>
                            <a href="#" class="edit-btn" data-id="<?php echo $product['id']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-category="<?php echo $product['category_id']; ?>" data-price="<?php echo $product['price']; ?>" data-description="<?php echo htmlspecialchars($product['description']); ?>" data-stock="<?php echo $product['stock']; ?>" data-image="<?php echo htmlspecialchars($product['image_url']); ?>" style="color: var(--secondary-color); margin-right: 10px; font-size: 1.2rem;" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="products.php?delete=<?php echo $product['id']; ?>" onclick="return confirm('Delete this product?');" style="color: #ff6b6b; font-size: 1.2rem;" title="Delete"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(count($products) == 0): ?>
                <p style="grid-column: 1 / -1; text-align: center; padding: 2rem;">No products found. Add some!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div id="add-modal" class="modal">
    <div class="modal-content">
        <span onclick="document.getElementById('add-modal').style.display='none'" class="modal-close">&times;</span>
        <h3 style="margin-bottom:1.5rem; font-family:var(--font-heading);">Add New Product</h3>
        <form action="products.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-group"><label>Product Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group"><label>Category</label><select name="category_id" class="form-control" required>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select></div>
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
<div id="edit-modal" class="modal">
    <div class="modal-content">
        <span onclick="document.getElementById('edit-modal').style.display='none'" class="modal-close">&times;</span>
        <h3 style="margin-bottom:1.5rem; font-family:var(--font-heading);">Edit Product</h3>
        <form action="products.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <input type="hidden" name="current_image" id="edit-current-image">
            <div class="form-group"><label>Product Name</label><input type="text" name="name" id="edit-name" class="form-control" required></div>
            <div class="form-group"><label>Category</label><select name="category_id" id="edit-category" class="form-control" required>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select></div>
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
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-name').value = this.dataset.name;
            document.getElementById('edit-category').value = this.dataset.category;
            document.getElementById('edit-price').value = this.dataset.price;
            document.getElementById('edit-stock').value = this.dataset.stock;
            document.getElementById('edit-description').value = this.dataset.description;
            document.getElementById('edit-current-image').value = this.dataset.image;
            document.getElementById('edit-modal').style.display = 'block';
        });
    });
    // Close modal on outside click
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
</script>
</body>
</html>
