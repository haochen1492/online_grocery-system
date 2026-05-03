<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$db = getDB();
$page_title = 'Manage Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

    if ($action === 'add') {
        $existing = $db->query("SELECT id FROM categories WHERE slug='$slug'")->fetch_assoc();
        if ($existing) $slug .= '-' . time();
        $stmt = $db->prepare("INSERT INTO categories (name,slug,description,status) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $name,$slug,$description,$status);
        $stmt->execute();
        redirect('categories.php', 'Category added successfully!');
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE categories SET name=?,description=?,status=? WHERE id=?");
        $stmt->bind_param("sssi", $name,$description,$status,$id);
        $stmt->execute();
        redirect('categories.php', 'Category updated successfully!');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $prod_count = $db->query("SELECT COUNT(*) as c FROM products WHERE category_id=$id")->fetch_assoc()['c'];
    if ($prod_count > 0) {
        redirect('categories.php', "Cannot delete: $prod_count products use this category.", 'error');
    }
    $db->query("DELETE FROM categories WHERE id=$id");
    redirect('categories.php', 'Category deleted.', 'info');
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $db->query("UPDATE categories SET status=IF(status='active','inactive','active') WHERE id=$id");
    redirect('categories.php', 'Status updated.');
}

$categories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id=c.id) as product_count FROM categories c ORDER BY c.created_at DESC");

require_once '../includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <div style="font-size:13px;color:var(--text-muted)">Manage your store categories</div>
    <button class="btn btn-primary" onclick="openModal('addModal')">＋ Add Category</button>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Category</th><th>Slug</th><th>Description</th>
                    <th>Products</th><th>Created</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $rows = [];
            while ($c = $categories->fetch_assoc()) $rows[] = $c;
            if (empty($rows)):
            ?>
                <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🏷️</div><p>No categories yet</p></div></td></tr>
            <?php else:
            $emoji_map = ['fruits-vegetables'=>'🥦','dairy-eggs'=>'🥛','meat-seafood'=>'🥩','bakery'=>'🍞','beverages'=>'🥤','snacks'=>'🍿'];
            $i = 1;
            foreach ($rows as $c):
                $ico = $emoji_map[$c['slug']] ?? '🏷️';
            ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $i++ ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <span style="font-size:20px"><?= $ico ?></span>
                            <strong><?= sanitize($c['name']) ?></strong>
                        </div>
                    </td>
                    <td><code style="background:var(--bg3);padding:3px 8px;border-radius:4px;font-size:12px;color:var(--text-muted)"><?= $c['slug'] ?></code></td>
                    <td style="color:var(--text-muted);max-width:200px"><?= $c['description'] ? substr(sanitize($c['description']),0,60).'...' : '—' ?></td>
                    <td><a href="products.php?category=<?= $c['id'] ?>" style="color:var(--accent);font-weight:600"><?= $c['product_count'] ?> products</a></td>
                    <td style="color:var(--text-muted);font-size:12px"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                    <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-ghost btn-sm btn-icon" onclick="editCategory(<?= htmlspecialchars(json_encode($c)) ?>)">✏️</button>
                            <a href="categories.php?toggle=<?= $c['id'] ?>" class="btn btn-ghost btn-sm btn-icon" onclick="return confirm('Toggle status?')">🔄</a>
                            <a href="categories.php?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete this category?')">🗑</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add New Category</span>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Fruits & Vegetables">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Brief description..."></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Category</span>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_desc"></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(c) {
    document.getElementById('edit_id').value = c.id;
    document.getElementById('edit_name').value = c.name;
    document.getElementById('edit_desc').value = c.description || '';
    document.getElementById('edit_status').value = c.status;
    openModal('editModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>
