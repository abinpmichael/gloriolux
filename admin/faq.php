<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: /Gloriolux/login.php'); exit; }
require_once '../includes/db.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FAQ CMS | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Gloriolux/assets/css/style.css">
    <style>
        .admin-layout {display:flex; min-height:100vh;}
        .admin-sidebar {width:250px; background:var(--primary-color); color:#fff; padding:2rem 1rem; display:flex; flex-direction:column;}
        .admin-main {flex:1; padding:2rem; background:#f4f6f8;}
        .data-table {width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.05);}
        .data-table th, .data-table td {padding:1rem; border-bottom:1px solid #eee; text-align: left;}
        .admin-sidebar a { display: block; padding: 1rem; color: #ccc; border-radius: 8px; margin-bottom: 0.5rem; text-decoration:none; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background-color: rgba(255,255,255,0.1); color: #fff; }
        .admin-logo { font-family: var(--font-heading); font-size: 1.5rem; text-align: center; margin-bottom: 3rem; color: #fff; }
        .modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); }
        .modal-content { background:#fff; margin:10% auto; padding:2rem; border-radius:12px; width:100%; max-width:600px; position:relative; }
        .close-modal { position:absolute; right:20px; top:20px; cursor:pointer; font-size:1.5rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
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
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
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
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
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
</body>
</html>
