<?php
require_once '../includes/auth.php';
require_once '../db.php';
requireLogin();
$db = getDB();
$page_title = 'Products';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name   = sanitize($_POST['name'] ?? '');
    $cat_id = (int)($_POST['category_id'] ?? 0);
    $desc   = sanitize($_POST['description'] ?? '');
    $price  = (float)($_POST['price'] ?? 0);
    $stock  = (int)($_POST['stock_quantity'] ?? 0);
    $img    = sanitize($_POST['product_image'] ?? '');

    if ($action === 'add') {
        $stmt = $db->prepare("INSERT INTO products (category_id,name,description,price,stock_quantity,product_image) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("issdis", $cat_id,$name,$desc,$price,$stock,$img);
        $stmt->execute();
        redirect('products.php', "Product '$name' added!");
    } elseif ($action === 'edit') {
        $id = (int)$_POST['product_id'];
        $stmt = $db->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,stock_quantity=?,product_image=? WHERE product_id=?");
        $stmt->bind_param("issdisi", $cat_id,$name,$desc,$price,$stock,$img,$id);
        $stmt->execute();
        redirect('products.php', 'Product updated!');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM products WHERE product_id=$id");
    redirect('products.php', 'Product deleted.', 'info');
}

$view    = $_GET['view'] ?? 'table';
$search  = sanitize($_GET['search'] ?? '');
$fcat    = (int)($_GET['category'] ?? 0);
$where   = "WHERE 1";
if ($search) $where .= " AND p.name LIKE '%$search%'";
if ($fcat)   $where .= " AND p.category_id=$fcat";

$result = $db->query("
    SELECT p.*, c.category_name
    FROM products p LEFT JOIN categories c ON p.category_id=c.category_id
    $where ORDER BY p.created_at DESC
");
$rows = [];
while ($r = $result->fetch_assoc()) $rows[] = $r;

$cats_r = $db->query("SELECT * FROM categories ORDER BY category_name");
$cats   = [];
while ($c = $cats_r->fetch_assoc()) $cats[] = $c;

require_once '../includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>Products</h2>
        <p>Add products — visible on the customer page — Task 2</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <div class="view-toggle">
            <a href="?view=table&search=<?= $search ?>&category=<?= $fcat ?>" class="vt-btn <?= $view==='table'?'active':'' ?>" title="Table">☰</a>
            <a href="?view=grid&search=<?= $search ?>&category=<?= $fcat ?>"  class="vt-btn <?= $view==='grid'?'active':'' ?>"  title="Grid">⊞</a>
        </div>
        <button class="btn btn-primary" onclick="openModal('addModal')">＋ Add Product</button>
    </div>
</div>

<div class="card">
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <input type="hidden" name="view" value="<?= $view ?>">
            <div class="search-bar">
                <span class="si">🔍</span>
                <input type="text" name="search" placeholder="Search products..." value="<?= $search ?>">
            </div>
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($cats as $c): ?>
                <option value="<?= $c['category_id'] ?>" <?= $fcat==$c['category_id']?'selected':'' ?>>
                    <?= sanitize($c['category_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($search || $fcat): ?><a href="products.php?view=<?= $view ?>" class="btn btn-ghost btn-sm">✕ Clear</a><?php endif; ?>
        </form>
        <span style="margin-left:auto;font-size:13px;color:var(--text3)"><?= count($rows) ?> products</span>
    </div>

    <?php if (empty($rows)): ?>
        <div class="empty-state"><div class="ei">📦</div><p>No products found</p></div>
    <?php elseif ($view === 'grid'): ?>
    <!-- GRID -->
    <div class="p-grid">
        <?php foreach ($rows as $p): ?>
        <div class="p-card">
            <?php if ($p['stock_quantity'] <= 5): ?>
            <div class="p-flag p-flag-r" style="background:var(--red-bg);color:var(--red)">LOW STOCK</div>
            <?php elseif ($p['stock_quantity'] <= 15): ?>
            <div class="p-flag p-flag-r" style="background:var(--yellow-bg);color:var(--yellow)">LOW</div>
            <?php endif; ?>

            <?php if ($p['product_image']): ?>
            <img src="<?= sanitize($p['product_image']) ?>" class="p-card-img" alt="<?= sanitize($p['name']) ?>"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="p-card-img-ph" style="display:none">📦</div>
            <?php else: ?>
            <div class="p-card-img-ph">📦</div>
            <?php endif; ?>

            <div class="p-card-body">
                <div class="p-card-cat"><?= sanitize($p['category_name']) ?></div>
                <div class="p-card-name"><?= sanitize($p['name']) ?></div>
                <div class="p-card-price"><?= formatRM($p['price']) ?></div>
                <div class="p-card-foot">
                    <span class="p-card-stock"
                          style="color:<?= $p['stock_quantity']<=5?'var(--red)':($p['stock_quantity']<=15?'var(--orange)':'var(--text3)') ?>">
                        📦 <?= $p['stock_quantity'] ?> left
                    </span>
                    <div style="display:flex;gap:5px">
                        <button class="btn btn-orange btn-sm btn-icon" onclick='editProduct(<?= htmlspecialchars(json_encode($p)) ?>)'>✏️</button>
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
    <!-- TABLE -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Image</th><th>Product</th><th>Category</th>
                    <th>Price</th><th>Stock</th><th>Added</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach ($rows as $p): ?>
            <tr>
                <td style="color:var(--text3)"><?= $i++ ?></td>
                <td>
                    <?php if ($p['product_image']): ?>
                    <img src="<?= sanitize($p['product_image']) ?>" class="p-thumb" alt=""
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="p-thumb-ph" style="display:none">📦</div>
                    <?php else: ?><div class="p-thumb-ph">📦</div><?php endif; ?>
                </td>
                <td>
                    <div style="font-weight:600"><?= sanitize($p['name']) ?></div>
                    <?php if ($p['description']): ?>
                    <div style="font-size:11.5px;color:var(--text3)"><?= substr(sanitize($p['description']),0,55) ?>...</div>
                    <?php endif; ?>
                </td>
                <td>
                    <span style="background:var(--green-bg);color:var(--green);padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600">
                        <?= sanitize($p['category_name']) ?>
                    </span>
                </td>
                <td><strong style="color:var(--green)"><?= formatRM($p['price']) ?></strong></td>
                <td>
                    <span style="font-weight:700;color:<?= $p['stock_quantity']<=5?'var(--red)':($p['stock_quantity']<=15?'var(--orange)':'var(--text)') ?>">
                        <?= $p['stock_quantity'] ?>
                    </span>
                </td>
                <td style="color:var(--text3);font-size:12px"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <button class="btn btn-orange btn-sm btn-icon" onclick='editProduct(<?= htmlspecialchars(json_encode($p)) ?>)'>✏️</button>
                        <a href="products.php?delete=<?= $p['product_id'] ?>"
                           class="btn btn-danger btn-sm btn-icon"
                           onclick="return confirm('Delete?')">🗑</a>
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
            <span class="modal-title">➕ Add Product</span>
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
                    <label>Image URL</label>
                    <input type="text" name="product_image" id="ai_url"
                           placeholder="https://images.unsplash.com/..."
                           oninput="previewImg('ai_prev',this.value)">
                    <div style="margin-top:9px">
                        <img id="ai_prev" src="" style="width:78px;height:78px;border-radius:9px;object-fit:cover;border:1px solid var(--border);display:none" alt="">
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
            <span class="modal-title">✏️ Edit Product</span>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="product_id" id="ep_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" id="ep_name" required>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" id="ep_cat" required>
                            <?php foreach ($cats as $c): ?>
                            <option value="<?= $c['category_id'] ?>"><?= sanitize($c['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="product_image" id="ep_img"
                           oninput="previewImg('ep_prev',this.value)">
                    <div style="margin-top:9px">
                        <img id="ep_prev" src="" style="width:78px;height:78px;border-radius:9px;object-fit:cover;border:1px solid var(--border)" alt="">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="ep_desc"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (RM) *</label>
                        <input type="number" name="price" id="ep_price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" id="ep_stock" min="0" required>
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
    document.getElementById('ep_id').value    = p.product_id;
    document.getElementById('ep_name').value  = p.name;
    document.getElementById('ep_cat').value   = p.category_id;
    document.getElementById('ep_desc').value  = p.description || '';
    document.getElementById('ep_price').value = p.price;
    document.getElementById('ep_stock').value = p.stock_quantity;
    document.getElementById('ep_img').value   = p.product_image || '';
    const prev = document.getElementById('ep_prev');
    if (p.product_image) { prev.src = p.product_image; prev.style.display = 'block'; }
    else prev.style.display = 'none';
    openModal('editModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>
