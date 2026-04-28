<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . BASE_URL . 'login.php'); exit; }
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
    
    // Handle image upload logic (simplified for now, keep existing if not changed)
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog CMS | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .admin-layout {display:flex; min-height:100vh;}
        .admin-sidebar {width:250px; background:var(--primary-color); color:#fff; padding:2rem 1rem; display:flex; flex-direction:column;}
        .admin-main {flex:1; padding:2rem; background:#f4f6f8;}
        .data-table {width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.05);}
        .data-table th, .data-table td {padding:1rem; border-bottom:1px solid #eee; text-align: left;}
        .admin-sidebar a { display: block; padding: 1rem; color: #ccc; border-radius: 8px; margin-bottom: 0.5rem; text-decoration:none; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background-color: rgba(255,255,255,0.1); color: #fff; }
        .admin-logo { font-family: var(--font-heading); font-size: 1.5rem; text-align: center; margin-bottom: 3rem; color: #fff; }
        .modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); overflow-y: auto;}
        .modal-content { background:#fff; margin:5% auto; padding:2rem; border-radius:12px; width:90%; max-width:800px; position:relative; }
        .close-modal { position:absolute; right:20px; top:20px; cursor:pointer; font-size:1.5rem; }
    </style>
    <link rel="icon" href="<?= BASE_URL ?>assets/img/logo.png" type="image/png">
</head>
<body>
<div class="admin-layout">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Manage Blog Posts</h2>
            <button onclick="openAdd()" class="btn btn-primary"><i class="fas fa-plus"></i> Add Post</button>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;">Blog updated successfully!</div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Published</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($blogs as $b): ?>
                <tr>
                    <td>
                        <?php if($b['image_url']): ?>
                            <img src="<?= BASE_URL ?><?= htmlspecialchars($b['image_url']) ?>" style="width: 80px; height: 50px; object-fit: cover; border-radius: 5px;">
                        <?php else: ?>
                            <div style="width:80px; height:50px; background:#eee; border-radius:5px;"></div>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:bold;"><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= date('M j, Y', strtotime($b['created_at'])) ?></td>
                    <td>
                        <a href="#" onclick="openEdit(<?= $b['id'] ?>, '<?= addslashes($b['title']) ?>', '<?= addslashes($b['slug']) ?>', `<?= str_replace('`', '\`', $b['excerpt']) ?>`, `<?= str_replace('`', '\`', $b['content']) ?>`, '<?= $b['image_url'] ?>', '<?= addslashes((string)$b['meta_title']) ?>', `<?= str_replace('`', '\`', (string)$b['meta_description']) ?>`, `<?= str_replace('`', '\`', (string)$b['meta_keywords']) ?>`)" style="color:var(--secondary-color); margin-right:10px;"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?= $b['id'] ?>" onclick="return confirm('Delete this post?');" style="color:#ff6b6b;"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($blogs) == 0): ?>
                    <tr><td colspan="4" style="text-align: center;">No blog posts found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="blogModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="document.getElementById('blogModal').style.display='none'">&times;</span>
        <h3 id="modal-title" style="margin-bottom:1.5rem; font-family:var(--font-heading);">Add Blog Post</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="modal-action" value="add">
            <input type="hidden" name="id" id="edit-id">
            <input type="hidden" name="current_image" id="edit-current-image">
            
            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;"><label>Post Title</label><input type="text" name="title" id="edit-title" class="form-control" required></div>
                <div class="form-group" style="flex:1;"><label>URL Slug (e.g. my-first-post)</label><input type="text" name="slug" id="edit-slug" class="form-control" required></div>
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

function openEdit(id, title, slug, excerpt, content, img, mt, md, mk) {
    document.getElementById('modal-title').innerText = 'Edit Blog Post';
    document.getElementById('modal-action').value = 'edit';
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-slug').value = slug;
    document.getElementById('edit-excerpt').value = excerpt;
    document.getElementById('edit-content').value = content;
    document.getElementById('edit-current-image').value = img;
    document.getElementById('edit-meta-title').value = mt;
    document.getElementById('edit-meta-desc').value = md;
    document.getElementById('edit-meta-keywords').value = mk;
    document.getElementById('modal-btn').innerText = 'Update Post';
    document.getElementById('blogModal').style.display = 'block';
}
</script>
</body>
</html>
