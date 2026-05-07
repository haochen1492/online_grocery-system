<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();
$page_title = 'Manage Products';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? '';
    $name        = sanitize($_POST['name'] ?? '');
    $cat_id      = (int)($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock_quantity'] ?? 0);
    $image       = sanitize($_POST['product_image'] ?? '');

    if ($action === 'add') {
        $stmt = $db->prepare("INSERT INTO products (category_id,name,description,price,stock_quantity,product_image) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("issdis", $cat_id, $name, $description, $price, $stock, $image);
        $stmt->execute();
        redirect('products.php', 'Product added!');
    } elseif ($action === 'edit') {
        $id = (int)$_POST['product_id'];
        $stmt = $db->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,stock_quantity=?,product_image=? WHERE product_id=?");
        $stmt->bind_param("issdisi", $cat_id, $name, $description, $price, $stock, $image, $id);
        $stmt->execute();
        redirect('products.php', 'Product updated!');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM products WHERE product_id=$id");
    redirect('products.php', 'Product deleted.', 'info');
}

$view       = $_GET['view'] ?? 'table';
$search     = sanitize($_GET['search'] ?? '');
$filter_cat = (int)($_GET['category'] ?? 0);
$where = "WHERE 1";
if ($search)     $where .= " AND p.name LIKE '%$search%'";
if ($filter_cat) $where .= " AND p.category_id = $filter_cat";

$products = $db->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    $where
    ORDER BY p.created_at DESC
");
$rows  = [];
while ($r = $products->fetch_assoc()) $rows[] = $r;
$total = count($rows);

$cats_res = $db->query("SELECT * FROM categories ORDER BY category_name");
$cats = [];
while ($c = $cats_res->fetch_assoc()) $cats[] = $c;

require_once '../includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <p style="color:var(--text3);font-size:13px"><?= $total ?> products found</p>
    <div style="display:flex;gap:10px;align-items:center">
        <div class="view-toggle">
            <a href="?view=table&search=<?= $search ?>&category=<?= $filter_cat ?>" class="view-btn <?= $view==='table'?'active':'' ?>" title="Table">☰</a>
            <a href="?view=grid&search=<?= $search ?>&category=<?= $filter_cat ?>"  class="view-btn <?= $view==='grid'?'active':'' ?>"  title="Grid">⊞</a>
        </div>
        <button class="btn btn-primary" onclick="openModal('addModal')">＋ Add Product</button>
    </div>
</div>

<div class="card">
    <!-- FILTERS -->
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
                <option value="<?= $c['category_id'] ?>" <?= $filter_cat==$c['category_id']?'selected':'' ?>>
                    <?= sanitize($c['category_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($search || $filter_cat): ?>
            <a href="products.php?view=<?= $view ?>" class="btn btn-ghost btn-sm">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($rows)): ?>
        <div class="empty-state"><div class="ei">📦</div><p>No products found</p></div>

    <?php elseif ($view === 'grid'): ?>
    <!-- GRID VIEW -->
    <div class="product-grid">
        <?php foreach ($rows as $p): ?>
        <div class="product-card">
            <?php if ($p['product_image']): ?>
            <img src="<?= sanitize($p['product_image']) ?>" class="product-card-img" alt="<?= sanitize($p['name']) ?>"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="product-card-img-placeholder" style="display:none">📦</div>
            <?php else: ?>
            <div class="product-card-img-placeholder">📦</div>
            <?php endif; ?>

            <?php if ($p['stock_quantity'] <= 5): ?>
            <div class="card-badge card-badge-right">LOW STOCK</div>
            <?php endif; ?>

            <div class="product-card-body">
                <div class="product-card-cat"><?= sanitize($p['category_name']) ?></div>
                <div class="product-card-name"><?= sanitize($p['name']) ?></div>
                <div class="product-card-price">
                    <span class="cur"><?= formatRM($p['price']) ?></span>
                </div>
                <div class="product-card-footer">
                    <span class="product-card-stock"
                          style="color:<?= $p['stock_quantity']<=5?'var(--red)':($p['stock_quantity']<=20?'var(--accent2)':'var(--text3)') ?>">
                        📦 <?= $p['stock_quantity'] ?> in stock
                    </span>
                    <div style="display:flex;gap:5px">
                        <button class="btn btn-orange btn-sm btn-icon" onclick="editProduct(<?= htmlspecialchars(json_encode($p)) ?>)">✏️</button>
                        <a href="products.php?delete=<?= $p['product_id'] ?>&view=grid"
                           class="btn btn-danger btn-sm btn-icon"
                           onclick="return confirm('Delete product?')">🗑</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <!-- TABLE VIEW -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach ($rows as $p): ?>
            <tr>
                <td style="color:var(--text3)"><?= $i++ ?></td>
                <td>
                    <?php if ($p['product_image']): ?>
                    <img src="<?= sanitize($p['product_image']) ?>" class="product-thumb" alt=""
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="product-thumb-placeholder" style="display:none">📦</div>
                    <?php else: ?>
                    <div class="product-thumb-placeholder">📦</div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight:600"><?= sanitize($p['name']) ?></div>
                    <?php if ($p['description']): ?>
                    <div style="font-size:11px;color:var(--text3)"><?= substr(sanitize($p['description']),0,50) ?>...</div>
                    <?php endif; ?>
                </td>
                <td>
                    <span style="background:var(--green-bg);color:var(--green);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">
                        <?= sanitize($p['category_name']) ?>
                    </span>
                </td>
                <td><strong style="color:var(--green)"><?= formatRM($p['price']) ?></strong></td>
                <td>
                    <span style="font-weight:700;color:<?= $p['stock_quantity']<=5?'var(--red)':($p['stock_quantity']<=20?'var(--accent2)':'var(--text)') ?>">
                        <?= $p['stock_quantity'] ?>
                    </span>
                </td>
                <td style="color:var(--text3);font-size:12px"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <button class="btn btn-orange btn-sm btn-icon" onclick="editProduct(<?= htmlspecialchars(json_encode($p)) ?>)">✏️</button>
                        <a href="products.php?delete=<?= $p['product_id'] ?>"
                           class="btn btn-danger btn-sm btn-icon"
                           onclick="return confirm('Delete this product?')">🗑</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ADD MODAL -->
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
                            <option value="<?= $c['category_id'] ?>"><?= sanitize($c['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Product Image URL</label>
                    <input type="text" name="product_image" id="add_img_url"
                           placeholder="https://images.unsplash.com/..."
                           oninput="previewImg('add_img_prev', this.value)">
                    <div style="margin-top:10px">
                        <img id="add_img_prev" src="" style="width:80px;height:80px;border-radius:10px;object-fit:cover;border:1px solid var(--border);display:none" alt="">
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
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" min="0" required placeholder="0">
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

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <span class="modal-title">Edit Product</span>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="product_id" id="e_pid">
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
                            <option value="<?= $c['category_id'] ?>"><?= sanitize($c['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Product Image URL</label>
                    <input type="text" name="product_image" id="e_img"
                           oninput="previewImg('e_img_prev', this.value)">
                    <div style="margin-top:10px">
                        <img id="e_img_prev" src="" style="width:80px;height:80px;border-radius:10px;object-fit:cover;border:1px solid var(--border)" alt="">
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
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" id="e_stock" min="0" required>
                    </div>
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
function editProduct(p) {
    document.getElementById('e_pid').value   = p.product_id;
    document.getElementById('e_name').value  = p.name;
    document.getElementById('e_cat').value   = p.category_id;
    document.getElementById('e_desc').value  = p.description || '';
    document.getElementById('e_price').value = p.price;
    document.getElementById('e_stock').value = p.stock_quantity;
    document.getElementById('e_img').value   = p.product_image || '';
    const prev = document.getElementById('e_img_prev');
    if (p.product_image) { prev.src = p.product_image; prev.style.display='block'; }
    else prev.style.display = 'none';
    openModal('editModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>
