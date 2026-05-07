<?php
session_start();
require 'config/db.php';
require 'includes/header.php';
$pageTitle = "Manage Customers";

$msg = '';

// Delete customer
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM customers WHERE customer_id = $id");
    header("Location: customers.php?deleted=1");
    exit;
}

if (isset($_GET['deleted'])) $msg = ['success', 'Customer deleted.'];

// Search
$search = trim($_GET['search'] ?? '');
$where  = "WHERE 1=1";
$params = [];
$types  = "";
if ($search) {
    $where   .= " AND (c.customer_name LIKE ? OR c.customer_email LIKE ? OR c.customer_phone LIKE ?)";
    $like     = "%$search%";
    $params   = [$like, $like, $like];
    $types    = "sss";
}

$sql  = "
    SELECT c.*,
           COUNT(DISTINCT o.order_id) AS total_orders,
           COALESCE(SUM(o.total_price), 0) AS total_spent
    FROM customers c
    LEFT JOIN orders o ON c.customer_id = o.customer_id
    $where
    GROUP BY c.customer_id
    ORDER BY c.created_at DESC
";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$customers = $stmt->get_result();
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

<!-- Search -->
<div class="mb-3">
    <form class="d-flex gap-2" method="GET">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search by name, email or phone…"
               value="<?= htmlspecialchars($search) ?>" style="max-width:300px">
        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
        <a href="customers.php" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></a>
    </form>
</div>

<!-- Table -->
<div class="content-card card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($customers->num_rows === 0): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No customers found.</td></tr>
                <?php endif; ?>
                <?php while ($c = $customers->fetch_assoc()): ?>
                <tr>
                    <td><?= $c['customer_id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle">
                                <?= strtoupper(substr($c['customer_name'], 0, 1)) ?>
                            </div>
                            <strong><?= htmlspecialchars($c['customer_name']) ?></strong>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($c['customer_email']) ?></td>
                    <td><?= htmlspecialchars($c['customer_phone'] ?? '—') ?></td>
                    <td><span class="badge bg-primary rounded-pill"><?= $c['total_orders'] ?></span></td>
                    <td class="text-success fw-semibold">$<?= number_format($c['total_spent'], 2) ?></td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info me-1"
                            data-bs-toggle="modal" data-bs-target="#viewModal"
                            data-id="<?= $c['customer_id'] ?>"
                            data-name="<?= htmlspecialchars($c['customer_name'], ENT_QUOTES) ?>"
                            data-email="<?= htmlspecialchars($c['customer_email'], ENT_QUOTES) ?>"
                            data-phone="<?= htmlspecialchars($c['customer_phone'] ?? '', ENT_QUOTES) ?>"
                            data-joined="<?= date('d M Y', strtotime($c['created_at'])) ?>"
                            data-orders="<?= $c['total_orders'] ?>"
                            data-spent="<?= number_format($c['total_spent'], 2) ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="?delete=<?= $c['customer_id'] ?>" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete customer <?= htmlspecialchars($c['customer_name'], ENT_QUOTES) ?>? All their orders and addresses will also be deleted.')">
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

<!-- View Customer Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-circle text-success me-2"></i>Customer Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar-lg mx-auto mb-2" id="vm_avatar"></div>
                    <h5 class="fw-bold mb-0" id="vm_name"></h5>
                    <small class="text-muted" id="vm_email"></small>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="bg-light rounded-3 p-3">
                            <div class="fw-bold text-primary fs-5" id="vm_orders"></div>
                            <small class="text-muted">Orders</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded-3 p-3">
                            <div class="fw-bold text-success fs-5" id="vm_spent"></div>
                            <small class="text-muted">Spent</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded-3 p-3">
                            <div class="fw-bold text-secondary fs-6" id="vm_joined"></div>
                            <small class="text-muted">Joined</small>
                        </div>
                    </div>
                </div>
                <hr>
                <p class="mb-1"><i class="bi bi-telephone me-2 text-muted"></i><span id="vm_phone"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Avatar styles -->
<style>
.avatar-circle {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white; font-weight: 700; font-size: 0.9rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.avatar-lg {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white; font-weight: 700; font-size: 1.8rem;
    display: flex; align-items: center; justify-content: center;
}
</style>

<script src="[cdn.jsdelivr.net](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js)"></script>
<script>
const viewModal = document.getElementById('viewModal');
viewModal.addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    document.getElementById('vm_name').textContent    = b.dataset.name;
    document.getElementById('vm_email').textContent   = b.dataset.email;
    document.getElementById('vm_phone').textContent   = b.dataset.phone || '—';
    document.getElementById('vm_joined').textContent  = b.dataset.joined;
    document.getElementById('vm_orders').textContent  = b.dataset.orders;
    document.getElementById('vm_spent').textContent   = '$' + b.dataset.spent;
    document.getElementById('vm_avatar').textContent  = b.dataset.name.charAt(0).toUpperCase();
});

document.getElementById('sidebarToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
});
</script>
</body>
</html>

