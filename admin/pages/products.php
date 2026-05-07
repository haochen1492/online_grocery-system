<?php
session_start();
require 'config/db.php';

// Auth check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = "Manage Products";
$msg = '';
$uploadDir = 'uploads/products/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// ─── Helper: upload image ───────────────────────────────────────────────────
function uploadImage($file, $uploadDir) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed))       return [false, 'Invalid image type. Allowed: JPG, PNG, WEBP, GIF.'];
    if ($file['size'] > 2 * 1024 * 1024) return [false, 'Image must be under 2MB.'];
    $filename = uniqid('prod_') . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) return [true, $filename];
    return [false, 'Upload failed. Check folder permissions.'];
}

// ─── ADD product ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $catId  = (int)$_POST['category_id'];
    $name   = trim($_POST['name']);
    $desc   = trim($_POST['description']);
    $price  = (float)$_POST['price'];
    $stock  = (int)$_POST['stock_quantity'];
    $imgName = null;

    if (!empty($_FILES['product_image']['name'])) {
        [$ok, $res] = uploadImage($_FILES['product_image'], $uploadDir);
        if ($ok) {
            $imgName = $res;
        } else {
            $msg = ['danger', $res];
            goto skipAdd;
        }
    }

    $stmt = $conn->prepare(
        "INSERT INTO products (category_id, name, description, price, stock_quantity, product_image)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issdis", $catId, $name, $desc, $price, $stock, $imgName);
    $msg = $stmt->execute()
        ? ['success', '<i class="bi bi-check-circle me-1"></i>Product added successfully.']
        : ['danger',  'Error adding product: ' . $conn->error];

    skipAdd:;
}

// ─── EDIT product ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id      = (int)$_POST['product_id'];
    $catId   = (int)$_POST['category_id_edit'];
    $name    = trim($_POST['name_edit']);
    $desc    = trim($_POST['description_edit']);
    $price   = (float)$_POST['price_edit'];
    $stock   = (int)$_POST['stock_edit'];
    $imgName = $_POST['current_image'];

    if (!empty($_FILES['product_image_edit']['name'])) {
        [$ok, $res] = uploadImage($_FILES['product_image_edit'], $uploadDir);
        if ($ok) {
            // Remove old image
            if ($imgName && file_exists($uploadDir . $imgName)) {
                unlink($uploadDir . $imgName);
            }
            $imgName = $res;
        } else {
            $msg = ['danger', $res];
            goto skipEdit;
        }
    }

    $stmt = $conn->prepare(
        "UPDATE products
         SET category_id=?, name=?, description=?, price=?, stock_quantity=?, product_image=?
         WHERE product_id=?"
    );
    $stmt->bind_param("issdisi", $catId, $name, $desc, $price, $stock, $imgName, $id);
    $msg = $stmt->execute()
        ? ['success', '<i class="bi bi-check-circle me-1"></i>Product updated successfully.']
        : ['danger',  'Error updating product: ' . $conn->error];

    skipEdit:;
}

// ─── DELETE product ─────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $r  = $conn->query("SELECT product_image FROM products WHERE product_id = $id")->fetch_assoc();
    if ($r && $r['product_image'] && file_exists($uploadDir . $r['product_image'])) {
        unlink($uploadDir . $r['product_image']);
    }
    $conn->query("DELETE FROM products WHERE product_id = $id");
    header("Location: products.php?deleted=1");
    exit;
}

if (isset($_GET['deleted'])) {
    $msg = ['success', '<i class="bi bi-check-circle me-1"></i>Product deleted successfully.'];
}

// ─── SEARCH & FILTER ────────────────────────────────────────────────────────
$search    = trim($_GET['search'] ?? '');
$filterCat = (int)($_GET['category'] ?? 0);
$where     = "WHERE 1=1";
$params    = [];
$types     = "";

if ($search) {
    $where   .= " AND p.name LIKE ?";
    $params[] = "%$search%";
    $types   .= "s";
}
if ($filterCat) {
    $where   .= " AND p.category_id = ?";
    $params[] = $filterCat;
    $types   .= "i";
}

$sql  = "SELECT p.*, c.category_name
         FROM products p
         JOIN categories c ON p.category_id = c.category_id
         $where
         ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

// ─── Category list (for dropdowns) ─────────────────────────────────────────
$catResult = $conn->query("SELECT * FROM categories ORDER BY category_name");
$catList   = [];
while ($r = $catResult->fetch_assoc()) $catList[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> – FreshCart</title>
    <link href="[cdn.jsdelivr.net](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css)" rel="stylesheet">
    <link rel="stylesheet" href="[cdn.jsdelivr.net](https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css)">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="d-flex" id="wrapper">

<?php
// ─── Sidebar ─────────────────────────────────────────────────────────────
$current = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebar" class="sidebar d-flex flex-column">
    <div class="sidebar-brand">
        <i class="bi bi-basket2-fill me-2"></i>FreshCart
    </div>
    <ul class="nav flex-column flex-grow-1 px-2 mt-3">
        <li class="nav-item">
            <a class="nav-link <?= $current === 'dashboard.php'  ? 'active' : '' ?>" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current === 'categories.php' ? 'active' : '' ?>" href="categories.php">
                <i class="bi bi-tags me-2"></i>Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current === 'products.php'   ? 'active' : '' ?>" href="products.php">
                <i class="bi bi-box-seam me-2"></i>Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current === 'customers.php'  ? 'active' : '' ?>" href="customers.php">
                <i class="bi bi-people me-2"></i>Customers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current === 'orders.php'     ? 'active' : '' ?>" href="orders.php">
                <i class="bi bi-cart3 me-2"></i>Orders
            </a>
        </li>
    </ul>
    <div class="sidebar-footer px-3 py-3">
        <div class="text-muted small mb-2">
            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['admin_username']) ?>
        </div>
        <a href="logout.php" class="btn btn-outline-light btn-sm w-100">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>
</nav>

<!-- ─── Main Content ──────────────────────────────────────────────────────── -->
<div id="content-wrapper">

    <!-- Topbar -->
    <nav class="navbar topbar px-4">
        <button id="sidebarToggle" class="btn btn-link text-dark p-0">
            <i class="bi bi-list fs-4"></i>
        </button>
        <span class="fw-semibold text-dark ms-2"><?= $pageTitle ?></span>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-muted small d-none d-md-inline">
                <i class="bi bi-clock me-1"></i><?= date('D, d M Y') ?>
            </span>
            <a href="logout.php" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </nav>

    <!-- Page Body -->
    <div class="p-4">

        <!-- Alert -->
        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg[0] ?> alert-dismissible fade show" role="alert">
                <?= $msg[1] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Toolbar -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <form class="d-flex gap-2 flex-wrap" method="GET">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search product…"
                       value="<?= htmlspecialchars($search) ?>"
                       style="width:200px">
                <select name="category" class="form-select form-select-sm" style="width:170px">
                    <option value="">All Categories</option>
                    <?php foreach ($catList as $c): ?>
                        <option value="<?= $c['category_id'] ?>"
                            <?= $filterCat == $c['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-search"></i>
                </button>
                <?php if ($search || $filterCat): ?>
                    <a href="products.php" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-lg me-1"></i>Add Product
            </button>
        </div>

        <!-- Products Table -->
        <div class="content-card card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Added</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($products->num_rows === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-box-seam fs-2 d-block mb-2 opacity-25"></i>
                                    No products found.
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php while ($p = $products->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 text-muted small"><?= $p['product_id'] ?></td>
                            <td>
                                <?php
                                $imgPath = $uploadDir . $p['product_image'];
                                if ($p['product_image'] && file_exists($imgPath)):
                                ?>
                                    <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                                         style="width:50px;height:50px;object-fit:cover;border-radius:8px;border:1px solid #eee">
                                <?php else: ?>
                                    <div style="width:50px;height:50px;background:#f0f4f8;border-radius:8px;
                                                display:flex;align-items:center;justify-content:center;border:1px solid #eee">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($p['name']) ?></div>
                                <?php if ($p['description']): ?>
                                    <small class="text-muted">
                                        <?= htmlspecialchars(mb_substr($p['description'], 0, 55)) ?>
                                        <?= mb_strlen($p['description']) > 55 ? '…' : '' ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($p['category_name']) ?>
                                </span>
                            </td>
                            <td class="fw-semibold">$<?= number_format($p['price'], 2) ?></td>
                            <td>
                                <?php if ($p['stock_quantity'] == 0): ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php elseif ($p['stock_quantity'] <= 10): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-triangle me-1"></i><?= $p['stock_quantity'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= $p['stock_quantity'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= date('d M Y', strtotime($p['created_at'])) ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary me-1 btn-edit-product"
                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="<?= $p['product_id'] ?>"
                                    data-catid="<?= $p['category_id'] ?>"
                                    data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                                    data-desc="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                                    data-price="<?= $p['price'] ?>"
                                    data-stock="<?= $p['stock_quantity'] ?>"
                                    data-img="<?= htmlspecialchars($p['product_image'] ?? '', ENT_QUOTES) ?>"
                                    title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?delete=<?= $p['product_id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete \'<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>\'? This cannot be undone.')"
                                   title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- end .p-4 -->

    <!-- Footer -->
    <footer class="footer mt-auto py-3">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">
                    &copy; <?= date('Y') ?> <strong>FreshCart</strong>. All rights reserved.
                </span>
                <span class="text-muted small">
                    Logged in as <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong>
                </span>
            </div>
        </div>
    </footer>

</div><!-- end #content-wrapper -->
</div><!-- end #wrapper -->


<!-- ═══════════════════════════════════════════════════════════════════════════
     ADD PRODUCT MODAL
════════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">
                        <i class="bi bi-plus-circle text-success me-2"></i>Add New Product
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Product Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="e.g. Organic Bananas" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($catList as $c): ?>
                                    <option value="<?= $c['category_id'] ?>">
                                        <?= htmlspecialchars($c['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Price (USD) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price" class="form-control"
                                       step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Stock Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="stock_quantity" class="form-control"
                                   min="0" placeholder="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Brief product description…"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Product Image</label>
                            <input type="file" name="product_image" id="add_img_input"
                                   class="form-control" accept="image/*">
                            <div class="form-text">Max 2MB — JPG, PNG, WEBP or GIF.</div>
                            <!-- Preview -->
                            <div id="add_img_preview" class="mt-2 d-none">
                                <img id="add_img_preview_src" src=""
                                     style="height:80px;border-radius:8px;object-fit:cover;border:1px solid #dee2e6">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_product" class="btn btn-success">
                        <i class="bi bi-plus-lg me-1"></i>Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════════════
     EDIT PRODUCT MODAL
════════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="bi bi-pencil text-primary me-2"></i>Edit Product
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="product_id"    id="ep_id">
                    <input type="hidden" name="current_image" id="ep_img">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name</label>
                            <input type="text" name="name_edit" id="ep_name"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id_edit" id="ep_cat" class="form-select" required>
                                <?php foreach ($catList as $c): ?>
                                    <option value="<?= $c['category_id'] ?>">
                                        <?= htmlspecialchars($c['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Price (USD)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price_edit" id="ep_price"
                                       class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Stock Quantity</label>
                            <input type="number" name="stock_edit" id="ep_stock"
                                   class="form-control" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description_edit" id="ep_desc"
                                      class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Replace Image</label>
                            <input type="file" name="product_image_edit" id="ep_img_input"
                                   class="form-control" accept="image/*">
                            <div class="form-text">Leave blank to keep the current image.</div>
                            <div id="ep_img_preview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_product" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════════════════════════ -->
<script src="[cdn.jsdelivr.net](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js)"></script>
<script>
// ── Sidebar toggle ──────────────────────────────────────────────────────────
document.getElementById('sidebarToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
});

// ── Add modal: live image preview ───────────────────────────────────────────
document.getElementById('add_img_input').addEventListener('change', function () {
    const preview    = document.getElementById('add_img_preview');
    const previewImg = document.getElementById('add_img_preview_src');
    if (this.files && this.files[0]) {
        previewImg.src = URL.createObjectURL(this.files[0]);
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});

// ── Edit modal: populate fields ─────────────────────────────────────────────
document.querySelectorAll('.btn-edit-product').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('ep_id').value    = btn.dataset.id;
        document.getElementById('ep_name').value  = btn.dataset.name;
        document.getElementById('ep_desc').value  = btn.dataset.desc;
        document.getElementById('ep_price').value = btn.dataset.price;
        document.getElementById('ep_stock').value = btn.dataset.stock;
        document.getElementById('ep_img').value   = btn.dataset.img;

        // Set category dropdown
        const sel = document.getElementById('ep_cat');
        Array.from(sel.options).forEach(opt => {
            opt.selected = opt.value == btn.dataset.catid;
        });

        // Show current image or placeholder
        const prev = document.getElementById('ep_img_preview');
        if (btn.dataset.img) {
            prev.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <img src="uploads/products/${btn.dataset.img}"
                         style="height:60px;width:60px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6"
                         alt="current image">
                    <small class="text-muted">Current image — upload a new one to replace it.</small>
                </div>`;
        } else {
            prev.innerHTML = '<small class="text-muted">No image currently set.</small>';
        }
    });
});

// ── Edit modal: live image preview when replacing ───────────────────────────
document.getElementById('ep_img_input').addEventListener('change', function () {
    const prev = document.getElementById('ep_img_preview');
    if (this.files && this.files[0]) {
        const url = URL.createObjectURL(this.files[0]);
        prev.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <img src="${url}"
                     style="height:60px;width:60px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6"
                     alt="new image preview">
                <small class="text-success"><i class="bi bi-check-circle me-1"></i>New image selected.</small>
            </div>`;
    }
});
</script>
</body>
</html>
