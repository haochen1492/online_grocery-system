<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();
$page_title = 'Orders & Products';

// ── Task 5: Change delivery status ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $oid    = (int)$_POST['order_id'];
    $status = $_POST['delivery_status'];
    if (in_array($status, ['pending','shipped','delivered'])) {
        $db->query("UPDATE orders SET delivery_status='$status' WHERE order_id=$oid");
        redirect('orders.php', "Order #$oid status updated to '$status'!");
    }
}

// ── Filters ──
$search  = sanitize($_GET['search'] ?? '');
$fstatus = $_GET['status']   ?? '';
$fcust   = (int)($_GET['customer'] ?? 0);

$where = "WHERE 1";
if ($search)  $where .= " AND (o.order_id LIKE '%$search%' OR c.customer_name LIKE '%$search%' OR c.customer_email LIKE '%$search%')";
if ($fstatus) $where .= " AND o.delivery_status = '$fstatus'";
if ($fcust)   $where .= " AND o.customer_id = $fcust";

$orders = $db->query("
    SELECT o.*,
           c.customer_name, c.customer_email, c.customer_phone,
           a.unit_no, a.street, a.city, a.state, a.postal_code,
           p.payment_status, p.price AS paid_amount
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    LEFT JOIN addresses a ON o.address_id = a.address_id
    LEFT JOIN payments p  ON o.order_id   = p.order_id
    $where
    ORDER BY o.order_date DESC
");
$rows  = [];
while ($r = $orders->fetch_assoc()) $rows[] = $r;
$total = count($rows);

// Summary counts
$cnt_pending   = $db->query("SELECT COUNT(*) c FROM orders WHERE delivery_status='pending'")->fetch_assoc()['c'];
$cnt_shipped   = $db->query("SELECT COUNT(*) c FROM orders WHERE delivery_status='shipped'")->fetch_assoc()['c'];
$cnt_delivered = $db->query("SELECT COUNT(*) c FROM orders WHERE delivery_status='delivered'")->fetch_assoc()['c'];

require_once '../includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>Orders &amp; Products</h2>
        <p>Task 4 — View orders &amp; product list &nbsp;|&nbsp; Task 5 — Change delivery status</p>
    </div>
</div>

<!-- Quick Status Cards -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px">
    <div class="stat-card" style="cursor:pointer" onclick="window.location='orders.php?status=pending'">
        <div class="sc-top"><div class="sc-icon y">⏳</div><span class="sc-tag warn">Pending</span></div>
        <div class="sc-val" style="color:var(--yellow)"><?= $cnt_pending ?></div>
        <div class="sc-lbl">Pending Orders</div>
    </div>
    <div class="stat-card" style="cursor:pointer" onclick="window.location='orders.php?status=shipped'">
        <div class="sc-top"><div class="sc-icon b">🚚</div><span class="sc-tag neu">Shipped</span></div>
        <div class="sc-val" style="color:var(--blue)"><?= $cnt_shipped ?></div>
        <div class="sc-lbl">Shipped Orders</div>
    </div>
    <div class="stat-card" style="cursor:pointer" onclick="window.location='orders.php?status=delivered'">
        <div class="sc-top"><div class="sc-icon g">✅</div><span class="sc-tag up">Done</span></div>
        <div class="sc-val" style="color:var(--green)"><?= $cnt_delivered ?></div>
        <div class="sc-lbl">Delivered Orders</div>
    </div>
</div>

<div class="card">
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <div class="search-bar">
                <span class="si">🔍</span>
                <input type="text" name="search" placeholder="Search order ID or customer..." value="<?= $search ?>">
            </div>
            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending"   <?= $fstatus==='pending'   ?'selected':'' ?>>Pending</option>
                <option value="shipped"   <?= $fstatus==='shipped'   ?'selected':'' ?>>Shipped</option>
                <option value="delivered" <?= $fstatus==='delivered' ?'selected':'' ?>>Delivered</option>
            </select>
            <?php if ($search || $fstatus || $fcust): ?>
            <a href="orders.php" class="btn btn-ghost btn-sm">✕ Clear</a>
            <?php endif; ?>
        </form>
        <span style="margin-left:auto;font-size:13px;color:var(--text3)"><?= $total ?> orders</span>
    </div>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Products Ordered</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Delivery Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="8">
                    <div class="empty-state"><div class="ei">🛍️</div><p>No orders found</p></div>
                </td></tr>
            <?php else: foreach ($rows as $o):
                // Get order items
                $items = $db->query("
                    SELECT od.quantity, p.name, p.product_image, od.product_price
                    FROM order_details od
                    JOIN products p ON od.product_id = p.product_id
                    WHERE od.order_id = {$o['order_id']}
                ");
                $item_rows = [];
                while ($ir = $items->fetch_assoc()) $item_rows[] = $ir;
            ?>
            <tr>
                <td>
                    <strong style="color:var(--blue);font-size:14px">#<?= $o['order_id'] ?></strong>
                    <div style="font-size:11px;color:var(--text3)"><?= count($item_rows) ?> item(s)</div>
                </td>
                <td>
                    <div style="font-weight:600"><?= sanitize($o['customer_name']) ?></div>
                    <div style="font-size:11.5px;color:var(--text3)"><?= sanitize($o['customer_phone'] ?? '') ?></div>
                </td>
                <!-- Task 4: Show product list -->
                <td style="max-width:220px">
                    <?php foreach ($item_rows as $ir): ?>
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:5px">
                        <?php if ($ir['product_image']): ?>
                        <img src="<?= sanitize($ir['product_image']) ?>"
                             style="width:30px;height:30px;border-radius:6px;object-fit:cover;border:1px solid var(--border);flex-shrink:0"
                             onerror="this.style.display='none'" alt="">
                        <?php else: ?>
                        <div style="width:30px;height:30px;border-radius:6px;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">📦</div>
                        <?php endif; ?>
                        <div>
                            <div style="font-size:12px;font-weight:600;line-height:1.2"><?= sanitize($ir['name']) ?></div>
                            <div style="font-size:11px;color:var(--text3)">x<?= $ir['quantity'] ?> · <?= formatRM($ir['product_price']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </td>
                <td><strong style="color:var(--green)"><?= formatRM($o['total_price']) ?></strong></td>
                <td>
                    <?php $ps = $o['payment_status'] ?? 'pending'; ?>
                    <span class="badge badge-<?= $ps ?>"><?= ucfirst($ps) ?></span>
                </td>
                <!-- Task 5: Delivery status with inline update -->
                <td>
                    <span class="badge badge-<?= $o['delivery_status'] ?>"><?= ucfirst($o['delivery_status']) ?></span>
                </td>
                <td style="color:var(--text3);font-size:12px">
                    <?= date('d M Y', strtotime($o['order_date'])) ?><br>
                    <span style="font-size:11px"><?= date('H:i', strtotime($o['order_date'])) ?></span>
                </td>
                <td>
                    <div style="display:flex;gap:6px">
                        <button class="btn btn-ghost btn-sm btn-icon"
                            onclick="viewOrder(<?= $o['order_id'] ?>)" title="View Details">👁</button>
                        <!-- Task 5: Change status button -->
                        <button class="btn btn-orange btn-sm"
                            onclick="changeStatus(<?= htmlspecialchars(json_encode($o)) ?>)"
                            title="Change Delivery Status">🔄 Status</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── TASK 5: CHANGE STATUS MODAL ── -->
<div class="modal-overlay" id="statusM">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">🔄 Change Delivery Status — Task 5</span>
            <button class="modal-close" onclick="closeModal('statusM')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="order_id" id="cs_id">
            <div class="modal-body">
                <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:18px">
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3);margin-bottom:6px">Order</div>
                    <div style="font-weight:700;font-size:16px" id="cs_label">#—</div>
                    <div style="font-size:13px;color:var(--text3)" id="cs_cust"></div>
                </div>
                <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:18px">
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3);margin-bottom:6px">Current Status</div>
                    <div id="cs_current"></div>
                </div>
                <div class="form-group">
                    <label>New Delivery Status *</label>
                    <select name="delivery_status" id="cs_status">
                        <option value="pending">⏳ Pending — Order placed, not yet shipped</option>
                        <option value="shipped">🚚 Shipped — Package is on the way</option>
                        <option value="delivered">✅ Delivered — Customer received the order</option>
                    </select>
                    <div class="form-note">
                        Typical flow: <strong>Pending → Shipped → Delivered</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('statusM')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<!-- ── VIEW ORDER DETAIL MODAL ── -->
<div class="modal-overlay" id="viewM">
    <div class="modal modal-xl">
        <div class="modal-header">
            <span class="modal-title" id="view_title">Order Details</span>
            <button class="modal-close" onclick="closeModal('viewM')">✕</button>
        </div>
        <div class="modal-body" id="view_body" style="min-height:200px">
            <div style="text-align:center;padding:40px;color:var(--text3)">Loading...</div>
        </div>
    </div>
</div>

<script>
function changeStatus(o) {
    document.getElementById('cs_id').value      = o.order_id;
    document.getElementById('cs_label').textContent = '#' + o.order_id;
    document.getElementById('cs_cust').textContent  = o.customer_name;
    document.getElementById('cs_status').value  = o.delivery_status;

    const badges = {
        pending:   '<span class="badge badge-pending">Pending</span>',
        shipped:   '<span class="badge badge-shipped">Shipped</span>',
        delivered: '<span class="badge badge-delivered">Delivered</span>'
    };
    document.getElementById('cs_current').innerHTML = badges[o.delivery_status] || o.delivery_status;
    openModal('statusM');
}

function viewOrder(id) {
    document.getElementById('view_title').textContent = 'Order #' + id + ' — Details';
    document.getElementById('view_body').innerHTML = '<div style="text-align:center;padding:40px;color:var(--text3)">Loading...</div>';
    openModal('viewM');
    fetch('order_detail.php?id=' + id)
        .then(r => r.text())
        .then(html => document.getElementById('view_body').innerHTML = html)
        .catch(() => document.getElementById('view_body').innerHTML = '<p style="color:red;padding:20px">Failed to load order.</p>');
}
</script>

<?php require_once '../includes/footer.php'; ?>
