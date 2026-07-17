<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}
require_once '../includes/db.php';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: categories.php?success=1"); exit;
    }
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        header("Location: categories.php?success=2"); exit;
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Check if category is in use
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            header("Location: categories.php?error=linked"); exit;
        }
        
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        header("Location: categories.php?success=3"); exit;
    } catch (Exception $e) {
        header("Location: categories.php?error=" . urlencode($e->getMessage())); exit;
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();

$admin_page_title = 'Manage Categories';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Product Categories</h2>
    <button onclick="document.getElementById('add-modal').style.display='block'" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Category
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success">
        <i class="fas fa-check-circle"></i>
        <?php
            if ($_GET['success']==1) echo 'Category added successfully!';
            elseif ($_GET['success']==2) echo 'Category updated successfully!';
            elseif ($_GET['success']==3) echo 'Category deleted successfully!';
        ?>
    </div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
    <div class="admin-alert admin-alert-danger" style="background:#f8d7da; color:#721c24; padding:1rem; border-radius:8px; margin-bottom:1.5rem; border-left:5px solid #dc3545;">
        <i class="fas fa-exclamation-circle"></i>
        <?php 
            if($_GET['error'] == 'linked') echo 'Cannot delete category: It is currently linked to existing products.';
            else echo 'Error: ' . htmlspecialchars($_GET['error']);
        ?>
    </div>
<?php endif; ?>

<div class="settings-card">
    <table class="data-table" style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="text-align:left; border-bottom:2px solid #eee;">
                <th style="padding:1rem;">ID</th>
                <th style="padding:1rem;">Category Name</th>
                <th style="padding:1rem; text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($categories as $cat): ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:1rem;"><?= $cat['id'] ?></td>
                <td style="padding:1rem; font-weight:600;"><?= htmlspecialchars($cat['name']) ?></td>
                <td style="padding:1rem; text-align:right;">
                    <button onclick="openEditModal(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>')" style="background:none; border:none; color:var(--secondary-color); cursor:pointer; font-size:1.1rem; margin-right:15px;" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <a href="categories.php?delete=<?= $cat['id'] ?>" onclick="return confirm('Delete this category?')" style="color:#ff6b6b; font-size:1.1rem;" title="Delete">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div id="add-modal" class="admin-modal" style="display:none; position:fixed; z-index:2000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; width:400px; margin:10% auto; padding:2rem; border-radius:12px; position:relative;">
        <span onclick="this.parentElement.parentElement.style.display='none'" style="position:absolute; right:1.5rem; top:1rem; cursor:pointer; font-size:1.5rem;">&times;</span>
        <h3 style="margin-bottom:1.5rem;">Add New Category</h3>
        <form action="categories" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Luxury Candles">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">Save Category</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="admin-modal" style="display:none; position:fixed; z-index:2000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; width:400px; margin:10% auto; padding:2rem; border-radius:12px; position:relative;">
        <span onclick="this.parentElement.parentElement.style.display='none'" style="position:absolute; right:1.5rem; top:1rem; cursor:pointer; font-size:1.5rem;">&times;</span>
        <h3 style="margin-bottom:1.5rem;">Edit Category</h3>
        <form action="categories" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="name" id="edit-name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">Update Category</button>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-modal').style.display = 'block';
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
