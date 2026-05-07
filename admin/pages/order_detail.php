<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo '<p>Invalid order.</p>'; exit; }

$order = $db->query("
    SELECT o.*,
           c.customer_name, c.customer_email, c.customer_phone,
           a.unit_no, a.street, a.city, a.state, a.postal_code, a.country,
           p.payment_status, p.payment_date, p.price as paid_amount
    FROM orders o
    JOIN customers c  ON o.customer_id  = c.customer_id
    LEFT JOIN addresses a ON o.address_id = a.address_id
    LEFT JOIN payments p  ON o.order_id   = p.order_id
    WHERE o.order_id = $id
")->fetch_assoc();

if (!$order) { echo '<p>Order not found.</p>'; exit; }

$items = $db->query("
    SELECT od.*, p.name, p.product_image
    FROM order_details od
    JOIN products p ON od.product_id = p.product_id
    WHERE od.order_id = $id
");
?>

<div class="order-info-grid">
    <div class="info-box">
        <div class="info-box-label">👤 Customer</div>
        <div class="info-box-value"><?= sanitize($order['customer_name']) ?></div>
        <div class="info-box-sub"><?= sanitize($order['customer_email']) ?></div>
        <div class="info-box-sub"><?= sanitize($order['customer_phone'] ?? '') ?></div>
    </div>
    <div class="info-box">
        <div class="info-box-label">📅 Order Info</div>
        <div class="info-box-value"><?= date('d M Y, g:i A', strtotime($order['order_date'])) ?></div>
        <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">
            <span class="badge badge-<?= $order['delivery_status'] ?>"><?= ucfirst($order['delivery_status']) ?></span>
            <span class="badge badge-<?= $order['payment_status'] ?? 'pending' ?>"><?= ucfirst($order['payment_status'] ?? 'No Payment') ?></span>
        </div>
    </div>
    <?php if ($order['street']): ?>
    <div class="info-box" style="grid-column:span 2">
        <div class="info-box-label">📍 Delivery Address</div>
        <div class="info-box-value"><?= sanitize($order['unit_no'].' '.$order['street']) ?></div>
        <div class="info-box-sub"><?= sanitize($order['city'].', '.$order['state'].' '.$order['postal_code'].', '.$order['country']) ?></div>
    </div>
    <?php endif; ?>
</div>

<!-- Order Items -->
<div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3);margin-bottom:10px">Order Items</div>
<div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:18px">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead>
            <tr style="background:var(--bg3)">
                <th style="padding:10px 14px;text-align:left;color:var(--text3);font-size:11px;text-transform:uppercase">Product</th>
                <th style="padding:10px 14px;text-align:center;color:var(--text3);font-size:11px;text-transform:uppercase">Qty</th>
                <th style="padding:10px 14px;text-align:right;color:var(--text3);font-size:11px;text-transform:uppercase">Unit Price</th>
                <th style="padding:10px 14px;text-align:right;color:var(--text3);font-size:11px;text-transform:uppercase">Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($item = $items->fetch_assoc()): ?>
        <tr style="border-top:1px solid var(--border)">
            <td style="padding:11px 14px">
                <div style="display:flex;align-items:center;gap:10px">
                    <?php if ($item['product_image']): ?>
                    <img src="<?= sanitize($item['product_image']) ?>"
                         style="width:38px;height:38px;border-radius:8px;object-fit:cover;border:1px solid var(--border)"
                         onerror="this.style.display='none'" alt="">
                    <?php else: ?>
                    <div style="width:38px;height:38px;border-radius:8px;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:18px">📦</div>
                    <?php endif; ?>
                    <span style="font-weight:600"><?= sanitize($item['name']) ?></span>
                </div>
            </td>
            <td style="padding:11px 14px;text-align:center;color:var(--text2)"><?= $item['quantity'] ?></td>
            <td style="padding:11px 14px;text-align:right"><?= formatRM($item['product_price']) ?></td>
            <td style="padding:11px 14px;text-align:right;font-weight:700;color:var(--green)"><?= formatRM($item['quantity'] * $item['product_price']) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Summary -->
<div style="display:flex;justify-content:flex-end">
    <div style="width:260px">
        <div class="summary-row total">
            <span>Order Total</span>
            <span><?= formatRM($order['total_price']) ?></span>
        </div>
        <?php if ($order['paid_amount']): ?>
        <div class="summary-row" style="color:var(--green);font-weight:600;margin-top:6px">
            <span>Amount Paid</span>
            <span><?= formatRM($order['paid_amount']) ?></span>
        </div>
        <?php if ($order['payment_date']): ?>
        <div style="font-size:11px;color:var(--text3);text-align:right">
            Paid on <?= date('d M Y', strtotime($order['payment_date'])) ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
