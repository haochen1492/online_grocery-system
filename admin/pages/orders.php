<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$db = getDB();
$page_title = 'Manage Orders';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['order_id'];
    $status = $_POST['order_status'];
    $pay_status = $_POST['payment_status'];
    $db->query("UPDATE orders SET status='$status', payment_status='$pay_status' WHERE id=$id");
    redirect('orders.php', 'Order updated successfully!');
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM order_items WHERE order_id=$id");
    $db->query("DELETE FROM orders WHERE id=$id");
    redirect('orders.php', 'Order deleted.', 'info');
}

$search = sanitize($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$filter_pay = $_GET['pay'] ?? '';
$filter_customer = (int)($_GET['customer'] ?? 0);
$where = "WHERE 1";
if ($search) $where .= " AND (o.order_number LIKE '%$search%' OR c.name LIKE '%$search%')";
if ($filter_status) $where .= " AND o.status='$filter_status'";
if ($filter_pay) $where .= " AND o.payment_status='$filter_pay'";
if ($filter_customer) $where .= " AND o.customer_id=$filter_customer";

$orders = $db->query("
    SELECT o.*, c.name as customer_name, c.phone as customer_phone
    FROM orders o JOIN customers c ON o.customer_id=c.id
    $where ORDER BY o.created_at DESC
");
$total = $db->query("SELECT COUNT(*) as cnt FROM orders o JOIN customers c ON o.customer_id=c.id $where")->fetch_assoc()['cnt'];

require_once '../includes/header.php';
?>

<div style="margin-bottom:20px">
    <div style="font-size:13px;color:var(--text-muted)"><?= $total ?> orders found</div>
</div>

<div class="card">
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <div class="search-bar">
                <span>🔍</span>
                <input type="text" name="search" placeholder="Search order # or customer..." value="<?= $search ?>">
            </div>
            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $filter_status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="pay" onchange="this.form.submit()">
                <option value="">All Payments</option>
                <option value="unpaid" <?= $filter_pay==='unpaid'?'selected':'' ?>>Unpaid</option>
                <option value="paid" <?= $filter_pay==='paid'?'selected':'' ?>>Paid</option>
                <option value="refunded" <?= $filter_pay==='refunded'?'selected':'' ?>>Refunded</option>
            </select>
            <?php if ($search || $filter_status || $filter_pay || $filter_customer): ?>
            <a href="orders.php" class="btn btn-ghost btn-sm">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order #</th><th>Customer</th><th>Items</th><th>Total</th>
                    <th>Payment</th><th>Status</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $rows = [];
            while ($o = $orders->fetch_assoc()) $rows[] = $o;
            if (empty($rows)):
            ?>
                <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🛒</div><p>No orders found</p></div></td></tr>
            <?php else: foreach ($rows as $o):
                $item_count = $db->query("SELECT COUNT(*) as cnt FROM order_items WHERE order_id={$o['id']}")->fetch_assoc()['cnt'];
            ?>
                <tr>
                    <td><strong style="color:var(--accent)"><?= $o['order_number'] ?></strong></td>
                    <td>
                        <div style="font-weight:600"><?= sanitize($o['customer_name']) ?></div>
                        <div style="font-size:12px;color:var(--text-muted)"><?= $o['customer_phone'] ?></div>
                    </td>
                    <td style="color:var(--text-muted)"><?= $item_count ?> items</td>
                    <td><strong><?= formatMYR($o['grand_total']) ?></strong></td>
                    <td>
                        <div><span class="badge badge-<?= $o['payment_status'] ?>"><?= ucfirst($o['payment_status']) ?></span></div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:3px"><?= strtoupper($o['payment_method']) ?></div>
                    </td>
                    <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td style="color:var(--text-muted);font-size:12px"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-ghost btn-sm btn-icon" onclick="viewOrder(<?= $o['id'] ?>)">👁</button>
                            <button class="btn btn-ghost btn-sm btn-icon" onclick="editOrder(<?= htmlspecialchars(json_encode($o)) ?>)">✏️</button>
                            <a href="orders.php?delete=<?= $o['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete order <?= $o['order_number'] ?>?')">🗑</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Update Order Status</span>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="order_id" id="edit_order_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Order Number</label>
                    <input type="text" id="edit_order_num" disabled style="opacity:0.6">
                </div>
                <div class="form-group">
                    <label>Order Status</label>
                    <select name="order_status" id="edit_order_status">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Status</label>
                    <select name="payment_status" id="edit_pay_status">
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Order</button>
            </div>
        </form>
    </div>
</div>

<!-- View Detail Modal -->
<div class="modal-overlay" id="viewModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <span class="modal-title">Order Details</span>
            <button class="modal-close" onclick="closeModal('viewModal')">✕</button>
        </div>
        <div class="modal-body" id="view_body">
            <div style="text-align:center;padding:30px;color:var(--text-muted)">Loading...</div>
        </div>
    </div>
</div>

<script>
function editOrder(o) {
    document.getElementById('edit_order_id').value = o.id;
    document.getElementById('edit_order_num').value = o.order_number;
    document.getElementById('edit_order_status').value = o.status;
    document.getElementById('edit_pay_status').value = o.payment_status;
    openModal('editModal');
}
function viewOrder(id) {
    document.getElementById('view_body').innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted)">Loading...</div>';
    openModal('viewModal');
    fetch('order_detail.php?id=' + id)
        .then(r => r.text())
        .then(html => { document.getElementById('view_body').innerHTML = html; });
}
</script>

<?php require_once '../includes/footer.php'; ?>
