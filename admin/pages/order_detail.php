<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) die('<p>Invalid order.</p>');

$order = $db->query("
    SELECT o.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone
    FROM orders o JOIN customers c ON o.customer_id=c.id
    WHERE o.id=$id
")->fetch_assoc();

if (!$order) die('<p>Order not found.</p>');

$items = $db->query("
    SELECT oi.*, p.name as product_name, p.unit
    FROM order_items oi JOIN products p ON oi.product_id=p.id
    WHERE oi.order_id=$id
");
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
    <div>
        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px">Customer</div>
        <div style="font-weight:700;font-size:16px"><?= sanitize($order['customer_name']) ?></div>
        <div style="color:var(--text-muted);font-size:13px"><?= $order['customer_email'] ?></div>
        <div style="color:var(--text-muted);font-size:13px"><?= $order['customer_phone'] ?></div>
    </div>
    <div>
        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px">Order Info</div>
        <div style="font-weight:700;font-size:16px;color:var(--accent)"><?= $order['order_number'] ?></div>
        <div style="color:var(--text-muted);font-size:13px"><?= date('d M Y, g:i A', strtotime($order['created_at'])) ?></div>
        <div style="margin-top:6px">
            <span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
            <span class="badge badge-<?= $order['payment_status'] ?>" style="margin-left:6px"><?= ucfirst($order['payment_status']) ?></span>
        </div>
    </div>
</div>

<?php if ($order['delivery_address']): ?>
<div style="background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:12px;margin-bottom:20px">
    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Delivery Address</div>
    <div style="font-size:13px"><?= sanitize($order['delivery_address']) ?></div>
</div>
<?php endif; ?>

<div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">Order Items</div>
<div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:16px">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead>
            <tr style="background:var(--bg3)">
                <th style="padding:10px 14px;text-align:left;color:var(--text-muted);font-size:11px;text-transform:uppercase">Product</th>
                <th style="padding:10px 14px;text-align:center;color:var(--text-muted);font-size:11px;text-transform:uppercase">Qty</th>
                <th style="padding:10px 14px;text-align:right;color:var(--text-muted);font-size:11px;text-transform:uppercase">Price</th>
                <th style="padding:10px 14px;text-align:right;color:var(--text-muted);font-size:11px;text-transform:uppercase">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
            <tr style="border-top:1px solid var(--border)">
                <td style="padding:10px 14px;font-weight:500"><?= sanitize($item['product_name']) ?> <span style="color:var(--text-muted);font-size:11px">(<?= $item['unit'] ?>)</span></td>
                <td style="padding:10px 14px;text-align:center;color:var(--text-muted)"><?= $item['quantity'] ?></td>
                <td style="padding:10px 14px;text-align:right"><?= formatMYR($item['price']) ?></td>
                <td style="padding:10px 14px;text-align:right;font-weight:600"><?= formatMYR($item['subtotal']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div style="display:flex;justify-content:flex-end">
    <div style="width:240px">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;color:var(--text-muted)">
            <span>Subtotal</span><span><?= formatMYR($order['total_amount']) ?></span>
        </div>
        <?php if ($order['discount']>0): ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;color:var(--green)">
            <span>Discount</span><span>-<?= formatMYR($order['discount']) ?></span>
        </div>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:13px;color:var(--text-muted)">
            <span>Delivery</span><span><?= $order['delivery_fee']>0 ? formatMYR($order['delivery_fee']) : 'FREE' ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding-top:10px;border-top:1px solid var(--border);font-weight:700;font-size:16px">
            <span>Grand Total</span><span style="color:var(--green)"><?= formatMYR($order['grand_total']) ?></span>
        </div>
        <div style="margin-top:8px;text-align:right">
            <span style="background:var(--bg3);padding:3px 10px;border-radius:5px;font-size:12px;color:var(--text-muted)">
                <?= strtoupper($order['payment_method']) ?>
            </span>
        </div>
    </div>
</div>
