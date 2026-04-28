<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: /Gloriolux/login.php'); exit; }
require_once '../includes/db.php';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $stmt = $pdo->prepare('UPDATE pages SET title=?, content=? WHERE id=?');
        $stmt->execute([$_POST['title'], $_POST['content'], (int)$_POST['id']]);
        header('Location: pages.php?success=1'); exit;
    }
}

$stmt = $pdo->query('SELECT * FROM pages');
$pages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pages CMS | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Gloriolux/assets/css/style.css">
    <!-- Include basic WYSIWYG or let them use HTML for now -->
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
</head>
<body>
<div class="admin-layout">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Manage Dynamic Pages</h2>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;">Page updated successfully!</div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>URL Slug</th>
                    <th>Page Title</th>
                    <th>Last Updated</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pages as $p): ?>
                <tr>
                    <td style="font-family: monospace;">/<?= $p['slug'] ?>.php</td>
                    <td style="font-weight:bold;"><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= $p['updated_at'] ?></td>
                    <td>
                        <a href="#" onclick="openEdit(<?= $p['id'] ?>, '<?= addslashes($p['title']) ?>', `<?= str_replace('`', '\`', $p['content']) ?>`)" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;"><i class="fas fa-edit"></i> Edit Content</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h3 style="margin-bottom:1.5rem; font-family:var(--font-heading);">Edit Page Content</h3>
        <p style="color: #666; margin-bottom: 1rem;">Note: You can use HTML formatting (like &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, etc.) to style the page.</p>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-group"><label>Page Title (appears in Header)</label><input type="text" name="title" id="edit-title" class="form-control" required></div>
            <div class="form-group"><label>Page Content</label><textarea name="content" id="edit-content" class="form-control" rows="15" required style="font-family: monospace;"></textarea></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Save Page</button>
        </form>
    </div>
</div>

<script>
function openEdit(id, title, content) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-content').value = content;
    document.getElementById('editModal').style.display = 'block';
}
</script>
</body>
</html>
