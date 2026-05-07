<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();
$page_title = 'Dashboard';

// Stats
$total_orders    = $db->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$total_revenue   = $db->query("SELECT COALESCE(SUM(total_price),0) t FROM orders")->fetch_assoc()['t'];
$total_customers = $db->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()['c'];
$total_products  = $db->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$pending_orders  = $db->query("SELECT COUNT(*) c FROM orders WHERE delivery_status='pending'")->fetch_assoc()['c'];
$total_payments  = $db->query("SELECT COALESCE(SUM(price),0) t FROM payments WHERE payment_status='completed'")->fetch_assoc()['t'];

// Recent orders
$recent = $db->query("
    SELECT o.order_id, o.order_date, o.total_price, o.delivery_status,
           c.customer_name, c.customer_email
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC LIMIT 8
");

// Low stock
$low_stock = $db->query("
    SELECT p.product_id, p.name, p.stock_quantity, p.product_image, cat.category_name
    FROM products p
    JOIN categories cat ON p.category_id = cat.category_id
    WHERE p.stock_quantity <= 10
    ORDER BY p.stock_quantity ASC LIMIT 6
");

// Order status breakdown
$status_q = $db->query("SELECT delivery_status, COUNT(*) cnt FROM orders GROUP BY delivery_status");
$status_map = [];
while ($r = $status_q->fetch_assoc()) $status_map[$r['delivery_status']] = $r['cnt'];

// Top products
$top_products = $db->query("
    SELECT p.name, p.product_image, SUM(od.quantity) sold, SUM(od.quantity * od.product_price) revenue
    FROM order_details od
    JOIN products p ON od.product_id = p.product_id
    GROUP BY od.product_id
    ORDER BY sold DESC LIMIT 5
");

require_once '../includes/header.php';
?>

<!-- STAT CARDS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-top">
            <div class="stat-icon green">💰</div>
            <span class="stat-trend up">+12%</span>
        </div>
        <div class="stat-value"><?= formatRM($total_revenue) ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top">
            <div class="stat-icon orange">🛒</div>
            <span class="stat-trend up">+5%</span>
        </div>
        <div class="stat-value"><?= number_format($total_orders) ?></div>
        <div class="stat-label">Total Orders · <?= $pending_orders ?> pending</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top">
            <div class="stat-icon blue">👥</div>
            <span class="stat-trend up">+8%</span>
        </div>
        <div class="stat-value"><?= number_format($total_customers) ?></div>
        <div class="stat-label">Customers</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top">
            <div class="stat-icon red">📦</div>
            <span class="stat-trend down">-2%</span>
        </div>
        <div class="stat-value"><?= number_format($total_products) ?></div>
        <div class="stat-label">Products</div>
    </div>
</div>

<!-- RECENT ORDERS + ORDER STATUS -->
<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;margin-bottom:22px">
    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Orders</span>
            <a href="orders.php" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($o = $recent->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= $o['order_id'] ?></strong></td>
                    <td>
                        <div style="font-weight:600"><?= sanitize($o['customer_name']) ?></div>
                        <div style="font-size:11px;color:var(--text3)"><?= sanitize($o['customer_email']) ?></div>
                    </td>
                    <td><strong style="color:var(--green)"><?= formatRM($o['total_price']) ?></strong></td>
                    <td><span class="badge badge-<?= $o['delivery_status'] ?>"><?= ucfirst($o['delivery_status']) ?></span></td>
                    <td style="color:var(--text3);font-size:12px"><?= date('d M Y', strtotime($o['order_date'])) ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="card">
        <div class="card-header"><span class="card-title">Order Status</span></div>
        <div class="card-body">
            <?php
            $statuses = [
                'pending'   => ['🟡', '#f59e0b'],
                'shipped'   => ['🔵', '#3b82f6'],
                'delivered' => ['🟢', '#22c55e'],
            ];
            foreach ($statuses as $s => [$dot, $color]):
                $cnt = $status_map[$s] ?? 0;
                $pct = $total_orders > 0 ? round($cnt / $total_orders * 100) : 0;
            ?>
            <div style="margin-bottom:18px">
                <div style="display:flex;justify-content:space-between;margin-bottom:7px">
                    <span style="font-size:13px;font-weight:600"><?= $dot ?> <?= ucfirst($s) ?></span>
                    <span style="font-size:13px;color:var(--text3)"><?= $cnt ?></span>
                </div>
                <div style="height:6px;background:var(--bg3);border-radius:10px;overflow:hidden">
                    <div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:10px;transition:width 0.8s ease"></div>
                </div>
                <div style="font-size:11px;color:var(--text3);margin-top:3px;text-align:right"><?= $pct ?>%</div>
            </div>
            <?php endforeach; ?>

            <div style="border-top:1px solid var(--border);padding-top:14px;margin-top:6px">
                <div style="font-size:12px;color:var(--text3);margin-bottom:4px">Paid Revenue</div>
                <div style="font-family:'Playfair Display',serif;font-size:22px;font-weight:800;color:var(--green)"><?= formatRM($total_payments) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- LOW STOCK + TOP PRODUCTS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <!-- Low Stock -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">⚠️ Low Stock Alert</span>
            <a href="products.php" class="btn btn-ghost btn-sm">Manage</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Product</th><th>Category</th><th>Stock</th></tr></thead>
                <tbody>
                <?php
                $has = false;
                while ($p = $low_stock->fetch_assoc()):
                    $has = true;
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <?php if ($p['product_image']): ?>
                            <img src="<?= sanitize($p['product_image']) ?>" class="product-thumb"
                                 onerror="this.style.display='none'" alt="">
                            <?php else: ?>
                            <div class="product-thumb-placeholder">📦</div>
                            <?php endif; ?>
                            <span style="font-weight:600"><?= sanitize($p['name']) ?></span>
                        </div>
                    </td>
                    <td style="color:var(--text3)"><?= sanitize($p['category_name']) ?></td>
                    <td>
                        <span style="font-weight:700;color:<?= $p['stock_quantity']<=5?'var(--red)':'var(--accent2)' ?>">
                            <?= $p['stock_quantity'] ?> left
                        </span>
                    </td>
                </tr>
                <?php endwhile;
                if (!$has): ?>
                <tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text3)">✅ All products well-stocked</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Products -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🏆 Top Selling Products</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Product</th><th>Sold</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php
                $has2 = false;
                while ($p = $top_products->fetch_assoc()):
                    $has2 = true;
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <?php if ($p['product_image']): ?>
                            <img src="<?= sanitize($p['product_image']) ?>" class="product-thumb"
                                 onerror="this.style.display='none'" alt="">
                            <?php else: ?>
                            <div class="product-thumb-placeholder">📦</div>
                            <?php endif; ?>
                            <span style="font-weight:600"><?= sanitize($p['name']) ?></span>
                        </div>
                    </td>
                    <td style="font-weight:700;color:var(--blue)"><?= $p['sold'] ?></td>
                    <td style="font-weight:700;color:var(--green)"><?= formatRM($p['revenue']) ?></td>
                </tr>
                <?php endwhile;
                if (!$has2): ?>
                <tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text3)">No sales data yet</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
