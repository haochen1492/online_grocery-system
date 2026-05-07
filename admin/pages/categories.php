<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();
$page_title = 'Manage Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name   = sanitize($_POST['category_name'] ?? '');

    if (!$name) {
        redirect('categories.php', 'Category name is required.', 'error');
    }

    if ($action === 'add') {
        $stmt = $db->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) redirect('categories.php', 'Category added successfully!');
        else redirect('categories.php', 'Category already exists.', 'error');
    } elseif ($action === 'edit') {
        $id = (int)$_POST['category_id'];
        $stmt = $db->prepare("UPDATE categories SET category_name=? WHERE category_id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        redirect('categories.php', 'Category updated!');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $used = $db->query("SELECT COUNT(*) c FROM products WHERE category_id=$id")->fetch_assoc()['c'];
    if ($used > 0) {
        redirect('categories.php', "Cannot delete: $used products use this category.", 'error');
    }
    $db->query("DELETE FROM categories WHERE category_id=$id");
    redirect('categories.php', 'Category deleted.', 'info');
}

$search = sanitize($_GET['search'] ?? '');
$where  = $search ? "WHERE c.category_name LIKE '%$search%'" : '';
$cats   = $db->query("
    SELECT c.*, COUNT(p.product_id) product_count
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.category_id
    $where
    GROUP BY c.category_id
    ORDER BY c.category_name
");
$rows = [];
while ($r = $cats->fetch_assoc()) $rows[] = $r;

require_once '../includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <p style="color:var(--text3);font-size:13px"><?= count($rows) ?> categories found</p>
    <button class="btn btn-primary" onclick="openModal('addModal')">＋ Add Category</button>
</div>

<div class="card">
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center">
            <div class="search-bar">
                <span>🔍</span>
                <input type="text" name="search" placeholder="Search categories..." value="<?= $search ?>">
            </div>
            <?php if ($search): ?><a href="categories.php" class="btn btn-ghost btn-sm">✕ Clear</a><?php endif; ?>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category Name</th>
                    <th>Products</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="4"><div class="empty-state"><div class="ei">🏷️</div><p>No categories found</p></div></td></tr>
            <?php else: $i=1; foreach ($rows as $c): ?>
            <tr>
                <td style="color:var(--text3);font-weight:500"><?= $i++ ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:36px;height:36px;background:var(--green-bg);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px">🏷️</div>
                        <strong style="font-size:14px"><?= sanitize($c['category_name']) ?></strong>
                    </div>
                </td>
                <td>
                    <a href="products.php?category=<?= $c['category_id'] ?>" style="color:var(--blue);font-weight:600">
                        <?= $c['product_count'] ?> products
                    </a>
                </td>
                <td>
                    <div style="display:flex;gap:7px">
                        <button class="btn btn-orange btn-sm btn-icon"
                            onclick="editCat(<?= $c['category_id'] ?>, '<?= addslashes(sanitize($c['category_name'])) ?>')">
                            ✏️ Edit
                        </button>
                        <a href="categories.php?delete=<?= $c['category_id'] ?>"
                           class="btn btn-danger btn-sm btn-icon"
                           onclick="return confirm('Delete category \'<?= addslashes(sanitize($c['category_name'])) ?>\'?')">
                            🗑 Delete
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD MODAL -->
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
                    <input type="text" name="category_name" required placeholder="e.g. Fruits & Vegetables" autofocus>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Category</span>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="category_id" id="edit_cat_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="category_name" id="edit_cat_name" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCat(id, name) {
    document.getElementById('edit_cat_id').value   = id;
    document.getElementById('edit_cat_name').value = name;
    openModal('editModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>
