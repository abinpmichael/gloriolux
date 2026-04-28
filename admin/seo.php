<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . BASE_URL . 'login.php'); exit; }
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $stmt = $pdo->prepare('UPDATE seo_meta SET meta_title=?, meta_description=?, meta_keywords=? WHERE id=?');
        $stmt->execute([$_POST['meta_title'], $_POST['meta_description'], $_POST['meta_keywords'], (int)$_POST['id']]);
        header('Location: seo.php?success=1'); exit;
    }
}

$stmt = $pdo->query('SELECT * FROM seo_meta ORDER BY page_path ASC');
$seo_pages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SEO Management | Admin</title>
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
</head>
<body>
<div class="admin-layout">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Manage Global SEO Metadata</h2>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;border-radius:5px;margin-bottom:1rem;">SEO updated successfully!</div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Page / Route</th>
                    <th>Meta Title</th>
                    <th>Meta Description</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($seo_pages as $s): ?>
                <tr>
                    <td style="font-family: monospace;">/<?= $s['page_path'] ?></td>
                    <td style="font-weight:bold;"><?= htmlspecialchars($s['meta_title']) ?></td>
                    <td><?= substr(htmlspecialchars($s['meta_description']), 0, 50) ?>...</td>
                    <td>
                        <a href="#" onclick="openEdit(<?= $s['id'] ?>, '<?= $s['page_path'] ?>', '<?= addslashes($s['meta_title']) ?>', `<?= str_replace('`', '\`', $s['meta_description']) ?>`, `<?= str_replace('`', '\`', $s['meta_keywords']) ?>`)" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;"><i class="fas fa-edit"></i> Edit SEO</a>
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
        <h3 style="margin-bottom:1.5rem; font-family:var(--font-heading);">Edit SEO Metadata</h3>
        <p style="color: #666; margin-bottom: 1rem;">Editing SEO for: <strong id="display-path"></strong></p>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-group"><label>Meta Title (Max 60 chars recommended)</label><input type="text" name="meta_title" id="edit-title" class="form-control" required></div>
            <div class="form-group"><label>Meta Description (Max 160 chars recommended)</label><textarea name="meta_description" id="edit-desc" class="form-control" rows="3"></textarea></div>
            <div class="form-group"><label>Meta Keywords (Comma separated)</label><textarea name="meta_keywords" id="edit-keywords" class="form-control" rows="2"></textarea></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Save SEO Data</button>
        </form>
    </div>
</div>

<script>
function openEdit(id, path, title, desc, keywords) {
    document.getElementById('edit-id').value = id;
    document.getElementById('display-path').innerText = '/' + path;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-desc').value = desc;
    document.getElementById('edit-keywords').value = keywords;
    document.getElementById('editModal').style.display = 'block';
}
</script>
</body>
</html>
