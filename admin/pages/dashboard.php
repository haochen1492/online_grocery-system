<?php
session_start();
require 'config/db.php';
require 'includes/header.php';
$pageTitle = "Dashboard";

// Stats
$totalCustomers  = $conn->query("SELECT COUNT(*) AS c FROM customers")->fetch_assoc()['c'];
$totalProducts   = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$totalOrders     = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$totalRevenue    = $conn->query("SELECT COALESCE(SUM(total_price),0) AS r FROM orders WHERE delivery_status='delivered'")->fetch_assoc()['r'];

$pendingOrders   = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE delivery_status='pending'")->fetch_assoc()['c'];
$shippedOrders   = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE delivery_status='shipped'")->fetch_assoc()['c'];

// Recent orders
$recentOrders = $conn->query("
    SELECT o.order_id, c.customer_name, o.total_price, o.delivery_status, o.order_date
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC
    LIMIT 8
");

// Low stock
$lowStock = $conn->query("SELECT name, stock_quantity FROM products WHERE stock_quantity <= 10 ORDER BY stock_quantity ASC LIMIT 5");
?>
<?php require 'includes/sidebar.php'; ?>
<div id="content-wrapper">
<?php require 'includes/topbar.php'; ?>
<div class="p-4">

    <!-- Stat Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card h-100" style="background: linear-gradient(135deg,#2ecc71,#27ae60)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-white-50 small mb-1">Total Revenue</div>
                        <div class="stat-value">$<?= number_format($totalRevenue, 2) ?></div>
                    </div>
                    <i class="bi bi-currency-dollar stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card h-100" style="background: linear-gradient(135deg,#3498db,#2980b9)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-white-50 small mb-1">Total Orders</div>
                        <div class="stat-value"><?= $totalOrders ?></div>
                    </div>
                    <i class="bi bi-cart3 stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card h-100" style="background: linear-gradient(135deg,#9b59b6,#8e44ad)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-white-50 small mb-1">Customers</div>
                        <div class="stat-value"><?= $totalCustomers ?></div>
                    </div>
                    <i class="bi bi-people stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card h-100" style="background: linear-gradient(135deg,#e67e22,#d35400)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-white-50 small mb-1">Products</div>
                        <div class="stat-value"><?= $totalProducts ?></div>
                    </div>
                    <i class="bi bi-box-seam stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status Summary -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="content-card card p-3 text-center border-start border-warning border-4">
                <div class="text-warning fw-bold fs-4"><?= $pendingOrders ?></div>
                <div class="text-muted small">Pending Orders</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="content-card card p-3 text-center border-start border-primary border-4">
                <div class="text-primary fw-bold fs-4"><?= $shippedOrders ?></div>
                <div class="text-muted small">Shipped Orders</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="content-card card p-3 text-center border-start border-success border-4">
                <div class="text-success fw-bold fs-4"><?= $totalOrders - $pendingOrders - $shippedOrders ?></div>
                <div class="text-muted small">Delivered Orders</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="content-card card">
                <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-success"></i>Recent Orders</h6>
                </div>
                <div class="card-body px-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#Order</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($row = $recentOrders->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?= $row['order_id'] ?></strong></td>
                                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                    <td>$<?= number_format($row['total_price'], 2) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $row['delivery_status'] ?> px-2 py-1 rounded-pill">
                                            <?= ucfirst($row['delivery_status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= date('d M Y', strtotime($row['order_date'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-lg-4">
            <div class="content-card card">
                <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Low Stock Alert</h6>
                </div>
                <div class="card-body px-4">
                    <?php if ($lowStock->num_rows === 0): ?>
                        <p class="text-muted small">All products are well stocked.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                        <?php while ($p = $lowStock->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="small"><?= htmlspecialchars($p['name']) ?></span>
                                <span class="badge bg-danger rounded-pill"><?= $p['stock_quantity'] ?> left</span>
                            </li>
                        <?php endwhile; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<script src="[cdn.jsdelivr.net](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js)"></script>
<script>
document.getElementById('sidebarToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
});
</script>
</body>
</html>
