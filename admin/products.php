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
    
    $product_id = $pdo->lastInsertId();
    if (isset($_FILES['gallery_images'])) {
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                $filename = time() . '_' . uniqid() . '_' . basename($_FILES['gallery_images']['name'][$key]);
                if (move_uploaded_file($tmp_name, '../uploads/' . $filename)) {
                    $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)")->execute([$product_id, 'uploads/' . $filename]);
                }
            }
        }
    }
    
    if (isset($_POST['toppers']) && is_array($_POST['toppers'])) {
        $pt_stmt = $pdo->prepare("INSERT INTO product_toppers (product_id, topper_id, custom_image_url) VALUES (?, ?, ?)");
        foreach ($_POST['toppers'] as $tid) {
            $t_img = null;
            $img_field = "topper_images_" . $tid;
            if (isset($_FILES[$img_field]) && $_FILES[$img_field]['error'] === UPLOAD_ERR_OK) {
                $target_dir = '../uploads/';
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                $filename = time() . '_t_' . basename($_FILES[$img_field]['name']);
                if (move_uploaded_file($_FILES[$img_field]['tmp_name'], $target_dir . $filename)) {
                    $t_img = 'uploads/' . $filename;
                }
            }
            $pt_stmt->execute([$product_id, (int)$tid, $t_img]);
        }
    }

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
    
    $product_id = $_POST['id'];
    if (isset($_FILES['gallery_images'])) {
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                $filename = time() . '_' . uniqid() . '_' . basename($_FILES['gallery_images']['name'][$key]);
                if (move_uploaded_file($tmp_name, '../uploads/' . $filename)) {
                    $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)")->execute([$product_id, 'uploads/' . $filename]);
                }
            }
        }
    }
    
    $existing_pt_stmt = $pdo->prepare("SELECT topper_id, custom_image_url FROM product_toppers WHERE product_id = ?");
    $existing_pt_stmt->execute([$product_id]);
    $existing_pts = [];
    foreach($existing_pt_stmt->fetchAll() as $row) {
        $existing_pts[$row['topper_id']] = $row['custom_image_url'];
    }
    
    $pdo->prepare("DELETE FROM product_toppers WHERE product_id = ?")->execute([$product_id]);
    if (isset($_POST['toppers']) && is_array($_POST['toppers'])) {
        $pt_stmt = $pdo->prepare("INSERT INTO product_toppers (product_id, topper_id, custom_image_url) VALUES (?, ?, ?)");
        foreach ($_POST['toppers'] as $tid) {
            $t_img = $existing_pts[(int)$tid] ?? null;
            $img_field = "topper_images_" . $tid;
            if (isset($_FILES[$img_field]) && $_FILES[$img_field]['error'] === UPLOAD_ERR_OK) {
                $target_dir = '../uploads/';
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                $filename = time() . '_t_' . basename($_FILES[$img_field]['name']);
                if (move_uploaded_file($_FILES[$img_field]['tmp_name'], $target_dir . $filename)) {
                    $t_img = 'uploads/' . $filename;
                }
            }
            $pt_stmt->execute([$product_id, (int)$tid, $t_img]);
        }
    }

    header("Location: products.php?success=2"); exit;
}

// Handle visibility toggle
if (isset($_GET['toggle_visibility'])) {
    $pid = (int)$_GET['toggle_visibility'];
    $stmt = $pdo->prepare("SELECT is_hidden FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $current = $stmt->fetchColumn();
    $new_hidden = $current ? 0 : 1;
    $new_status = $new_hidden ? 'Hidden' : 'Active';
    
    $stmt = $pdo->prepare("UPDATE products SET is_hidden = ?, status = ? WHERE id = ?");
    $stmt->execute([$new_hidden, $new_status, $pid]);
    header("Location: products.php?success=4"); exit;
}

if (isset($_GET['delete'])) {
    $pid = (int)$_GET['delete'];
    try {
        // Try to delete related data (safe to delete)
        $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$pid]);
        $pdo->prepare("DELETE FROM product_toppers WHERE product_id = ?")->execute([$pid]);
        $pdo->prepare("DELETE FROM cart WHERE product_id = ?")->execute([$pid]);
        $pdo->prepare("DELETE FROM reviews WHERE product_id = ?")->execute([$pid]);
        
        // Try to delete the product itself
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$pid]);
        
        header("Location: products.php?success=3"); exit;
    } catch (Exception $e) {
        // Fallback: If it's linked to orders, we CANNOT delete it (Integrity Constraint)
        // Instead, we mark it as "Archived" so it disappears from the store but remains in records.
        $stmt = $pdo->prepare("UPDATE products SET is_hidden = 1, status = 'Archived' WHERE id = ?");
        $stmt->execute([$pid]);
        header("Location: products.php?success=6"); exit;
    }
}

// Handle gallery image delete
if (isset($_GET['delete_image'])) {
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id=?");
    $stmt->execute([(int)$_GET['delete_image']]);
    header("Location: products.php?success=5"); exit;
}

// Fetch products
$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

$all_images = $pdo->query("SELECT * FROM product_images")->fetchAll();
$gallery_by_product = [];
foreach($all_images as $img) {
    $gallery_by_product[$img['product_id']][] = $img;
}

$all_toppers = $pdo->query("SELECT * FROM toppers ORDER BY name ASC")->fetchAll();
$pt_rows = $pdo->query("SELECT * FROM product_toppers")->fetchAll();
$toppers_by_product = [];
foreach($pt_rows as $row) {
    $toppers_by_product[$row['product_id']][] = $row['topper_id'];
}

$admin_extra_css = '
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
.dataTables_wrapper { width: 100%; overflow-x: auto; background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.dataTables_filter input { border: 1px solid #ddd; border-radius: 4px; padding: 5px 10px; margin-left: 5px; }
table.dataTable.no-footer { border-bottom: none; }
</style>
';

$admin_page_title = 'Manage Products';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Manage Products</h2>
    <div style="display:flex; gap:10px;">
        <button onclick="document.getElementById('add-modal').style.display='block'" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
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
            elseif ($_GET['success']==5) echo 'Image deleted successfully!';
            elseif ($_GET['success']==6) echo 'Product is linked to existing orders, so it has been hidden from the store instead of deleted.';
        ?>
    </div>
<?php endif; ?>

<div class="data-table-wrap">
    <table id="productsTable" class="data-table" style="width: 100%;">
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
                    <?php 
                        $status = $product['status'] ?? 'Active';
                        $bg = '#d4edda'; $cl = '#155724';
                        if ($status == 'Hidden') { $bg = '#fff3cd'; $cl = '#856404'; }
                        elseif ($status == 'Archived') { $bg = '#e2e3e5'; $cl = '#383d41'; }
                    ?>
                    <span class="status-badge" style="background:<?= $bg ?>; color:<?= $cl ?>;"><?= $status ?></span>
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
                            data-gallery="<?= htmlspecialchars(json_encode($gallery_by_product[$product['id']] ?? [])) ?>"
                            data-toppers="<?= htmlspecialchars(json_encode($toppers_by_product[$product['id']] ?? [])) ?>"
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
            <div class="form-group"><label>Product Image (Main Cover)</label><input type="file" name="image" class="form-control" accept="image/*"></div>
            
            <div class="form-group">
                <label>Additional Gallery Images</label>
                <div id="add-gallery-inputs">
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" style="margin-bottom: 5px;" multiple>
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" style="margin-bottom: 5px;" multiple>
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" style="margin-bottom: 5px;" multiple>
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" style="margin-bottom: 5px;" multiple>
                </div>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('add-gallery-inputs').insertAdjacentHTML('beforeend', '<input type=\'file\' name=\'gallery_images[]\' class=\'form-control\' accept=\'image/*\' style=\'margin-bottom: 5px;\' multiple>');" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; margin-top: 5px;">+ Add Another Image Field</button>
            </div>
            
            <div class="form-group">
                <label>Available Candle Toppers</label>
                <div style="background: #f8f9fa; padding: 1rem; border: 1px solid #ddd; border-radius: 6px; max-height: 250px; overflow-y: auto;">
                    <?php foreach($all_toppers as $t): ?>
                        <div style="margin-bottom: 0.8rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">
                            <label style="display: block; margin-bottom: 0.2rem; font-weight: normal;">
                                <input type="checkbox" name="toppers[]" value="<?= $t['id'] ?>"> 
                                <?= htmlspecialchars($t['name']) ?> (+<?= formatPrice($t['price_addon']) ?>)
                            </label>
                            <div style="margin-left: 20px; font-size: 0.8rem; color: #666;">
                                Product-Specific Image (optional):
                                <input type="file" name="topper_images_<?= $t['id'] ?>" accept="image/*" style="font-size: 0.75rem;">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 1rem;">Save Product</button>
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
            <div class="form-group"><label>Product Image (Main Cover, optional update)</label><input type="file" name="image" class="form-control" accept="image/*"></div>
            
            <div class="form-group">
                <label>Existing Additional Images</label>
                <div id="edit-gallery-images" style="margin-bottom:10px;"></div>
                
                <label>Upload More Images</label>
                <div id="edit-gallery-inputs">
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" style="margin-bottom: 5px;" multiple>
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" style="margin-bottom: 5px;" multiple>
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" style="margin-bottom: 5px;" multiple>
                </div>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('edit-gallery-inputs').insertAdjacentHTML('beforeend', '<input type=\'file\' name=\'gallery_images[]\' class=\'form-control\' accept=\'image/*\' style=\'margin-bottom: 5px;\' multiple>');" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; margin-top: 5px;">+ Add Another Image Field</button>
            </div>
            
            <div class="form-group">
                <label>Available Candle Toppers</label>
                <div style="background: #f8f9fa; padding: 1rem; border: 1px solid #ddd; border-radius: 6px; max-height: 250px; overflow-y: auto;">
                    <?php foreach($all_toppers as $t): ?>
                        <div style="margin-bottom: 0.8rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">
                            <label style="display: block; margin-bottom: 0.2rem; font-weight: normal;">
                                <input type="checkbox" name="toppers[]" value="<?= $t['id'] ?>" class="edit-topper-checkbox" data-topper-id="<?= $t['id'] ?>"> 
                                <?= htmlspecialchars($t['name']) ?> (+<?= formatPrice($t['price_addon']) ?>)
                            </label>
                            <div style="margin-left: 20px; font-size: 0.8rem; color: #666;">
                                Product-Specific Image (optional update):
                                <input type="file" name="topper_images_<?= $t['id'] ?>" accept="image/*" style="font-size: 0.75rem;">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 1rem;">Update Product</button>
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
        
        let gallery = JSON.parse(this.dataset.gallery || "[]");
        let galleryContainer = document.getElementById('edit-gallery-images');
        galleryContainer.innerHTML = '';
        gallery.forEach(img => {
            galleryContainer.innerHTML += `
                <div style="display:inline-block; position:relative; margin-right:10px;">
                    <img src="${'<?= BASE_URL ?>' + img.image_url}" style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
                    <a href="products.php?delete_img=${img.id}" onclick="return confirm('Delete image?');" style="position:absolute; top:-5px; right:-5px; background:red; color:white; border-radius:50%; width:20px; height:20px; text-align:center; line-height:20px; font-size:12px;"><i class="fas fa-times"></i></a>
                </div>
            `;
        });
        
        let toppers = JSON.parse(this.dataset.toppers || "[]");
        document.querySelectorAll('.edit-topper-checkbox').forEach(cb => {
            cb.checked = toppers.includes(parseInt(cb.dataset.topperId));
        });

        document.getElementById('edit-modal').style.display = 'flex';
        if(gallery.length === 0) {
            galleryContainer.innerHTML = '<span style="color:#888; font-size:0.9rem;">No additional images.</span>';
        } else {
            gallery.forEach(function(img) {
                galleryContainer.innerHTML += `
                <div style="display:inline-block; position:relative; margin-right:10px; margin-bottom:10px;">
                    <img src="<?= BASE_URL ?>${img.image_url}" style="width:60px; height:60px; object-fit:cover; border-radius:4px; border:1px solid #ddd;">
                    <a href="products.php?delete_image=${img.id}" onclick="return confirm('Delete this image?');" style="position:absolute; top:-8px; right:-8px; background:#ff4757; color:white; border-radius:50%; width:20px; height:20px; text-align:center; line-height:20px; font-size:14px; text-decoration:none; box-shadow:0 2px 4px rgba(0,0,0,0.2);">&times;</a>
                </div>`;
            });
        }
        
        document.getElementById('edit-modal').style.display = 'block';
    });
});
</script>

<?php 
$admin_extra_js = '
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $("#productsTable").DataTable({
        "pageLength": 10,
        "ordering": true,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [0, 6] } // Disable sorting on Image and Actions columns
        ],
        "language": {
            "search": "Filter products:"
        }
    });
});
</script>
';
require_once 'includes/admin_footer.php'; 
?>
