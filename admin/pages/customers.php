<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();
$page_title = 'Customer List';

// Task 3: VIEW ONLY — no add/edit/delete
// This table is populated by Student A's Customer Registration module

$search = sanitize($_GET['search'] ?? '');
$where  = $search
    ? "WHERE customer_name LIKE '%$search%' OR customer_email LIKE '%$search%' OR customer_phone LIKE '%$search%'"
    : '';

$result = $db->query("SELECT * FROM customers $where ORDER BY created_at DESC");
$rows   = [];
while ($r = $result->fetch_assoc()) $rows[] = $r;

$total       = count($rows);
$with_orders = $db->query("SELECT COUNT(DISTINCT customer_id) c FROM orders")->fetch_assoc()['c'];
$no_orders   = $total - $with_orders;

require_once '../includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>Customer List</h2>
        <p>Task 3 — View only. Customer data is registered by Student A's module.</p>
    </div>
    <div style="background:var(--blue-bg);border:1px solid #bae0f5;color:var(--blue);padding:8px 16px;border-radius:9px;font-size:13px;font-weight:600">
        👁 View Only
    </div>
</div>

<!-- Info Banner -->
<div class="alert-banner alert-info" style="margin-bottom:20px">
    <span style="font-size:22px">ℹ️</span>
    <div>
        <strong style="font-size:13.5px">Read-Only Access</strong><br>
        <span style="font-size:12.5px;opacity:.9">This list comes from Student A's Customer Registration module. Admin can view customer data but cannot add, edit or delete customers.</span>
    </div>
</div>

<!-- Summary -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px">
    <div class="stat-card">
        <div class="sc-top"><div class="sc-icon b">👥</div></div>
        <div class="sc-val"><?= $total ?></div>
        <div class="sc-lbl">Total Customers</div>
    </div>
    <div class="stat-card">
        <div class="sc-top"><div class="sc-icon g">🛍️</div></div>
        <div class="sc-val"><?= $with_orders ?></div>
        <div class="sc-lbl">Have Ordered</div>
    </div>
    <div class="stat-card">
        <div class="sc-top"><div class="sc-icon o">⏳</div></div>
        <div class="sc-val"><?= $no_orders ?></div>
        <div class="sc-lbl">Never Ordered</div>
    </div>
</div>

<div class="card">
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center">
            <div class="search-bar">
                <span class="si">🔍</span>
                <input type="text" name="search" placeholder="Search name, email, phone..." value="<?= $search ?>">
            </div>
            <?php if ($search): ?>
            <a href="customers.php" class="btn btn-ghost btn-sm">✕ Clear</a>
            <?php endif; ?>
        </form>
        <span style="margin-left:auto;font-size:13px;color:var(--text3)"><?= $total ?> customers</span>
    </div>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Delivery Address</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="7">
                    <div class="empty-state"><div class="ei">👥</div><p>No customers registered yet.<br>Customers register via Student A's module.</p></div>
                </td></tr>
            <?php else: $i = 1; foreach ($rows as $c):
                $oc = $db->query("SELECT COUNT(*) cnt FROM orders WHERE customer_id={$c['customer_id']}")->fetch_assoc()['cnt'];
                $ts = $db->query("SELECT COALESCE(SUM(total_price),0) t FROM orders WHERE customer_id={$c['customer_id']}")->fetch_assoc()['t'];
                $addr = $db->query("SELECT * FROM addresses WHERE customer_id={$c['customer_id']} LIMIT 1")->fetch_assoc();
            ?>
            <tr>
                <td style="color:var(--text3)"><?= $i++ ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:11px">
                        <div style="width:38px;height:38px;border-radius:50%;background:var(--green-bg);border:2px solid var(--green3);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:var(--green);flex-shrink:0">
                            <?= strtoupper(substr($c['customer_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:600"><?= sanitize($c['customer_name']) ?></div>
                            <div style="font-size:12px;color:var(--text3)"><?= sanitize($c['customer_email']) ?></div>
                        </div>
                    </div>
                </td>
                <td style="color:var(--text2)"><?= $c['customer_phone'] ?: '—' ?></td>
                <td style="font-size:12px;color:var(--text2);max-width:180px">
                    <?php if ($addr): ?>
                        <?= sanitize($addr['unit_no'].' '.$addr['street']) ?>,<br>
                        <?= sanitize($addr['city'].', '.$addr['state']) ?>
                    <?php else: ?>
                        <span style="color:var(--text3)">No address</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($oc > 0): ?>
                    <a href="orders.php?customer=<?= $c['customer_id'] ?>" style="color:var(--blue);font-weight:700">
                        <?= $oc ?> orders
                    </a>
                    <?php else: ?>
                    <span style="color:var(--text3)">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <strong style="color:<?= $ts > 0 ? 'var(--green)' : 'var(--text3)' ?>">
                        <?= $ts > 0 ? formatRM($ts) : '—' ?>
                    </strong>
                </td>
                <td style="color:var(--text3);font-size:12px"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
