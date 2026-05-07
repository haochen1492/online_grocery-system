<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();
$page_title = 'Manage Customers';

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $name     = sanitize($_POST['customer_name'] ?? '');
    $email    = sanitize($_POST['customer_email'] ?? '');
    $phone    = sanitize($_POST['customer_phone'] ?? '');
    $password = $_POST['customer_password'] ?? '';

    if ($action === 'add') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO customers (customer_name,customer_email,customer_password,customer_phone) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $name,$email,$hash,$phone);
        if ($stmt->execute()) redirect('customers.php','Customer added!');
        else redirect('customers.php','Email already exists.','error');
    } elseif ($action === 'edit') {
        $id = (int)$_POST['customer_id'];
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE customers SET customer_name=?,customer_email=?,customer_phone=?,customer_password=? WHERE customer_id=?");
            $stmt->bind_param("ssssi",$name,$email,$phone,$hash,$id);
        } else {
            $stmt = $db->prepare("UPDATE customers SET customer_name=?,customer_email=?,customer_phone=? WHERE customer_id=?");
            $stmt->bind_param("sssi",$name,$email,$phone,$id);
        }
        $stmt->execute();
        redirect('customers.php','Customer updated!');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM customers WHERE customer_id=$id");
    redirect('customers.php','Customer deleted.','info');
}

$search = sanitize($_GET['search'] ?? '');
$where  = $search ? "WHERE customer_name LIKE '%$search%' OR customer_email LIKE '%$search%' OR customer_phone LIKE '%$search%'" : '';
$result = $db->query("SELECT * FROM customers $where ORDER BY created_at DESC");
$rows = [];
while ($r = $result->fetch_assoc()) $rows[] = $r;

require_once '../includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <p style="color:var(--text3);font-size:13px"><?= count($rows) ?> customers</p>
    <button class="btn btn-primary" onclick="openModal('addModal')">＋ Add Customer</button>
</div>

<div class="card">
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center">
            <div class="search-bar">
                <span>🔍</span>
                <input type="text" name="search" placeholder="Search name, email, phone..." value="<?= $search ?>">
            </div>
            <?php if ($search): ?><a href="customers.php" class="btn btn-ghost btn-sm">✕ Clear</a><?php endif; ?>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="ei">👥</div><p>No customers found</p></div></td></tr>
            <?php else: $i=1; foreach ($rows as $c):
                $orders = $db->query("SELECT COUNT(*) cnt FROM orders WHERE customer_id={$c['customer_id']}")->fetch_assoc()['cnt'];
                $spent  = $db->query("SELECT COALESCE(SUM(total_price),0) t FROM orders WHERE customer_id={$c['customer_id']}")->fetch_assoc()['t'];
            ?>
            <tr>
                <td style="color:var(--text3)"><?= $i++ ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:11px">
                        <div style="width:38px;height:38px;border-radius:50%;background:var(--green-bg);border:2px solid var(--green3);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--green);font-size:14px;flex-shrink:0">
                            <?= strtoupper(substr($c['customer_name'],0,1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:600"><?= sanitize($c['customer_name']) ?></div>
                            <div style="font-size:12px;color:var(--text3)"><?= sanitize($c['customer_email']) ?></div>
                        </div>
                    </div>
                </td>
                <td style="color:var(--text2)"><?= $c['customer_phone'] ?: '—' ?></td>
                <td>
                    <a href="orders.php?customer=<?= $c['customer_id'] ?>" style="color:var(--blue);font-weight:600">
                        <?= $orders ?> orders
                    </a>
                    <div style="font-size:11px;color:var(--text3)"><?= formatRM($spent) ?> spent</div>
                </td>
                <td style="color:var(--text3);font-size:12px"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <button class="btn btn-orange btn-sm" onclick='editCustomer(<?= json_encode($c) ?>)'>✏️ Edit</button>
                        <a href="customers.php?delete=<?= $c['customer_id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete customer <?= addslashes(sanitize($c['customer_name'])) ?>?')">🗑</a>
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
    <div class="modal modal-lg">
        <div class="modal-header">
            <span class="modal-title">Add New Customer</span>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="customer_name" required placeholder="Ahmad bin Hassan">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="customer_email" required placeholder="email@example.com">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="customer_phone" placeholder="012-3456789">
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="customer_password" required placeholder="Set a password">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Customer</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <span class="modal-title">Edit Customer</span>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="customer_id" id="ec_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="customer_name" id="ec_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="customer_email" id="ec_email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="customer_phone" id="ec_phone">
                    </div>
                    <div class="form-group">
                        <label>New Password <span style="font-weight:400;text-transform:none">(leave blank to keep)</span></label>
                        <input type="password" name="customer_password" placeholder="••••••••">
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
function editCustomer(c) {
    document.getElementById('ec_id').value    = c.customer_id;
    document.getElementById('ec_name').value  = c.customer_name;
    document.getElementById('ec_email').value = c.customer_email;
    document.getElementById('ec_phone').value = c.customer_phone || '';
    openModal('editModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>
