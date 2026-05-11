<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo '<p style="color:red">Invalid order ID.</p>'; exit; }

$order = $db->query("
    SELECT o.*,
           c.customer_name, c.customer_email, c.customer_phone,
           a.unit_no, a.street, a.city, a.state, a.postal_code, a.country,
           p.payment_status, p.payment_date, p.price AS paid_amount
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    LEFT JOIN addresses a ON o.address_id = a.address_id
    LEFT JOIN payments p  ON o.order_id   = p.order_id
    WHERE o.order_id = $id
")->fetch_assoc();

if (!$order) { echo '<p style="color:red">Order not found.</p>'; exit; }

$items = $db->query("
    SELECT od.*, p.name, p.product_image
    FROM order_details od
    JOIN products p ON od.product_id = p.product_id
    WHERE od.order_id = $id
");
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px">
    <div class="info-box">
        <div class="info-box-lbl">👤 Customer</div>
        <div class="info-box-val"><?= sanitize($order['customer_name']) ?></div>
        <div class="info-box-sub"><?= sanitize($order['customer_email']) ?></div>
        <div class="info-box-sub"><?= sanitize($order['customer_phone'] ?? '—') ?></div>
    </div>
    <div class="info-box">
        <div class="info-box-lbl">📅 Order Info</div>
        <div class="info-box-val"><?= date('d M Y, g:i A', strtotime($order['order_date'])) ?></div>
        <div style="margin-top:8px;display:flex;gap:7px;flex-wrap:wrap">
            <span class="badge badge-<?= $order['delivery_status'] ?>"><?= ucfirst($order['delivery_status']) ?></span>
            <span class="badge badge-<?= $order['payment_status'] ?? 'pending' ?>"><?= ucfirst($order['payment_status'] ?? 'No Payment') ?></span>
        </div>
    </div>

    <?php if ($order['street']): ?>
    <div class="info-box" style="grid-column:span 2">
        <div class="info-box-lbl">📍 Delivery Address</div>
        <div class="info-box-val"><?= sanitize($order['unit_no'].' '.$order['street']) ?></div>
        <div class="info-box-sub"><?= sanitize($order['city'].', '.$order['state'].' '.$order['postal_code'].', '.$order['country']) ?></div>
    </div>
    <?php endif; ?>
</div>

<!-- Items -->
<div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3);margin-bottom:9px">🛍️ Products in This Order</div>
<div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:16px">
    <table style="width:100%;border-collapse:collapse;font-size:13.5px">
        <thead>
            <tr style="background:var(--surface2)">
                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3)">Product</th>
                <th style="padding:10px 14px;text-align:center;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3)">Qty</th>
                <th style="padding:10px 14px;text-align:right;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3)">Unit Price</th>
                <th style="padding:10px 14px;text-align:right;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3)">Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($item = $items->fetch_assoc()): ?>
        <tr style="border-top:1px solid var(--border)">
            <td style="padding:11px 14px">
                <div style="display:flex;align-items:center;gap:10px">
                    <?php if ($item['product_image']): ?>
                    <img src="<?= sanitize($item['product_image']) ?>"
                         style="width:38px;height:38px;border-radius:8px;object-fit:cover;border:1px solid var(--border);flex-shrink:0"
                         onerror="this.style.display='none'" alt="">
                    <?php else: ?>
                    <div style="width:38px;height:38px;border-radius:8px;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">📦</div>
                    <?php endif; ?>
                    <span style="font-weight:600"><?= sanitize($item['name']) ?></span>
                </div>
            </td>
            <td style="padding:11px 14px;text-align:center;color:var(--text2);font-weight:600"><?= $item['quantity'] ?></td>
            <td style="padding:11px 14px;text-align:right;color:var(--text2)"><?= formatRM($item['product_price']) ?></td>
            <td style="padding:11px 14px;text-align:right;font-weight:700;color:var(--green)"><?= formatRM($item['quantity'] * $item['product_price']) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Summary -->
<div style="display:flex;justify-content:flex-end">
    <div style="width:260px">
        <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;color:var(--text2)">
            <span>Order Total</span>
            <span style="font-weight:700"><?= formatRM($order['total_price']) ?></span>
        </div>
        <?php if ($order['paid_amount']): ?>
        <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;color:var(--green);border-top:1px solid var(--border);margin-top:4px">
            <span style="font-weight:600">Amount Paid</span>
            <span style="font-weight:700"><?= formatRM($order['paid_amount']) ?></span>
        </div>
        <?php if ($order['payment_date']): ?>
        <div style="font-size:11px;color:var(--text3);text-align:right;margin-top:3px">
            Paid on <?= date('d M Y, H:i', strtotime($order['payment_date'])) ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
