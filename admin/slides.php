<?php
session_start();
require_once '../includes/db.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . BASE_URL . 'login.php'); exit; }

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_url = 'assets/img/logo.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
            $image_url = 'uploads/' . $filename;
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $stmt = $pdo->prepare('INSERT INTO slides (title, subtitle, button_text, button_url, display_order, image_url) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$_POST['title'], $_POST['subtitle'], $_POST['button_text'], $_POST['button_url'], (int)$_POST['display_order'], $image_url]);
        header('Location: slides.php?success=added'); exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $image_url = $_POST['current_image'];
        }
        $stmt = $pdo->prepare('UPDATE slides SET title=?, subtitle=?, button_text=?, button_url=?, display_order=?, image_url=? WHERE id=?');
        $stmt->execute([$_POST['title'], $_POST['subtitle'], $_POST['button_text'], $_POST['button_url'], (int)$_POST['display_order'], $image_url, (int)$_POST['id']]);
        header('Location: slides.php?success=1'); exit;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM slides WHERE id=?');
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: slides.php?success=deleted'); exit;
}

$stmt = $pdo->query('SELECT * FROM slides ORDER BY display_order ASC');
$slides = $stmt->fetchAll();
$admin_page_title = 'Home Slider CMS';
require_once 'includes/admin_header.php';
?>
<div class="admin-page-header">
    <h2>Manage Homepage Slider</h2>
    <button onclick="openAdd()" class="btn btn-primary"><i class="fas fa-plus"></i> Add Slide</button>
</div>

        
        <?php if(isset($_GET['success'])): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;">
                <?= $_GET['success'] == 'deleted' ? 'Slide deleted successfully.' : 'Slide updated successfully!' ?>
            </div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Button Text</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($slides as $s): ?>
                <tr>
                    <td><?= $s['display_order'] ?></td>
                    <td><img src="<?= BASE_URL ?><?= htmlspecialchars($s['image_url']) ?>" style="width: 100px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                    <td style="font-weight:bold;"><?= htmlspecialchars($s['title']) ?></td>
                    <td><?= htmlspecialchars($s['button_text']) ?></td>
                    <td>
                        <a href="#" onclick="openEdit(<?= $s['id'] ?>, '<?= addslashes($s['title']) ?>', `<?= addslashes($s['subtitle']) ?>`, '<?= addslashes($s['button_text']) ?>', '<?= addslashes($s['button_url']) ?>', <?= $s['display_order'] ?>, '<?= $s['image_url'] ?>')" style="color:var(--secondary-color); margin-right:10px;"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?= $s['id'] ?>" onclick="return confirm('Are you sure you want to delete this slide?');" style="color:#ff6b6b;"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

<!-- Modal -->
<div id="editModal" class="admin-modal">
    <div class="admin-modal-content">
        <span class="admin-modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h3 id="modal-title" style="margin-bottom:1.5rem; font-family:var(--font-heading);">Edit Slide</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="modal-action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <input type="hidden" name="current_image" id="edit-current-image">
            <div class="form-group"><label>Hero Title</label><input type="text" name="title" id="edit-title" class="form-control" required></div>
            <div class="form-group"><label>Subtitle / Description</label><textarea name="subtitle" id="edit-sub" class="form-control" rows="3"></textarea></div>
            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;"><label>Button Text</label><input type="text" name="button_text" id="edit-btnt" class="form-control"></div>
                <div class="form-group" style="flex:1;"><label>Button URL</label><input type="text" name="button_url" id="edit-btnu" class="form-control"></div>
            </div>
            <div class="form-group"><label>Display Order</label><input type="number" name="display_order" id="edit-order" class="form-control"></div>
            <div class="form-group"><label>Slide Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Save Slide</button>
        </form>
    </div>
</div>

<script>
function openAdd() {
    document.getElementById('modal-title').innerText = 'Add Slide';
    document.getElementById('modal-action').value = 'add';
    document.getElementById('edit-id').value = '';
    document.getElementById('edit-title').value = '';
    document.getElementById('edit-sub').value = '';
    document.getElementById('edit-btnt').value = '';
    document.getElementById('edit-btnu').value = '';
    document.getElementById('edit-order').value = '';
    document.getElementById('edit-current-image').value = '';
    document.getElementById('editModal').style.display = 'block';
}

function openEdit(id, title, sub, btnt, btnu, order, img) {
    document.getElementById('modal-title').innerText = 'Edit Slide';
    document.getElementById('modal-action').value = 'edit';
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-sub').value = sub;
    document.getElementById('edit-btnt').value = btnt;
    document.getElementById('edit-btnu').value = btnu;
    document.getElementById('edit-order').value = order;
    document.getElementById('edit-current-image').value = img;
    document.getElementById('editModal').style.display = 'block';
}
</script>
<?php require_once 'includes/admin_footer.php'; ?>
