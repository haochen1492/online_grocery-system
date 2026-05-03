<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$db = getDB();
$page_title = 'Dashboard';

$total_orders = $db->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$total_revenue = $db->query("SELECT SUM(grand_total) as t FROM orders WHERE payment_status='paid'")->fetch_assoc()['t'] ?? 0;
$total_customers = $db->query("SELECT COUNT(*) as c FROM customers")->fetch_assoc()['c'];
$total_products = $db->query("SELECT COUNT(*) as c FROM products WHERE status='active'")->fetch_assoc()['c'];
$pending_orders = $db->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];

$recent_orders = $db->query("
    SELECT o.*, c.name as customer_name
    FROM orders o JOIN customers c ON o.customer_id = c.id
    ORDER BY o.created_at DESC LIMIT 7
");

$low_stock = $db->query("SELECT * FROM products WHERE stock <= 20 AND status='active' ORDER BY stock ASC LIMIT 6");

$status_data = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
$status_counts = [];
while ($r = $status_data->fetch_assoc()) $status_counts[$r['status']] = $r['cnt'];

require_once '../includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-icon">💰</div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value" style="color:var(--green)"><?= formatMYR($total_revenue) ?></div>
        <div class="stat-change">📈 From paid orders</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon">🛒</div>
        <div class="stat-label">Total Orders</div>
        <div class="stat-value" style="color:var(--accent)"><?= $total_orders ?></div>
        <div class="stat-change">⏳ <?= $pending_orders ?> pending</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon">👥</div>
        <div class="stat-label">Customers</div>
        <div class="stat-value" style="color:var(--orange)"><?= $total_customers ?></div>
        <div class="stat-change">Registered users</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">📦</div>
        <div class="stat-label">Active Products</div>
        <div class="stat-value" style="color:var(--purple)"><?= $total_products ?></div>
        <div class="stat-change">In store catalog</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;margin-bottom:20px">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Orders</span>
            <a href="orders.php" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($o = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= $o['order_number'] ?></strong></td>
                        <td><?= sanitize($o['customer_name']) ?></td>
                        <td><?= formatMYR($o['grand_total']) ?></td>
                        <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                        <td style="color:var(--text-muted)"><?= timeAgo($o['created_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Order Status</span>
        </div>
        <div class="card-body">
            <?php
            $statuses = ['pending'=>'🟡','confirmed'=>'🔵','processing'=>'🔵','shipped'=>'🟣','delivered'=>'🟢','cancelled'=>'🔴'];
            foreach ($statuses as $s => $dot):
                $count = $status_counts[$s] ?? 0;
                $pct = $total_orders > 0 ? round($count/$total_orders*100) : 0;
            ?>
            <div style="margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px">
                    <span style="font-size:13px;font-weight:500"><?= $dot ?> <?= ucfirst($s) ?></span>
                    <span style="font-size:13px;color:var(--text-muted)"><?= $count ?></span>
                </div>
                <div style="height:5px;background:var(--bg3);border-radius:10px;overflow:hidden">
                    <div style="height:100%;width:<?= $pct ?>%;background:var(--green);border-radius:10px"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">⚠️ Low Stock Alert</span>
        <a href="products.php" class="btn btn-ghost btn-sm">Manage Products</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th></tr></thead>
            <tbody>
            <?php
            $no_low = true;
            while($p = $low_stock->fetch_assoc()):
                $no_low = false;
                $cat = $db->query("SELECT name FROM categories WHERE id={$p['category_id']}")->fetch_assoc()['name'] ?? '-';
            ?>
                <tr>
                    <td><strong><?= sanitize($p['name']) ?></strong></td>
                    <td style="color:var(--text-muted)"><?= $cat ?></td>
                    <td><?= formatMYR($p['price']) ?></td>
                    <td>
                        <span style="color:<?= $p['stock']<=5?'var(--red)':'var(--yellow)' ?>;font-weight:700">
                            <?= $p['stock'] ?> left
                        </span>
                    </td>
                    <td><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($no_low): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:30px">✅ All products have sufficient stock</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
