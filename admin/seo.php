<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '../') . 'login.php'); 
    exit; 
}
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $stmt = $pdo->prepare('UPDATE seo_meta SET meta_title=?, meta_description=?, meta_keywords=?, schema_code=? WHERE id=?');
        $stmt->execute([
            $_POST['meta_title'], 
            $_POST['meta_description'], 
            $_POST['meta_keywords'], 
            $_POST['schema_code'], 
            (int)$_POST['id']
        ]);
        header('Location: seo.php?success=1'); exit;
    }
}

$stmt = $pdo->query('SELECT * FROM seo_meta ORDER BY page_path ASC');
$seo_pages = $stmt->fetchAll();

$admin_page_title = 'SEO Manager';
require_once 'includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2>Search Engine Optimization</h2>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> SEO data and Schema code updated successfully!</div>
<?php endif; ?>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Page / Route</th>
                <th>Meta Title</th>
                <th>Meta Description</th>
                <th>Schema</th>
                <th style="width: 120px; text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($seo_pages as $s): ?>
            <tr>
                <td style="font-family: monospace; color: var(--secondary-color);">/<?= htmlspecialchars($s['page_path']) ?></td>
                <td style="font-weight:bold;"><?= htmlspecialchars($s['meta_title']) ?></td>
                <td style="font-size: 0.85rem; color: var(--text-light);"><?= substr(htmlspecialchars($s['meta_description']), 0, 60) ?>...</td>
                <td>
                    <?php if(!empty($s['schema_code'])): ?>
                        <span class="status-badge" style="background:#e8f5e9; color:#2e7d32;"><i class="fas fa-code"></i> Active</span>
                    <?php else: ?>
                        <span class="status-badge" style="background:#f5f5f5; color:#9e9e9e;">None</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;">
                    <button onclick="openEdit(<?= $s['id'] ?>, '<?= $s['page_path'] ?>', '<?= addslashes($s['meta_title']) ?>', `<?= str_replace('`', '\`', $s['meta_description']) ?>`, `<?= str_replace('`', '\`', $s['meta_keywords']) ?>`, `<?= str_replace('`', '\`', $s['schema_code']) ?>`)" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit SEO Modal -->
<div id="seoModal" class="admin-modal">
    <div class="admin-modal-content" style="max-width: 800px;">
        <span class="admin-modal-close" onclick="document.getElementById('seoModal').style.display='none'">&times;</span>
        <h3 style="margin-bottom:1.5rem; font-family:var(--font-heading);">Edit SEO & Schema</h3>
        <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.9rem;">Optimizing: <strong id="display-path" style="color: var(--primary-color);"></strong></p>
        
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            
            <div class="form-group">
                <label style="font-weight:bold; display:block; margin-bottom:0.5rem;">Meta Title</label>
                <input type="text" name="meta_title" id="edit-title" class="form-control" required placeholder="e.g. Gloriolux | Luxury Soy Candles">
                <small style="color:#999;">Optimal length: 50-60 characters.</small>
            </div>
            
            <div class="form-group">
                <label style="font-weight:bold; display:block; margin-bottom:0.5rem;">Meta Description</label>
                <textarea name="meta_description" id="edit-desc" class="form-control" rows="3" placeholder="Enter a compelling description for search results..."></textarea>
                <small style="color:#999;">Optimal length: 150-160 characters.</small>
            </div>
            
            <div class="form-group">
                <label style="font-weight:bold; display:block; margin-bottom:0.5rem;">Meta Keywords</label>
                <textarea name="meta_keywords" id="edit-keywords" class="form-control" rows="2" placeholder="candles, luxury, soy wax..."></textarea>
            </div>

            <div class="form-group">
                <label style="font-weight:bold; display:block; margin-bottom:0.5rem;">Structured Data (JSON-LD Schema)</label>
                <textarea name="schema_code" id="edit-schema" class="form-control" rows="6" placeholder='{ "@context": "https://schema.org", "@type": "Organization", ... }' style="font-family: monospace; font-size: 0.85rem; background: #f9f9f9;"></textarea>
                <small style="color:#999;">Paste your JSON-LD schema code here. It will be placed in the &lt;head&gt; of this page.</small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 1rem;">Update SEO & Schema</button>
        </form>
    </div>
</div>

<script>
function openEdit(id, path, title, desc, keywords, schema) {
    document.getElementById('edit-id').value = id;
    document.getElementById('display-path').innerText = '/' + path;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-desc').value = desc;
    document.getElementById('edit-keywords').value = keywords;
    document.getElementById('edit-schema').value = schema;
    document.getElementById('seoModal').style.display = 'block';
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
