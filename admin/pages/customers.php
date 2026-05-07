<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();
$page_title = 'Customer List';

// Task 3: VIEW ONLY — data comes from Student A's registration module
// No add/edit/delete — read only

$search = sanitize($_GET['search'] ?? '');
$where  = $search ? "WHERE customer_name LIKE '%$search%' OR customer_email LIKE '%$search%' OR customer_phone LIKE '%$search%'" : '';

$result = $db->query("SELECT * FROM customers $where ORDER BY created_at DESC");
$rows   = [];
while ($r = $result->fetch_assoc()) $rows[] = $r;

$total_customers = count($rows);

require_once '../includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>Customer List</h2>
        <p>View-only — customer data from Student A's registration module — Task 3</p>
    </div>
    <div style="background:var(--blue-bg);border:1px solid #a8d1ee;color:var(--blue);padding:8px 16px;border-radius:9px;font-size:13px;font-weight:600">
        👁 View Only Mode
    </div>
</div>

<!-- Info Banner -->
<div style="background:var(--blue-bg);border:1px solid #a8d1ee;border-radius:var(--radius);padding:14px 18px;margin-bottom:22px;display:flex;align-items:center;gap:12px">
    <span style="font-size:20px">ℹ️</span>
    <div>
        <div style="font-weight:700;color:var(--blue);font-size:13.5px">Read-Only View</div>
        <div style="font-size:12.5px;color:var(--blue);opacity:.8">This table is populated by Student A's Customer Registration module. Admin can view but not modify customer accounts.</div>
    </div>
</div>

<!-- Summary cards -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px">
    <div class="card" style="padding:18px 20px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3);margin-bottom:8px">Total Customers</div>
        <div style="font-family:'Lora',serif;font-size:26px;font-weight:700;color:var(--blue)"><?= $total_customers ?></div>
    </div>
    <div class="card" style="padding:18px 20px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3);margin-bottom:8px">With Orders</div>
        <?php $with_orders = $db->query("SELECT COUNT(DISTINCT customer_id) c FROM orders")->fetch_assoc()['c']; ?>
        <div style="font-family:'Lora',serif;font-size:26px;font-weight:700;color:var(--green)"><?= $with_orders ?></div>
    </div>
    <div class="card" style="padding:18px 20px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3);margin-bottom:8px">No Orders Yet</div>
        <div style="font-family:'Lora',serif;font-size:26px;font-weight:700;color:var(--orange)"><?= ($total_customers - $with_orders) ?></div>
    </div>
</div>

<div class="card">
    <div class="filters-row">
        <form method="GET" style="display:flex;gap:10px;align-items:center">
            <div class="search-bar">
                <span class="si">🔍</span>
                <input type="text" name="search" placeholder="Search name, email, phone..." value="<?= $search ?>">
            </div>
            <?php if ($search): ?><a href="customers.php" class="btn btn-ghost btn-sm">✕ Clear</a><?php endif; ?>
        </form>
        <span style="margin-left:auto;font-size:13px;color:var(--text3)"><?= $total_customers ?> customers</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="ei">👥</div><p>No customers registered yet</p></div></td></tr>
            <?php else: $i=1; foreach ($rows as $c):
                $oc = $db->query("SELECT COUNT(*) c FROM orders WHERE customer_id={$c['customer_id']}")->fetch_assoc()['c'];
                $ts = $db->query("SELECT COALESCE(SUM(total_price),0) t FROM orders WHERE customer_id={$c['customer_id']}")->fetch_assoc()['t'];
            ?>
            <tr>
                <td style="color:var(--text3)"><?= $i++ ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:11px">
                        <div style="width:38px;height:38px;border-radius:50%;background:var(--green-bg);border:2px solid var(--green3);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:var(--green);flex-shrink:0">
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
                    <?php if ($oc > 0): ?>
                    <a href="orders.php?customer=<?= $c['customer_id'] ?>"
                       style="color:var(--blue);font-weight:700"><?= $oc ?> orders</a>
                    <?php else: ?>
                    <span style="color:var(--text3)">No orders</span>
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
