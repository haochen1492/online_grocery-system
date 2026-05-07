<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();
$page_title = 'Dashboard';

// ── Stats ──
$s_orders    = $db->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$s_revenue   = $db->query("SELECT COALESCE(SUM(total_price),0) t FROM orders")->fetch_assoc()['t'];
$s_customers = $db->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()['c'];
$s_products  = $db->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$s_pending   = $db->query("SELECT COUNT(*) c FROM orders WHERE delivery_status='pending'")->fetch_assoc()['c'];
$s_delivered = $db->query("SELECT COUNT(*) c FROM orders WHERE delivery_status='delivered'")->fetch_assoc()['c'];
$s_paid      = $db->query("SELECT COALESCE(SUM(price),0) t FROM payments WHERE payment_status='completed'")->fetch_assoc()['t'];
$s_admins    = $db->query("SELECT COUNT(*) c FROM admin")->fetch_assoc()['c'];

// ── Recent orders ──
$recent_orders = $db->query("
    SELECT o.order_id, o.order_date, o.total_price, o.delivery_status,
           c.customer_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC LIMIT 6
");

// ── Top products by sales ──
$top_products = $db->query("
    SELECT p.name, p.product_image, SUM(od.quantity) total_sold
    FROM order_details od
    JOIN products p ON od.product_id = p.product_id
    GROUP BY od.product_id ORDER BY total_sold DESC LIMIT 5
");

// ── Low stock ──
$low_stock = $db->query("
    SELECT p.name, p.stock_quantity, p.product_image, c.category_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.stock_quantity <= 15 ORDER BY p.stock_quantity ASC LIMIT 6
");

// ── Status counts for progress ──
$status_data = $db->query("SELECT delivery_status, COUNT(*) cnt FROM orders GROUP BY delivery_status");
$sm = [];
while ($r = $status_data->fetch_assoc()) $sm[$r['delivery_status']] = $r['cnt'];

require_once '../includes/header.php';
?>

<!-- STAT CARDS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="sc-top">
            <div class="sc-icon g">💰</div>
            <span class="sc-tag up">Revenue</span>
        </div>
        <div class="sc-value" style="color:var(--green)"><?= formatRM($s_revenue) ?></div>
        <div class="sc-label">Total Sales</div>
    </div>
    <div class="stat-card">
        <div class="sc-top">
            <div class="sc-icon o">🛍️</div>
            <span class="sc-tag neu"><?= $s_pending ?> pending</span>
        </div>
        <div class="sc-value"><?= number_format($s_orders) ?></div>
        <div class="sc-label">Total Orders</div>
    </div>
    <div class="stat-card">
        <div class="sc-top">
            <div class="sc-icon b">👥</div>
            <span class="sc-tag up">Registered</span>
        </div>
        <div class="sc-value"><?= number_format($s_customers) ?></div>
        <div class="sc-label">Customers</div>
    </div>
    <div class="stat-card">
        <div class="sc-top">
            <div class="sc-icon r">📦</div>
            <span class="sc-tag neu">In catalog</span>
        </div>
        <div class="sc-value"><?= number_format($s_products) ?></div>
        <div class="sc-label">Products</div>
    </div>
</div>

<!-- RECENT ORDERS + ORDER STATUS -->
<div style="display:grid;grid-template-columns:1fr 310px;gap:20px;margin-bottom:22px">
    <div class="card">
        <div class="card-header">
            <span class="card-title">🛒 Recent Orders</span>
            <a href="orders.php" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th><th>Customer</th>
                        <th>Amount</th><th>Status</th><th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($o = $recent_orders->fetch_assoc()): ?>
                <tr>
                    <td><strong style="color:var(--blue)">#<?= $o['order_id'] ?></strong></td>
                    <td style="font-weight:600"><?= sanitize($o['customer_name']) ?></td>
                    <td><strong style="color:var(--green)"><?= formatRM($o['total_price']) ?></strong></td>
                    <td><span class="badge badge-<?= $o['delivery_status'] ?>"><?= ucfirst($o['delivery_status']) ?></span></td>
                    <td style="color:var(--text3);font-size:12px"><?= date('d M Y', strtotime($o['order_date'])) ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Status -->
    <div class="card">
        <div class="card-header"><span class="card-title">📊 Order Status</span></div>
        <div class="card-body">
            <?php
            $statuses = [
                'pending'   => ['🟡','#f59e0b','var(--yellow-bg)'],
                'shipped'   => ['🚚','#1a6fa8','var(--blue-bg)'],
                'delivered' => ['✅','#1e6641','var(--green-bg)'],
            ];
            foreach ($statuses as $s => [$dot, $color, $bg]):
                $cnt = $sm[$s] ?? 0;
                $pct = $s_orders > 0 ? round($cnt/$s_orders*100) : 0;
            ?>
            <div style="margin-bottom:18px">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                    <span style="font-size:13px;font-weight:600"><?= $dot ?> <?= ucfirst($s) ?></span>
                    <span style="font-size:13px;color:var(--text3);font-weight:600"><?= $cnt ?></span>
                </div>
                <div class="status-bar">
                    <div class="status-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                </div>
                <div style="font-size:11px;color:var(--text3);text-align:right;margin-top:3px"><?= $pct ?>%</div>
            </div>
            <?php endforeach; ?>

            <div style="border-top:1px solid var(--border);padding-top:14px;margin-top:8px">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3);margin-bottom:6px">Collected Payments</div>
                <div style="font-family:'Lora',serif;font-size:22px;font-weight:700;color:var(--green)"><?= formatRM($s_paid) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- LOW STOCK + TOP PRODUCTS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <!-- Low Stock -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">⚠️ Low Stock</span>
            <a href="products.php" class="btn btn-ghost btn-sm">Manage</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Product</th><th>Category</th><th>Qty</th></tr></thead>
                <tbody>
                <?php
                $any = false;
                while ($p = $low_stock->fetch_assoc()):
                    $any = true;
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px">
                            <?php if ($p['product_image']): ?>
                            <img src="<?= sanitize($p['product_image']) ?>" class="p-thumb"
                                 onerror="this.style.display='none'" alt="">
                            <?php else: ?><div class="p-thumb-ph">📦</div><?php endif; ?>
                            <span style="font-weight:600;font-size:13px"><?= sanitize($p['name']) ?></span>
                        </div>
                    </td>
                    <td style="color:var(--text3);font-size:12px"><?= sanitize($p['category_name']) ?></td>
                    <td>
                        <span style="font-weight:800;color:<?= $p['stock_quantity']<=5?'var(--red)':'var(--orange)' ?>">
                            <?= $p['stock_quantity'] ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile;
                if (!$any): ?>
                <tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text3)">✅ All products well stocked</td></tr>
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
                <thead><tr><th>Product</th><th>Units Sold</th></tr></thead>
                <tbody>
                <?php
                $any2 = false;
                $rank = 1;
                while ($p = $top_products->fetch_assoc()):
                    $any2 = true;
                    $medals = ['🥇','🥈','🥉','4️⃣','5️⃣'];
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px">
                            <?php if ($p['product_image']): ?>
                            <img src="<?= sanitize($p['product_image']) ?>" class="p-thumb"
                                 onerror="this.style.display='none'" alt="">
                            <?php else: ?><div class="p-thumb-ph">📦</div><?php endif; ?>
                            <div>
                                <div style="font-size:10px;margin-bottom:2px"><?= $medals[$rank-1] ?? '#'.$rank ?></div>
                                <div style="font-weight:600;font-size:13px"><?= sanitize($p['name']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong style="color:var(--blue);font-size:15px"><?= $p['total_sold'] ?></strong>
                        <span style="font-size:11px;color:var(--text3)"> units</span>
                    </td>
                </tr>
                <?php $rank++; endwhile;
                if (!$any2): ?>
                <tr><td colspan="2" style="text-align:center;padding:30px;color:var(--text3)">No sales data yet</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
