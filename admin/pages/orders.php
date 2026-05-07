<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();
$page_title = 'Manage Orders';

// Update delivery status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $oid    = (int)$_POST['order_id'];
    $status = $_POST['delivery_status'];
    $db->query("UPDATE orders SET delivery_status='$status' WHERE order_id=$oid");
    redirect('orders.php','Order status updated!');
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM orders WHERE order_id=$id");
    redirect('orders.php','Order deleted.','info');
}

// Filters
$search      = sanitize($_GET['search'] ?? '');
$filter_stat = $_GET['status'] ?? '';
$filter_cust = (int)($_GET['customer'] ?? 0);
$where = "WHERE 1";
if ($search)      $where .= " AND (o.order_id LIKE '%$search%' OR c.customer_name LIKE '%$search%')";
if ($filter_stat) $where .= " AND o.delivery_status='$filter_stat'";
if ($filter_cust) $where .= " AND o.customer_id=$filter_cust";

$orders = $db->query("
    SELECT o.*,
           c.customer_name, c.customer_email, c.customer_phone,
           a.unit_no, a.street, a.city, a.state, a.postal_code,
           p.payment_status
    FROM orders o
    JOIN customers c  ON o.customer_id  = c.customer_id
    LEFT JOIN addresses a ON o.address_id = a.address_id
    LEFT JOIN payments p  ON o.order_id   = p.order_id
    $where
    ORDER BY o.order_date DESC
");
$rows  = [];
while ($r = $orders->fetch_assoc()) $rows[] = $r;
$total = count($rows);

require_once '../includes/header.php';
?>

<div style="margin-bottom:20px">
    <p style="color:var(--text3);font-size:13px"><?= $total ?> orders found</p>
</div>

<div class="card">
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <div class="search-bar">
                <span>🔍</span>
                <input type="text" name="search" placeholder="Search ID or customer..." value="<?= $search ?>">
            </div>
            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending"   <?= $filter_stat==='pending'  ?'selected':'' ?>>Pending</option>
                <option value="shipped"   <?= $filter_stat==='shipped'  ?'selected':'' ?>>Shipped</option>
                <option value="delivered" <?= $filter_stat==='delivered'?'selected':'' ?>>Delivered</option>
            </select>
            <?php if ($search || $filter_stat || $filter_cust): ?>
            <a href="orders.php" class="btn btn-ghost btn-sm">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Delivery Address</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Delivery</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="8"><div class="empty-state"><div class="ei">🛒</div><p>No orders found</p></div></td></tr>
            <?php else: foreach ($rows as $o):
                $item_cnt = $db->query("SELECT COUNT(*) c FROM order_details WHERE order_id={$o['order_id']}")->fetch_assoc()['c'];
            ?>
            <tr>
                <td>
                    <strong style="color:var(--blue)">#<?= $o['order_id'] ?></strong>
                    <div style="font-size:11px;color:var(--text3)"><?= $item_cnt ?> item(s)</div>
                </td>
                <td>
                    <div style="font-weight:600"><?= sanitize($o['customer_name']) ?></div>
                    <div style="font-size:11px;color:var(--text3)"><?= sanitize($o['customer_phone'] ?? '') ?></div>
                </td>
                <td style="font-size:12px;color:var(--text2);max-width:180px">
                    <?php if ($o['street']): ?>
                    <?= sanitize($o['unit_no'].' '.$o['street']) ?>,<br>
                    <?= sanitize($o['city'].', '.$o['state']) ?>
                    <?php else: ?><span style="color:var(--text3)">—</span><?php endif; ?>
                </td>
                <td><strong style="color:var(--green)"><?= formatRM($o['total_price']) ?></strong></td>
                <td>
                    <?php $ps = $o['payment_status'] ?? 'pending'; ?>
                    <span class="badge badge-<?= $ps ?>"><?= ucfirst($ps) ?></span>
                </td>
                <td><span class="badge badge-<?= $o['delivery_status'] ?>"><?= ucfirst($o['delivery_status']) ?></span></td>
                <td style="color:var(--text3);font-size:12px"><?= date('d M Y', strtotime($o['order_date'])) ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <button class="btn btn-ghost btn-sm btn-icon" onclick="viewOrder(<?= $o['order_id'] ?>)" title="View">👁</button>
                        <button class="btn btn-orange btn-sm btn-icon" onclick="updateOrder(<?= htmlspecialchars(json_encode($o)) ?>)" title="Update Status">✏️</button>
                        <a href="orders.php?delete=<?= $o['order_id'] ?>"
                           class="btn btn-danger btn-sm btn-icon"
                           onclick="return confirm('Delete order #<?= $o['order_id'] ?>?')">🗑</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- UPDATE STATUS MODAL -->
<div class="modal-overlay" id="updateModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Update Delivery Status</span>
            <button class="modal-close" onclick="closeModal('updateModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="update_order" value="1">
            <input type="hidden" name="order_id" id="uo_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Order</label>
                    <input type="text" id="uo_label" disabled style="opacity:0.6">
                </div>
                <div class="form-group">
                    <label>Delivery Status</label>
                    <select name="delivery_status" id="uo_status">
                        <option value="pending">Pending</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('updateModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- VIEW ORDER MODAL -->
<div class="modal-overlay" id="viewModal">
    <div class="modal modal-xl">
        <div class="modal-header">
            <span class="modal-title" id="view_title">Order Details</span>
            <button class="modal-close" onclick="closeModal('viewModal')">✕</button>
        </div>
        <div class="modal-body" id="view_body" style="min-height:200px">
            <div style="text-align:center;padding:40px;color:var(--text3)">Loading...</div>
        </div>
    </div>
</div>

<script>
function updateOrder(o) {
    document.getElementById('uo_id').value     = o.order_id;
    document.getElementById('uo_label').value  = '#' + o.order_id + ' — ' + o.customer_name;
    document.getElementById('uo_status').value = o.delivery_status;
    openModal('updateModal');
}

function viewOrder(id) {
    document.getElementById('view_title').textContent = 'Order #' + id;
    document.getElementById('view_body').innerHTML = '<div style="text-align:center;padding:40px;color:var(--text3)">Loading...</div>';
    openModal('viewModal');
    fetch('order_detail.php?id=' + id)
        .then(r => r.text())
        .then(html => document.getElementById('view_body').innerHTML = html)
        .catch(() => document.getElementById('view_body').innerHTML = '<p style="color:red;padding:20px">Failed to load order.</p>');
}
</script>

<?php require_once '../includes/footer.php'; ?>
