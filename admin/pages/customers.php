<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$db = getDB();
$page_title = 'Manage Customers';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $status = $_POST['status'];

    if ($action === 'add') {
        $stmt = $db->prepare("INSERT INTO customers (name,email,phone,address,city,status) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $name,$email,$phone,$address,$city,$status);
        $stmt->execute();
        redirect('customers.php', 'Customer added successfully!');
    } else {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE customers SET name=?,email=?,phone=?,address=?,city=?,status=? WHERE id=?");
        $stmt->bind_param("ssssssi", $name,$email,$phone,$address,$city,$status,$id);
        $stmt->execute();
        redirect('customers.php', 'Customer updated successfully!');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM customers WHERE id=$id");
    redirect('customers.php', 'Customer deleted.', 'info');
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $db->query("UPDATE customers SET status = IF(status='active','blocked','active') WHERE id=$id");
    redirect('customers.php', 'Customer status updated.');
}

$search = sanitize($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$where = "WHERE 1";
if ($search) $where .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
if ($filter_status) $where .= " AND status='$filter_status'";

$customers = $db->query("SELECT * FROM customers $where ORDER BY created_at DESC");
$total = $db->query("SELECT COUNT(*) as c FROM customers $where")->fetch_assoc()['c'];

require_once '../includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <div style="font-size:13px;color:var(--text-muted)"><?= $total ?> customers found</div>
    <button class="btn btn-primary" onclick="openModal('addModal')">＋ Add Customer</button>
</div>

<div class="card">
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <div class="search-bar">
                <span>🔍</span>
                <input type="text" name="search" placeholder="Search name, email, phone..." value="<?= $search ?>">
            </div>
            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" <?= $filter_status==='active'?'selected':'' ?>>Active</option>
                <option value="blocked" <?= $filter_status==='blocked'?'selected':'' ?>>Blocked</option>
            </select>
            <?php if ($search || $filter_status): ?>
            <a href="customers.php" class="btn btn-ghost btn-sm">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Customer</th><th>Phone</th><th>City</th>
                    <th>Orders</th><th>Joined</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            $rows = [];
            while ($c = $customers->fetch_assoc()) $rows[] = $c;
            if (empty($rows)):
            ?>
                <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">👥</div><p>No customers found</p></div></td></tr>
            <?php else: foreach ($rows as $c):
                $order_count = $db->query("SELECT COUNT(*) as cnt FROM orders WHERE customer_id={$c['id']}")->fetch_assoc()['cnt'];
            ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $i++ ?></td>
                    <td>
                        <div style="font-weight:600"><?= sanitize($c['name']) ?></div>
                        <div style="font-size:12px;color:var(--text-muted)"><?= $c['email'] ?></div>
                    </td>
                    <td><?= $c['phone'] ?: '—' ?></td>
                    <td><?= $c['city'] ?: '—' ?></td>
                    <td><a href="orders.php?customer=<?= $c['id'] ?>" style="color:var(--accent);font-weight:600"><?= $order_count ?> orders</a></td>
                    <td style="color:var(--text-muted);font-size:12px"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                    <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-ghost btn-sm btn-icon" onclick="editCustomer(<?= htmlspecialchars(json_encode($c)) ?>)">✏️</button>
                            <a href="customers.php?toggle=<?= $c['id'] ?>" class="btn btn-ghost btn-sm btn-icon" onclick="return confirm('Toggle status?')">🔄</a>
                            <a href="customers.php?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete this customer?')">🗑</a>
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
    <div class="modal">
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
                        <input type="text" name="name" required placeholder="Ahmad bin Hassan">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required placeholder="email@example.com">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" placeholder="012-3456789">
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" placeholder="Kuala Lumpur">
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2" placeholder="Full address..."></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active">Active</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Customer</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Customer</span>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" id="edit_phone">
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" id="edit_city">
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" id="edit_address" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="active">Active</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Customer</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCustomer(c) {
    document.getElementById('edit_id').value = c.id;
    document.getElementById('edit_name').value = c.name;
    document.getElementById('edit_email').value = c.email;
    document.getElementById('edit_phone').value = c.phone || '';
    document.getElementById('edit_city').value = c.city || '';
    document.getElementById('edit_address').value = c.address || '';
    document.getElementById('edit_status').value = c.status;
    openModal('editModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>
