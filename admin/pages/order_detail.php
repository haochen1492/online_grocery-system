<?php
session_start();
require 'config/db.php';
if (!isset($_SESSION['admin_id'])) exit('Unauthorized');

$orderId = (int)($_GET['order_id'] ?? 0);
if (!$orderId) exit('Invalid order.');

// Order header
$order = $conn->query("
    SELECT o.*, c.customer_name, c.customer_email, c.customer_phone,
           a.unit_no, a.street, a.city, a.state, a.postal_code, a.country,
           p.payment_status, p.payment_date
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    JOIN addresses a ON o.address_id  = a.address_id
    LEFT JOIN payments p ON p.order_id = o.order_id
    WHERE o.order_id = $orderId
")->fetch_assoc();

if (!$order) exit('Order not found.');

// Order items
$items = $conn->query("
    SELECT od.quantity, od.product_price, pr.name, pr.product_image
    FROM order_details od
    JOIN products pr ON od.product_id = pr.product_id
    WHERE od.order_id = $orderId
");
?>
<div class="row g-3">
    <!-- Customer & Address -->
    <div class="col-md-6">
        <div class="card border-0 bg-light rounded-3 p-3 h-100">
            <h6 class="fw-bold text-success mb-3"><i class="bi bi-person-circle me-2"></i>Customer</h6>
            <p class="mb-1"><strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
            <p class="mb-1 text-muted small"><?= htmlspecialchars($order['customer_email']) ?></p>
            <p class="mb-0 text-muted small"><?= htmlspecialchars($order['customer_phone'] ?? '—') ?></p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 bg-light rounded-3 p-3 h-100">
            <h6 class="fw-bold text-success mb-3"><i class="bi bi-geo-alt me-2"></i>Delivery Address</h6>
            <p class="mb-0 small">
                <?= htmlspecialchars($order['unit_no']) ?>,
                <?= htmlspecialchars($order['street']) ?>,<br>
                <?= htmlspecialchars($order['city']) ?>,
                <?= htmlspecialchars($order['state']) ?>
                <?= htmlspecialchars($order['postal_code']) ?>,<br>
                <?= htmlspecialchars($order['country']) ?>
            </p>
        </div>
    </div>

    <!-- Order Items -->
    <div class="col-12">
        <h6 class="fw-bold mb-2"><i class="bi bi-basket me-2"></i>Items Ordered</h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr><th>Product</th><th>Unit Price</th><th>Qty</th><th class="text-end">Subtotal</th></tr>
                </thead>
                <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if ($item['product_image'] && file_exists("uploads/products/" . $item['product_image'])): ?>
                            <img src="uploads/products/<?= $item['product_image'] ?>"
                                 style="width:36px;height:36px;object-fit:cover;border-radius:6px" class="me-2">
                        <?php endif; ?>
                        <?= htmlspecialchars($item['name']) ?>
                    </td>
                    <td>$<?= number_format($item['product_price'], 2) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td class="text-end">$<?= number_format($item['product_price'] * $item['quantity'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Total</td>
                        <td class="text-end text-success">$<?= number_format($order['total_price'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Payment & Status -->
    <div class="col-md-6">
        <div class="card border-0 bg-light rounded-3 p-3">
            <h6 class="fw-bold text-success mb-2"><i class="bi bi-credit-card me-2"></i>Payment</h6>
            <p class="mb-1 small">Status:
                <span class="badge badge-<?= $order['payment_status'] ?? 'pending' ?> px-2">
                    <?= ucfirst($order['payment_status'] ?? 'pending') ?>
                </span>
            </p>
            <?php if ($order['payment_date']): ?>
                <p class="mb-0 small text-muted">Paid: <?= date('d M Y, h:i A', strtotime($order['payment_date'])) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 bg-light rounded-3 p-3">
            <h6 class="fw-bold text-success mb-2"><i class="bi bi-truck me-2"></i>Delivery</h6>
            <span class="badge badge-<?= $order['delivery_status'] ?> px-2 py-1">
                <?= ucfirst($order['delivery_status']) ?>
            </span>
            <p class="mb-0 mt-2 small text-muted">Ordered: <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></p>
        </div>
    </div>
</div>
