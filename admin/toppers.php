<?php
session_start();
require_once '../includes/db.php';

// Check admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Handle Add Topper
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $price_addon = (float)$_POST['price_addon'];
    
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = '../assets/img/toppers/';
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
            $image_url = 'assets/img/toppers/' . $filename;
        }
    }
    
    if ($name && $image_url) {
        $stmt = $pdo->prepare("INSERT INTO toppers (name, image_url, price_addon) VALUES (?, ?, ?)");
        $stmt->execute([$name, $image_url, $price_addon]);
    }
    header("Location: toppers.php?success=1");
    exit;
}

// Handle Edit Topper
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $price_addon = (float)$_POST['price_addon'];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = '../assets/img/toppers/';
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
            $image_url = 'assets/img/toppers/' . $filename;
            $stmt = $pdo->prepare("UPDATE toppers SET name = ?, price_addon = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$name, $price_addon, $image_url, $id]);
        }
    } else {
        $stmt = $pdo->prepare("UPDATE toppers SET name = ?, price_addon = ? WHERE id = ?");
        $stmt->execute([$name, $price_addon, $id]);
    }
    
    header("Location: toppers.php?success=2");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Check if linked to products
    $check = $pdo->prepare("SELECT COUNT(*) FROM product_toppers WHERE topper_id = ?");
    $check->execute([$id]);
    if ($check->fetchColumn() > 0) {
        header("Location: toppers.php?error=linked");
    } else {
        $stmt = $pdo->prepare("DELETE FROM toppers WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: toppers.php?deleted=1");
    }
    exit;
}

// Fetch all toppers
$stmt = $pdo->query("SELECT * FROM toppers ORDER BY name ASC");
$toppers = $stmt->fetchAll();

$admin_extra_css = '
<style>
    .topper-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
        padding: 1rem 0;
    }
    .topper-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid #eee;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .topper-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        border-color: var(--secondary-color);
    }
    .topper-img-wrap {
        width: 100%;
        height: 220px;
        overflow: hidden;
        background: #f9f9f9;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .topper-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 1rem;
        transition: transform 0.5s ease;
    }
    .topper-card:hover .topper-img-wrap img {
        transform: scale(1.1);
    }
    .topper-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--secondary-color);
        color: #fff;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 2;
    }
    .topper-info {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .topper-name {
        font-family: var(--font-heading);
        font-size: 1.25rem;
        color: var(--primary-color);
        margin: 0;
    }
    .topper-price {
        color: var(--secondary-color);
        font-weight: bold;
        font-size: 1.1rem;
    }
    .topper-actions {
        padding: 1rem 1.5rem;
        background: #fbfbfb;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .action-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    .btn-edit { background: rgba(212, 175, 55, 0.1); color: var(--secondary-color); }
    .btn-edit:hover { background: var(--secondary-color); color: #fff; transform: rotate(15deg); }
    .btn-delete { background: rgba(255, 107, 107, 0.1); color: #ff6b6b; }
    .btn-delete:hover { background: #ff6b6b; color: #fff; transform: rotate(-15deg); }
</style>
';

$admin_page_title = 'Candle Toppers';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h2 style="margin:0;">Candle Toppers</h2>
        <p style="color:var(--text-light); margin-top:0.5rem;">Manage custom shapes and their additional costs.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('add-modal').style.display='flex'">
        <i class="fas fa-plus"></i> Add New Shape
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Topper <?= $_GET['success'] == 1 ? 'added' : 'updated' ?> successfully!</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-trash"></i> Topper removed successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] == 'linked'): ?>
    <div class="admin-alert" style="background:#fff3cd; color:#856404; border-left:4px solid #856404;"><i class="fas fa-exclamation-triangle"></i> Cannot delete: This topper is currently assigned to one or more products.</div>
<?php endif; ?>

<div class="topper-grid">
    <?php foreach ($toppers as $topper): ?>
    <div class="topper-card">
        <div class="topper-badge">+<?= formatPrice($topper['price_addon']) ?></div>
        <div class="topper-img-wrap">
            <img src="<?= BASE_URL ?><?= htmlspecialchars($topper['image_url']) ?>" alt="<?= htmlspecialchars($topper['name']) ?>">
        </div>
        <div class="topper-info">
            <h3 class="topper-name"><?= htmlspecialchars($topper['name']) ?></h3>
            <span class="topper-price">Add-on: $<?= number_format($topper['price_addon'], 2) ?></span>
        </div>
        <div class="topper-actions">
            <a href="#" class="action-btn btn-edit edit-btn" 
                data-id="<?= $topper['id'] ?>"
                data-name="<?= htmlspecialchars($topper['name']) ?>"
                data-price="<?= $topper['price_addon'] ?>"
                title="Edit Topper">
                <i class="fas fa-edit"></i>
            </a>
            <a href="toppers.php?delete=<?= $topper['id'] ?>" 
                onclick="return confirm('Delete this topper? This cannot be undone.');" 
                class="action-btn btn-delete"
                title="Delete Topper">
                <i class="fas fa-trash"></i>
            </a>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (count($toppers) === 0): ?>
        <div style="grid-column: 1/-1; text-align:center; padding: 5rem; background:#fff; border-radius:16px; border:2px dashed #eee;">
            <i class="fas fa-shapes" style="font-size:3rem; color:#eee; margin-bottom:1rem;"></i>
            <p style="color:#999;">No candle toppers created yet. Click "Add New Shape" to get started.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div id="add-modal" class="admin-modal" style="display:none; align-items:center; justify-content:center; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div class="admin-modal-content" style="background:#fff; width:100%; max-width:500px; padding:2.5rem; border-radius:20px; position:relative; box-shadow:0 20px 50px rgba(0,0,0,0.2);">
        <span onclick="document.getElementById('add-modal').style.display='none'" class="admin-modal-close" style="position:absolute; right:1.5rem; top:1rem; font-size:2rem; cursor:pointer; color:#999;">&times;</span>
        <h3 style="margin-bottom:2rem; font-family:var(--font-heading); font-size:1.8rem; text-align:center;">New Candle Topper</h3>
        
        <form action="toppers" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="display:block; font-weight:bold; margin-bottom:0.5rem; color:var(--primary-color);">Shape Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Heart Shape" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px;">
            </div>
            
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="display:block; font-weight:bold; margin-bottom:0.5rem; color:var(--primary-color);">Price Add-on ($)</label>
                <input type="number" step="0.01" name="price_addon" class="form-control" value="0.00" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px;">
            </div>
            
            <div class="form-group" style="margin-bottom:2rem;">
                <label style="display:block; font-weight:bold; margin-bottom:0.5rem; color:var(--primary-color);">Shape Image (PNG recommended)</label>
                <div style="border:2px dashed #eee; padding:1.5rem; text-align:center; border-radius:12px; background:#fbfbfb;">
                    <input type="file" name="image" class="form-control" accept="image/*" required style="border:none; padding:0; background:transparent;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%; padding:1rem; border-radius:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase;">Create Topper</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="admin-modal" style="display:none; align-items:center; justify-content:center; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div class="admin-modal-content" style="background:#fff; width:100%; max-width:500px; padding:2.5rem; border-radius:20px; position:relative; box-shadow:0 20px 50px rgba(0,0,0,0.2);">
        <span onclick="document.getElementById('edit-modal').style.display='none'" class="admin-modal-close" style="position:absolute; right:1.5rem; top:1rem; font-size:2rem; cursor:pointer; color:#999;">&times;</span>
        <h3 style="margin-bottom:2rem; font-family:var(--font-heading); font-size:1.8rem; text-align:center;">Edit Topper</h3>
        
        <form action="toppers" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="display:block; font-weight:bold; margin-bottom:0.5rem; color:var(--primary-color);">Shape Name</label>
                <input type="text" name="name" id="edit-name" class="form-control" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px;">
            </div>
            
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="display:block; font-weight:bold; margin-bottom:0.5rem; color:var(--primary-color);">Price Add-on ($)</label>
                <input type="number" step="0.01" name="price_addon" id="edit-price" class="form-control" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px;">
            </div>
            
            <div class="form-group" style="margin-bottom:2rem;">
                <label style="display:block; font-weight:bold; margin-bottom:0.5rem; color:var(--primary-color);">Update Image (Optional)</label>
                <div style="border:2px dashed #eee; padding:1.5rem; text-align:center; border-radius:12px; background:#fbfbfb;">
                    <input type="file" name="image" class="form-control" accept="image/*" style="border:none; padding:0; background:transparent;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%; padding:1rem; border-radius:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase;">Update Topper</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('edit-id').value = this.getAttribute('data-id');
        document.getElementById('edit-name').value = this.getAttribute('data-name');
        document.getElementById('edit-price').value = this.getAttribute('data-price');
        document.getElementById('edit-modal').style.display = 'flex';
    });
});

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.className === 'admin-modal') {
        event.target.style.display = "none";
    }
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
