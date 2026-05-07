<?php
session_start();
require_once '../includes/db.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . BASE_URL . 'login.php'); exit; }

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
$admin_page_title = 'Pages CMS';
require_once 'includes/admin_header.php';
?>
<div class="admin-page-header">
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

<!-- Edit Modal -->
<div id="editModal" class="admin-modal">
    <div class="admin-modal-content">
        <span class="admin-modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
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
<?php require_once 'includes/admin_footer.php'; ?>
