<?php
session_start();
require_once '../includes/db.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . BASE_URL . 'login.php'); exit; }

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $stmt = $pdo->prepare('INSERT INTO faqs (question, answer, display_order) VALUES (?, ?, ?)');
        $stmt->execute([$_POST['question'], $_POST['answer'], (int)$_POST['display_order']]);
        header('Location: faq.php?success=1'); exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $stmt = $pdo->prepare('UPDATE faqs SET question=?, answer=?, display_order=? WHERE id=?');
        $stmt->execute([$_POST['question'], $_POST['answer'], (int)$_POST['display_order'], (int)$_POST['id']]);
        header('Location: faq.php?success=2'); exit;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM faqs WHERE id=?');
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: faq.php?success=3'); exit;
}

$stmt = $pdo->query('SELECT * FROM faqs ORDER BY display_order ASC');
$faqs = $stmt->fetchAll();
$admin_page_title = 'FAQ CMS';
require_once 'includes/admin_header.php';
?>
<div class="admin-page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Manage FAQs</h2>
    <button onclick="document.getElementById('addModal').style.display='block'" class="btn btn-primary"><i class="fas fa-plus"></i> Add FAQ</button>
</div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">Order</th>
                    <th>Question</th>
                    <th>Answer snippet</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($faqs as $f): ?>
                <tr>
                    <td><?= $f['display_order'] ?></td>
                    <td style="font-weight:bold;"><?= htmlspecialchars($f['question']) ?></td>
                    <td><?= substr(htmlspecialchars($f['answer']), 0, 80) ?>...</td>
                    <td>
                        <a href="#" onclick="openEdit(<?= $f['id'] ?>, '<?= addslashes($f['question']) ?>', '<?= addslashes($f['answer']) ?>', <?= $f['display_order'] ?>)" style="color:var(--secondary-color); margin-right:10px;"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?= $f['id'] ?>" onclick="return confirm('Delete this FAQ?');" style="color:#ff6b6b;"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

<!-- Add Modal -->
<div id="addModal" class="admin-modal">
    <div class="admin-modal-content">
        <span class="admin-modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h3 style="margin-bottom:1.5rem; font-family:var(--font-heading);">Add FAQ</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group"><label>Question</label><input type="text" name="question" class="form-control" required></div>
            <div class="form-group"><label>Answer</label><textarea name="answer" class="form-control" rows="5" required></textarea></div>
            <div class="form-group"><label>Display Order (1, 2, 3...)</label><input type="number" name="display_order" class="form-control" value="0" required></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Save FAQ</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="admin-modal">
    <div class="admin-modal-content">
        <span class="admin-modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h3 style="margin-bottom:1.5rem; font-family:var(--font-heading);">Edit FAQ</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-group"><label>Question</label><input type="text" name="question" id="edit-q" class="form-control" required></div>
            <div class="form-group"><label>Answer</label><textarea name="answer" id="edit-a" class="form-control" rows="5" required></textarea></div>
            <div class="form-group"><label>Display Order</label><input type="number" name="display_order" id="edit-o" class="form-control" required></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Update FAQ</button>
        </form>
    </div>
</div>

<script>
function openEdit(id, q, a, o) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-q').value = q;
    document.getElementById('edit-a').value = a;
    document.getElementById('edit-o').value = o;
    document.getElementById('editModal').style.display = 'block';
}
</script>
<?php require_once 'includes/admin_footer.php'; ?>
