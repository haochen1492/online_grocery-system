<?php
session_start();
require 'config/db.php';
require 'includes/header.php';
$pageTitle = "Manage Orders";

$msg = '';

// Update delivery status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status  = $_POST['delivery_status'];
    $allowed = ['pending', 'shipped', 'delivered'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET delivery_status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $status, $orderId);
        $msg = $stmt->execute() ? ['success', 'Order status updated.'] : ['danger', 'Update failed.'];
    }
}

// Delete order
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM orders WHERE order_id = $id");
    header("Location: orders.php?deleted=1");
    exit;
}

if (isset($_GET['deleted'])) $msg = ['success', 'Order deleted.'];

// Filters
$search    = trim($_GET['search'] ?? '');
$filterSt  = $_GET['status'] ?? '';
$where     = "WHERE 1=1";
$params    = [];
$types     = "";

if ($search) {
    $where   .= " AND (c.customer_name LIKE ? OR o.order_id = ?)";
    $params[] = "%$search%";
    $params[] = (int)$search;
    $types   .= "si";
}
if (in_array($filterSt, ['pending','shipped','delivered'])) {
    $where   .= " AND o.delivery_status = ?";
    $params[] = $filterSt;
    $types   .= "s";
}

$sql = "
    SELECT o.order_id, o.order_date, o.total_price, o.delivery_status,
           c.customer_name, c.customer_email,
           a.unit_no, a.street, a.city, a.state, a.postal_code, a.country
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    JOIN addresses a ON o.address_id  = a.address_id
    $where
    ORDER BY o.order_date DESC
";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();
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
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search by name or order #…"
               value="<?= htmlspecialchars($search) ?>" style="width:220px">
        <select name="status" class="form-select form-select-sm" style="width:150px">
            <option value="">All Statuses</option>
            <option value="pending"   <?= $filterSt === 'pending'   ? 'selected' : '' ?>>Pending</option>
            <option value="shipped"   <?= $filterSt === 'shipped'   ? 'selected' : '' ?>>Shipped</option>
            <option value="delivered" <?= $filterSt === 'delivered' ? 'selected' : '' ?>>Delivered</option>
        </select>
        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
        <a href="orders.php" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></a>
    </form>
</div>

<!-- Orders Table -->
<div class="content-card card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#Order</th>
                        <th>Customer</th>
                        <th>Delivery Address</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($orders->num_rows === 0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr>
                <?php endif; ?>
                <?php while ($o = $orders->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= $o['order_id'] ?></strong></td>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($o['customer_name']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($o['customer_email']) ?></small>
                    </td>
                    <td>
                        <small>
                            <?= htmlspecialchars($o['unit_no']) ?>,
                            <?= htmlspecialchars($o['street']) ?>,
                            <?= htmlspecialchars($o['city']) ?>,
                            <?= htmlspecialchars($o['state']) ?>
                            <?= htmlspecialchars($o['postal_code']) ?>,
                            <?= htmlspecialchars($o['country']) ?>
                        </small>
                    </td>
                    <td><strong>$<?= number_format($o['total_price'], 2) ?></strong></td>
                    <td>
                        <span class="badge badge-<?= $o['delivery_status'] ?> px-2 py-1 rounded-pill">
                            <?= ucfirst($o['delivery_status']) ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= date('d M Y, h:i A', strtotime($o['order_date'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-success me-1"
                            data-bs-toggle="modal" data-bs-target="#statusModal"
                            data-id="<?= $o['order_id'] ?>"
                            data-status="<?= $o['delivery_status'] ?>">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info me-1"
                            data-bs-toggle="modal" data-bs-target="#detailModal"
                            data-id="<?= $o['order_id'] ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="?delete=<?= $o['order_id'] ?>" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete order #<?= $o['order_id'] ?>? This cannot be undone.')">
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

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="sm_order_id">
                    <label class="form-label fw-semibold">Delivery Status</label>
                    <select name="delivery_status" id="sm_status" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AJAX: fetch_order_details.php output lands here -->
<script src="[cdn.jsdelivr.net](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js)"></script>
<script>
// Status modal
const statusModal = document.getElementById('statusModal');
statusModal.addEventListener('show.bs.modal', e => {
    const btn = e.relatedTarget;
    document.getElementById('sm_order_id').value = btn.dataset.id;
    const sel = document.getElementById('sm_status');
    for (let opt of sel.options) opt.selected = opt.value === btn.dataset.status;
});

// Detail modal — AJAX load
const detailModal = document.getElementById('detailModal');
detailModal.addEventListener('show.bs.modal', e => {
    const orderId = e.relatedTarget.dataset.id;
    document.getElementById('detailBody').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div></div>';
    fetch(`fetch_order_details.php?order_id=${orderId}`)
        .then(r => r.text())
        .then(html => document.getElementById('detailBody').innerHTML = html);
});

// Sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
});
</script>
</body>
</html>

