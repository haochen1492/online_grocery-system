<?php
session_start();
require 'config/db.php';
require 'includes/header.php';
$pageTitle = "Manage Products";

$msg = '';
$uploadDir = 'uploads/products/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

function uploadImage($file, $uploadDir) {
    $allowed = ['jpg','jpeg','png','webp','gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return [false, 'Invalid image type.'];
    if ($file['size'] > 2 * 1024 * 1024) return [false, 'Image must be under 2MB.'];
    $filename = uniqid('prod_') . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) return [true, $filename];
    return [false, 'Upload failed.'];
}

// Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $catId  = (int)$_POST['category_id'];
    $name   = trim($_POST['name']);
    $desc   = trim($_POST['description']);
    $price  = (float)$_POST['price'];
    $stock  = (int)$_POST['stock_quantity'];
    $imgName = null;

    if (!empty($_FILES['product_image']['name'])) {
        [$ok, $res] = uploadImage($_FILES['product_image'], $uploadDir);
        if ($ok) $imgName = $res;
        else { $msg = ['danger', $res]; goto endAdd; }
    }

    $stmt = $conn->prepare("INSERT INTO products (category_id,name,description,price,stock_quantity,product_image) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("issdis", $catId, $name, $desc, $price, $stock, $imgName);
    $msg = $stmt->execute() ? ['success', 'Product added.'] : ['danger', 'Error adding product.'];
    endAdd:;
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $r  = $conn->query("SELECT product_image FROM products WHERE product_id=$id")->fetch_assoc();
    if ($r && $r['product_image'] && file_exists($uploadDir . $r['product_image']))
        unlink($uploadDir . $r['product_image']);
    $conn->query("DELETE FROM products WHERE product_id=$id");
    header("Location: products.php?deleted=1"); exit;
}

// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id     = (int)$_POST['product_id'];
    $catId  = (int)$_POST['category_id_edit'];
    $name   = trim($_POST['name_edit']);
    $desc   = trim($_POST['description_edit']);
    $price  = (float)$_POST['price_edit'];
    $stock  = (int)$_POST['stock_edit'];
    $imgName = $_POST['current_image'];

    if (!empty($_FILES['product_image_edit']['name'])) {
        [$ok, $res] = uploadImage($_FILES['product_image_edit'], $uploadDir);
        if ($ok) {
            if ($imgName && file_exists($uploadDir . $imgName)) unlink($uploadDir . $imgName);
            $imgName = $res;
        } else { $msg = ['danger', $res]; goto endEdit; }
    }

    $stmt = $conn->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,stock_quantity=?,product_image=? WHERE product_id=?");
    $stmt->bind_param("issdisr", $catId, $name, $desc, $price, $stock, $imgName, $id);
    // fix bind: i s s d i s i
    $stmt = $conn->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,stock_quantity=?,product_image=? WHERE product_id=?");
    $stmt->bind_param("issdisi", $catId, $name, $desc, $price, $stock, $imgName, $id);
    $msg = $stmt->execute() ? ['success', 'Product updated.'] : ['danger', 'Update failed.'];
    endEdit:;
}

if (isset($_GET['deleted'])) $msg = ['success', 'Product deleted.'];

// Search & Filter
$search    = trim($_GET['search'] ?? '');
$filterCat = (int)($_GET['category'] ?? 0);
$where     = "WHERE 1=1";
$params    = [];
$types     = "";
if ($search)    { $where .= " AND p.name LIKE ?";         $params[] = "%$search%"; $types .= "s"; }
if ($filterCat) { $where .= " AND p.category_id = ?";     $params[] = $filterCat;  $types .= "i"; }

$sql  = "SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id=c.category_id $where ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");
$catList    = [];
while ($r = $categories->fetch_assoc()) $catList[] = $r;
?>
<?php require 'includes/sidebar.php'; ?>
<div id="content-wrapper">
<?php require 'includes/topbar.php'; ?>
<div class="p-4">

<?php if ($msg): ?>
    <div class="alert alert-<?= $msg[0] ?> alert-dismissible fade show">
        <?= $msg[1] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Toolbar -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2 flex-wrap" method="GET">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search product…" value="<?= htmlspecialchars($search) ?>" style="width:200px">
        <select name="category" class="form-select form-select-sm" style="width:160px">
            <option value="">All Categories</option>
            <?php foreach ($catList as $c): ?>
                <option value="<?= $c['category_id'] ?>" <?= $filterCat == $c['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
        <a href="products.php" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></a>
    </form>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-1"></i>Add Product
    </button>
</div>

<!-- Products Table -->
<div class="content-card card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Added</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php while ($p = $products->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if ($p['product_image'] && file_exists($uploadDir . $p['product_image'])): ?>
                            <img src="<?= $uploadDir . $p['product_image'] ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px">
                        <?php else: ?>
                            <div style="width:48px;height:48px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong><br><small class="text-muted"><?= substr(htmlspecialchars($p['description']), 0, 50) ?>…</small></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name']) ?></span></td>
                    <td>$<?= number_format($p['price'], 2) ?></td>
                    <td>
                        <span class="badge <?= $p['stock_quantity'] <= 10 ? 'bg-danger' : 'bg-success' ?>">
                            <?= $p['stock_quantity'] ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1 btn-edit-product"
                            data-bs-toggle="modal" data-bs-target="#editModal"
                            data-id="<?= $p['product_id'] ?>"
                            data-catid="<?= $p['category_id'] ?>"
                            data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                            data-desc="<?= htmlspecialchars($p['description'], ENT_QUOTES) ?>"
                            data-price="<?= $p['price'] ?>"
                            data-stock="<?= $p['stock_quantity'] ?>"
                            data-img="<?= $p['product_image'] ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete this product?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
</div>

<!-- Add Modal

}
</script>

<?php require_once '../includes/footer.php'; ?>
