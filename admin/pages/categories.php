<?php
session_start();
require 'config/db.php';
require 'includes/header.php';
$pageTitle = "Manage Categories";

$msg = '';

// Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['category_name']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) $msg = ['success', 'Category added successfully.'];
        else $msg = ['danger', 'Category already exists or error occurred.'];
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM categories WHERE category_id = $id");
    header("Location: categories.php?deleted=1");
    exit;
}

// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $id   = (int)$_POST['category_id'];
    $name = trim($_POST['category_name_edit']);
    $stmt = $conn->prepare("UPDATE categories SET category_name=? WHERE category_id=?");
    $stmt->bind_param("si", $name, $id);
    if ($stmt->execute()) $msg = ['success', 'Category updated.'];
    else $msg = ['danger', 'Update failed.'];
}

if (isset($_GET['deleted'])) $msg = ['success', 'Category deleted.'];

$categories = $conn->query("SELECT * FROM categories ORDER BY category_id DESC");
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

<div class="row g-4">
    <!-- Add Category -->
    <div class="col-md-4">
        <div class="content-card card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle text-success me-2"></i>Add Category</h6>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="category_name" class="form-control" placeholder="e.g. Fruits & Vegetables" required>
                </div>
                <button type="submit" name="add_category" class="btn btn-success w-100">
                    <i class="bi bi-plus-lg me-1"></i>Add Category
                </button>
            </form>
        </div>
    </div>

    <!-- Category List -->
    <div class="col-md-8">
        <div class="content-card card">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold"><i class="bi bi-tags text-success me-2"></i>All Categories</h6>
            </div>
            <div class="card-body px-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>#</th><th>Category Name</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><?= $cat['category_id'] ?></td>
                            <td><?= htmlspecialchars($cat['category_name']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1"
                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="<?= $cat['category_id'] ?>"
                                    data-name="<?= htmlspecialchars($cat['category_name']) ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?delete=<?= $cat['category_id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this category?')">
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
    </div>
</div>
</div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_id" id="edit_cat_id">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category_name_edit" id="edit_cat_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_category" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="[cdn.jsdelivr.net](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js)"></script>
<script>
const editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', e => {
    const btn = e.relatedTarget;
    document.getElementById('edit_cat_id').value   = btn.dataset.id;
    document.getElementById('edit_cat_name').value = btn.dataset.name;
});
document.getElementById('sidebarToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
});
</script>
</body>
</html>
