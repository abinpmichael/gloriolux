<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '../') . 'login.php'); 
    exit; 
}
require_once '../includes/db.php';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'])));
    $excerpt = $_POST['excerpt'];
    $content = $_POST['content'];
    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_keywords = $_POST['meta_keywords'];
    
    $image_url = $_POST['current_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = 'uploads/' . $file_name;
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $stmt = $pdo->prepare('INSERT INTO blogs (title, slug, excerpt, content, image_url, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $slug, $excerpt, $content, $image_url, $meta_title, $meta_description, $meta_keywords]);
        header('Location: blogs.php?success=1'); exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $stmt = $pdo->prepare('UPDATE blogs SET title=?, slug=?, excerpt=?, content=?, image_url=?, meta_title=?, meta_description=?, meta_keywords=? WHERE id=?');
        $stmt->execute([$title, $slug, $excerpt, $content, $image_url, $meta_title, $meta_description, $meta_keywords, (int)$_POST['id']]);
        header('Location: blogs.php?success=2'); exit;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM blogs WHERE id=?');
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: blogs.php?success=3'); exit;
}

$stmt = $pdo->query('SELECT * FROM blogs ORDER BY created_at DESC');
$blogs = $stmt->fetchAll();
$admin_page_title = 'Blog CMS';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Manage Blog Posts</h2>
    <button onclick="openAdd()" class="btn btn-primary"><i class="fas fa-plus"></i> Add Post</button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Blog updated successfully!</div>
<?php endif; ?>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Post Title</th>
                <th style="width: 120px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($blogs as $b): ?>
            <tr>
                <td data-label="Title" style="font-weight:bold;"><?= htmlspecialchars($b['title']) ?></td>
                <td data-label="Actions" style="text-align: right;">
                    <a href="#" class="edit-blog-btn" 
                        data-id="<?= $b['id'] ?>"
                        data-title="<?= htmlspecialchars($b['title']) ?>"
                        data-slug="<?= htmlspecialchars($b['slug']) ?>"
                        data-excerpt="<?= htmlspecialchars($b['excerpt']) ?>"
                        data-content="<?= htmlspecialchars($b['content']) ?>"
                        data-image="<?= htmlspecialchars($b['image_url']) ?>"
                        data-meta-title="<?= htmlspecialchars((string)$b['meta_title']) ?>"
                        data-meta-desc="<?= htmlspecialchars((string)$b['meta_description']) ?>"
                        data-meta-keywords="<?= htmlspecialchars((string)$b['meta_keywords']) ?>"
                        style="color:var(--secondary-color); margin-right:10px;">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="?delete=<?= $b['id'] ?>" onclick="return confirm('Delete this post?');" style="color:#ff6b6b;"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($blogs) == 0): ?>
                <tr><td colspan="2" style="text-align: center;">No blog posts found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Blog Modal (Add/Edit) -->
<div id="blogModal" class="admin-modal">
    <div class="admin-modal-content" style="max-width:800px; width: 95%; margin: 2rem auto;">
        <span class="admin-modal-close" onclick="document.getElementById('blogModal').style.display='none'">&times;</span>

        <h3 id="modal-title" style="margin-bottom:1.5rem; font-family:var(--font-heading);">Add Blog Post</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="modal-action" value="add">
            <input type="hidden" name="id" id="edit-id">
            <input type="hidden" name="current_image" id="edit-current-image">
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 250px;"><label>Post Title</label><input type="text" name="title" id="edit-title" class="form-control" required></div>
                <div class="form-group" style="flex: 1; min-width: 250px;"><label>URL Slug (e.g. my-first-post)</label><input type="text" name="slug" id="edit-slug" class="form-control" required></div>
            </div>
            <div class="form-group"><label>Excerpt (Short Description)</label><textarea name="excerpt" id="edit-excerpt" class="form-control" rows="2"></textarea></div>
            <div class="form-group"><label>Main Content (HTML allowed)</label><textarea name="content" id="edit-content" class="form-control" rows="10" required></textarea></div>
            <div class="form-group"><label>Featured Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
            
            <h4 style="margin: 1.5rem 0 1rem; color: var(--primary-color);">SEO Settings</h4>
            <div class="form-group"><label>Meta Title</label><input type="text" name="meta_title" id="edit-meta-title" class="form-control"></div>
            <div class="form-group"><label>Meta Description</label><textarea name="meta_description" id="edit-meta-desc" class="form-control" rows="2"></textarea></div>
            <div class="form-group"><label>Meta Keywords</label><textarea name="meta_keywords" id="edit-meta-keywords" class="form-control" rows="2"></textarea></div>
            
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 1rem;" id="modal-btn">Save Post</button>
        </form>
    </div>
</div>

<script>
function openAdd() {
    document.getElementById('modal-title').innerText = 'Add Blog Post';
    document.getElementById('modal-action').value = 'add';
    document.getElementById('edit-id').value = '';
    document.getElementById('edit-title').value = '';
    document.getElementById('edit-slug').value = '';
    document.getElementById('edit-excerpt').value = '';
    document.getElementById('edit-content').value = '';
    document.getElementById('edit-current-image').value = '';
    document.getElementById('edit-meta-title').value = '';
    document.getElementById('edit-meta-desc').value = '';
    document.getElementById('edit-meta-keywords').value = '';
    document.getElementById('modal-btn').innerText = 'Publish Post';
    document.getElementById('blogModal').style.display = 'block';
}

document.querySelectorAll('.edit-blog-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('modal-title').innerText = 'Edit Blog Post';
        document.getElementById('modal-action').value = 'edit';
        document.getElementById('edit-id').value = this.dataset.id;
        document.getElementById('edit-title').value = this.dataset.title;
        document.getElementById('edit-slug').value = this.dataset.slug;
        document.getElementById('edit-excerpt').value = this.dataset.excerpt;
        document.getElementById('edit-content').value = this.dataset.content;
        document.getElementById('edit-current-image').value = this.dataset.image;
        document.getElementById('edit-meta-title').value = this.dataset.metaTitle;
        document.getElementById('edit-meta-desc').value = this.dataset.metaDesc;
        document.getElementById('edit-meta-keywords').value = this.dataset.metaKeywords;
        document.getElementById('modal-btn').innerText = 'Update Post';
        document.getElementById('blogModal').style.display = 'block';
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
