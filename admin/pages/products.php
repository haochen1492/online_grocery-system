<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$db = getDB();
$page_title = 'Manage Products';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = sanitize($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $sale_price = $_POST['sale_price'] ? (float)$_POST['sale_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $unit = sanitize($_POST['unit'] ?? 'piece');
    $status = $_POST['status'] ?? 'active';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

    if ($action === 'add') {
        $existing = $db->query("SELECT id FROM products WHERE slug='$slug'")->fetch_assoc();
        if ($existing) $slug .= '-' . time();
        $stmt = $db->prepare("INSERT INTO products (category_id,name,slug,description,price,sale_price,stock,unit,status,featured) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("issssdissi", $category_id,$name,$slug,$description,$price,$sale_price,$stock,$unit,$status,$featured);
        $stmt->execute();
        redirect('products.php', 'Product added successfully!');
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,sale_price=?,stock=?,unit=?,status=?,featured=? WHERE id=?");
        $stmt->bind_param("issddiisii", $category_id,$name,$description,$price,$sale_price,$stock,$unit,$status,$featured,$id);
        $stmt->execute();
        redirect('products.php', 'Product updated successfully!');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM products WHERE id=$id");
    redirect('products.php', 'Product deleted.', 'info');
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $db->query("UPDATE products SET status=IF(status='active','inactive','active') WHERE id=$id");
    redirect('products.php', 'Product status updated.');
}

$search = sanitize($_GET['search'] ?? '');
$filter_cat = (int)($_GET['category'] ?? 0);
$filter_status = $_GET['status'] ?? '';
$where = "WHERE 1";
if ($search) $where .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
if ($filter_cat) $where .= " AND p.category_id=$filter_cat";
if ($filter_status) $where .= " AND p.status='$filter_status'";

$products = $db->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id $where ORDER BY p.created_at DESC");
$total = $db->query("SELECT COUNT(*) as cnt FROM products p $where")->fetch_assoc()['cnt'];

$categories = $db->query("SELECT * FROM categories WHERE status='active' ORDER BY name");
$cats = [];
while ($c = $categories->fetch_assoc()) $cats[] = $c;

require_once '../includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <div style="font-size:13px;color:var(--text-muted)"><?= $total ?> products found</div>
    <button class="btn btn-primary" onclick="openModal('addModal')">＋ Add Product</button>
</div>

<div class="card">
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <div class="search-bar">
                <span>🔍</span>
                <input type="text" name="search" placeholder="Search products..." value="<?= $search ?>">
            </div>
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $filter_cat==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" <?= $filter_status==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= $filter_status==='inactive'?'selected':'' ?>>Inactive</option>
            </select>
            <?php if ($search || $filter_cat || $filter_status): ?>
            <a href="products.php" class="btn btn-ghost btn-sm">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Product</th><th>Category</th><th>Price</th>
                    <th>Stock</th><th>Unit</th><th>Featured</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $rows = [];
            while ($p = $products->fetch_assoc()) $rows[] = $p;
            if (empty($rows)):
            ?>
                <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">📦</div><p>No products found</p></div></td></tr>
            <?php else: $i=1; foreach ($rows as $p): ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $i++ ?></td>
                    <td>
                        <div style="font-weight:600"><?= sanitize($p['name']) ?></div>
                        <?php if ($p['sale_price']): ?>
                        <div style="font-size:11px;color:var(--green)">On sale!</div>
                        <?php endif; ?>
                    </td>
                    <td><span style="background:var(--bg3);padding:3px 8px;border-radius:5px;font-size:12px"><?= sanitize($p['cat_name']) ?></span></td>
                    <td>
                        <?php if ($p['sale_price']): ?>
                        <span style="text-decoration:line-through;color:var(--text-muted);font-size:12px"><?= formatMYR($p['price']) ?></span><br>
                        <strong style="color:var(--green)"><?= formatMYR($p['sale_price']) ?></strong>
                        <?php else: ?>
                        <strong><?= formatMYR($p['price']) ?></strong>
                        <?php endif; ?>
                    </td>
                    <td><span style="color:<?= $p['stock']<=5?'var(--red)':($p['stock']<=20?'var(--yellow)':'var(--green)') ?>;font-weight:600"><?= $p['stock'] ?></span></td>
                    <td style="color:var(--text-muted)"><?= $p['unit'] ?></td>
                    <td><?= $p['featured'] ? '⭐' : '—' ?></td>
                    <td><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-ghost btn-sm btn-icon" onclick="editProduct(<?= htmlspecialchars(json_encode($p)) ?>)">✏️</button>
                            <a href="products.php?toggle=<?= $p['id'] ?>" class="btn btn-ghost btn-sm btn-icon" onclick="return confirm('Toggle status?')">🔄</a>
                            <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete this product?')">🗑</a>
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
    <div class="modal modal-lg">
        <div class="modal-header">
            <span class="modal-title">Add New Product</span>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Organic Bananas">
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($cats as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Product description..."></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (RM) *</label>
                        <input type="number" name="price" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Sale Price (RM)</label>
                        <input type="number" name="sale_price" step="0.01" min="0" placeholder="Leave blank if no sale">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock *</label>
                        <input type="number" name="stock" min="0" required placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <select name="unit">
                            <option value="piece">Piece</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="g">Gram (g)</option>
                            <option value="liter">Liter</option>
                            <option value="pack">Pack</option>
                            <option value="bunch">Bunch</option>
                            <option value="dozen">Dozen</option>
                            <option value="loaf">Loaf</option>
                            <option value="head">Head</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;text-transform:none;font-size:14px">
                            <input type="checkbox" name="featured" style="width:auto">
                            Mark as Featured Product
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <span class="modal-title">Edit Product</span>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="e_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" id="e_name" required>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" id="e_cat" required>
                            <?php foreach ($cats as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="e_desc"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (RM) *</label>
                        <input type="number" name="price" id="e_price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Sale Price (RM)</label>
                        <input type="number" name="sale_price" id="e_sale" step="0.01" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock *</label>
                        <input type="number" name="stock" id="e_stock" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <select name="unit" id="e_unit">
                            <option value="piece">Piece</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="g">Gram (g)</option>
                            <option value="liter">Liter</option>
                            <option value="pack">Pack</option>
                            <option value="bunch">Bunch</option>
                            <option value="dozen">Dozen</option>
                            <option value="loaf">Loaf</option>
                            <option value="head">Head</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="e_status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;text-transform:none;font-size:14px">
                            <input type="checkbox" name="featured" id="e_featured" style="width:auto">
                            Mark as Featured Product
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>

<script>
function editProduct(p) {
    document.getElementById('e_id').value = p.id;
    document.getElementById('e_name').value = p.name;
    document.getElementById('e_cat').value = p.category_id;
    document.getElementById('e_desc').value = p.description || '';
    document.getElementById('e_price').value = p.price;
    document.getElementById('e_sale').value = p.sale_price || '';
    document.getElementById('e_stock').value = p.stock;
    document.getElementById('e_unit').value = p.unit;
    document.getElementById('e_status').value = p.status;
    document.getElementById('e_featured').checked = p.featured == 1;
    openModal('editModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>
