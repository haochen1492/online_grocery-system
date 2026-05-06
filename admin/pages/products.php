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
    $image = sanitize($_POST['image'] ?? '');
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

    if ($action === 'add') {
        $existing = $db->query("SELECT id FROM products WHERE slug='$slug'")->fetch_assoc();
        if ($existing) $slug .= '-' . time();
        $stmt = $db->prepare("INSERT INTO products (category_id,name,slug,description,price,sale_price,stock,unit,image,status,featured) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("isssddiissi", $category_id,$name,$slug,$description,$price,$sale_price,$stock,$unit,$image,$status,$featured);
        $stmt->execute();
        redirect('products.php', 'Product added successfully!');
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,sale_price=?,stock=?,unit=?,image=?,status=?,featured=? WHERE id=?");
        $stmt->bind_param("issddiissii", $category_id,$name,$description,$price,$sale_price,$stock,$unit,$image,$status,$featured,$id);
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

// View toggle: table or grid
$view = $_GET['view'] ?? 'table';

// Filters
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

$rows = [];
while ($p = $products->fetch_assoc()) $rows[] = $p;

require_once '../includes/header.php';
?>

<style>
/* Product Grid View */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    padding: 20px;
}

.product-card {
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.2s;
    position: relative;
}

.product-card:hover {
    border-color: rgba(63,185,80,0.4);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

.product-card-img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    display: block;
    background: var(--bg2);
}

.product-card-img-placeholder {
    width: 100%;
    height: 160px;
    background: var(--bg2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
}

.product-card-body {
    padding: 12px;
}

.product-card-cat {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--green);
    font-weight: 600;
    margin-bottom: 4px;
}

.product-card-name {
    font-weight: 700;
    font-size: 13.5px;
    margin-bottom: 6px;
    line-height: 1.3;
    font-family: var(--font-head);
}

.product-card-price {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
}

.product-card-price .current {
    font-weight: 800;
    font-size: 15px;
    color: var(--green);
}

.product-card-price .original {
    font-size: 11px;
    color: var(--text-muted);
    text-decoration: line-through;
}

.product-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 1px solid var(--border);
}

.product-card-stock {
    font-size: 11px;
    font-weight: 600;
}

.product-card-actions {
    display: flex;
    gap: 4px;
}

.featured-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: rgba(227,179,65,0.9);
    color: #000;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
    backdrop-filter: blur(4px);
}

.sale-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(248,81,73,0.9);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
}

.inactive-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: var(--red);
    letter-spacing: 2px;
    text-transform: uppercase;
}

/* Table image thumbnail */
.product-thumb {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border);
    background: var(--bg3);
}

.product-thumb-placeholder {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    background: var(--bg3);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

/* View toggle buttons */
.view-toggle {
    display: flex;
    gap: 4px;
}

.view-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.15s;
    text-decoration: none;
}

.view-btn.active, .view-btn:hover {
    background: var(--bg3);
    color: var(--text);
    border-color: var(--green);
    color: var(--green);
}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <div style="font-size:13px;color:var(--text-muted)"><?= $total ?> products found</div>
    <div style="display:flex;gap:10px;align-items:center">
        <!-- View Toggle -->
        <div class="view-toggle">
            <a href="?view=table&search=<?= $search ?>&category=<?= $filter_cat ?>&status=<?= $filter_status ?>" class="view-btn <?= $view==='table'?'active':'' ?>" title="Table View">☰</a>
            <a href="?view=grid&search=<?= $search ?>&category=<?= $filter_cat ?>&status=<?= $filter_status ?>" class="view-btn <?= $view==='grid'?'active':'' ?>" title="Grid View">⊞</a>
        </div>
        <button class="btn btn-primary" onclick="openModal('addModal')">＋ Add Product</button>
    </div>
</div>

<div class="card">
    <!-- Filters -->
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <input type="hidden" name="view" value="<?= $view ?>">
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
            <a href="products.php?view=<?= $view ?>" class="btn btn-ghost btn-sm">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($rows)): ?>
        <div class="empty-state"><div class="empty-icon">📦</div><p>No products found</p></div>

    <?php elseif ($view === 'grid'): ?>
    <!-- ===================== GRID VIEW ===================== -->
    <div class="product-grid">
        <?php foreach ($rows as $p): ?>
        <div class="product-card">
            <?php if ($p['status'] === 'inactive'): ?>
            <div class="inactive-overlay">Inactive</div>
            <?php endif; ?>

            <?php if ($p['featured']): ?>
            <div class="featured-badge">⭐ Featured</div>
            <?php endif; ?>

            <?php if ($p['sale_price']): ?>
            <div class="sale-badge">SALE</div>
            <?php endif; ?>

            <?php if ($p['image']): ?>
            <img src="<?= $p['image'] ?>" alt="<?= sanitize($p['name']) ?>" class="product-card-img"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="product-card-img-placeholder" style="display:none">📦</div>
            <?php else: ?>
            <div class="product-card-img-placeholder">📦</div>
            <?php endif; ?>

            <div class="product-card-body">
                <div class="product-card-cat"><?= sanitize($p['cat_name']) ?></div>
                <div class="product-card-name"><?= sanitize($p['name']) ?></div>
                <div class="product-card-price">
                    <span class="current"><?= formatMYR($p['sale_price'] ?? $p['price']) ?></span>
                    <?php if ($p['sale_price']): ?>
                    <span class="original"><?= formatMYR($p['price']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="product-card-footer">
                    <span class="product-card-stock" style="color:<?= $p['stock']<=5?'var(--red)':($p['stock']<=20?'var(--yellow)':'var(--text-muted)') ?>">
                        📦 <?= $p['stock'] ?> <?= $p['unit'] ?>
                    </span>
                    <div class="product-card-actions">
                        <button class="btn btn-ghost btn-sm btn-icon" onclick="editProduct(<?= htmlspecialchars(json_encode($p)) ?>)" title="Edit">✏️</button>
                        <a href="products.php?toggle=<?= $p['id'] ?>&view=grid" class="btn btn-ghost btn-sm btn-icon" onclick="return confirm('Toggle status?')" title="Toggle">🔄</a>
                        <a href="products.php?delete=<?= $p['id'] ?>&view=grid" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete?')" title="Delete">🗑</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <!-- ===================== TABLE VIEW ===================== -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Unit</th>
                    <th>⭐</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach ($rows as $p): ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $i++ ?></td>
                    <td>
                        <?php if ($p['image']): ?>
                        <img src="<?= $p['image'] ?>" alt="<?= sanitize($p['name']) ?>" class="product-thumb"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="product-thumb-placeholder" style="display:none">📦</div>
                        <?php else: ?>
                        <div class="product-thumb-placeholder">📦</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:600"><?= sanitize($p['name']) ?></div>
                        <?php if ($p['sale_price']): ?>
                        <div style="font-size:11px;color:var(--red);font-weight:600">🏷 ON SALE</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="background:var(--bg3);padding:3px 8px;border-radius:5px;font-size:12px">
                            <?= sanitize($p['cat_name']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($p['sale_price']): ?>
                        <span style="text-decoration:line-through;color:var(--text-muted);font-size:12px"><?= formatMYR($p['price']) ?></span><br>
                        <strong style="color:var(--green)"><?= formatMYR($p['sale_price']) ?></strong>
                        <?php else: ?>
                        <strong><?= formatMYR($p['price']) ?></strong>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="color:<?= $p['stock']<=5?'var(--red)':($p['stock']<=20?'var(--yellow)':'var(--green)') ?>;font-weight:600">
                            <?= $p['stock'] ?>
                        </span>
                    </td>
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
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ===================== ADD MODAL ===================== -->
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
                    <label>Image URL</label>
                    <input type="text" name="image" id="add_image_url" placeholder="https://images.unsplash.com/..." oninput="previewImage('add_preview', this.value)">
                    <div style="margin-top:10px;display:flex;gap:12px;align-items:center">
                        <img id="add_preview" src="" alt="" style="width:80px;height:80px;border-radius:8px;object-fit:cover;border:1px solid var(--border);display:none;background:var(--bg3)">
                        <span style="font-size:12px;color:var(--text-muted)">Paste an image URL to preview</span>
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

<!-- ===================== EDIT MODAL ===================== -->
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
                    <label>Image URL</label>
                    <input type="text" name="image" id="e_image" placeholder="https://images.unsplash.com/..." oninput="previewImage('e_preview', this.value)">
                    <div style="margin-top:10px;display:flex;gap:12px;align-items:center">
                        <img id="e_preview" src="" alt="" style="width:80px;height:80px;border-radius:8px;object-fit:cover;b

<?php require_once '../includes/footer.php'; ?>
