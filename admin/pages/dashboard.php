<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db=getDB();
$page_title='Dashboard';

$s_orders    =$db->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$s_revenue   =$db->query("SELECT COALESCE(SUM(total_price),0) t FROM orders")->fetch_assoc()['t'];
$s_customers =$db->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()['c'];
$s_products  =$db->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$s_pending   =$db->query("SELECT COUNT(*) c FROM orders WHERE delivery_status='pending'")->fetch_assoc()['c'];
$s_shipped   =$db->query("SELECT COUNT(*) c FROM orders WHERE delivery_status='shipped'")->fetch_assoc()['c'];
$s_delivered =$db->query("SELECT COUNT(*) c FROM orders WHERE delivery_status='delivered'")->fetch_assoc()['c'];
$s_paid      =$db->query("SELECT COALESCE(SUM(price),0) t FROM payments WHERE payment_status='completed'")->fetch_assoc()['t'];
$s_admins    =$db->query("SELECT COUNT(*) c FROM admin")->fetch_assoc()['c'];
$s_cats      =$db->query("SELECT COUNT(*) c FROM categories")->fetch_assoc()['c'];

$recent=$db->query("SELECT o.order_id,o.order_date,o.total_price,o.delivery_status,c.customer_name FROM orders o JOIN customers c ON o.customer_id=c.customer_id ORDER BY o.order_date DESC LIMIT 7");

$top=$db->query("SELECT p.name,p.product_image,SUM(od.quantity) sold FROM order_details od JOIN products p ON od.product_id=p.product_id GROUP BY od.product_id ORDER BY sold DESC LIMIT 5");

$low=$db->query("SELECT p.name,p.stock_quantity,p.product_image,c.category_name FROM products p JOIN categories c ON p.category_id=c.category_id WHERE p.stock_quantity<=15 ORDER BY p.stock_quantity ASC LIMIT 6");

require_once '../includes/header.php';
?>
<div class="stats-grid">
  <div class="stat-card">
    <div class="sc-top"><div class="sc-icon g">💰</div><span class="sc-tag up">Revenue</span></div>
    <div class="sc-val" style="color:var(--green)"><?= formatRM($s_revenue) ?></div>
    <div class="sc-lbl">Total Sales</div>
  </div>
  <div class="stat-card">
    <div class="sc-top"><div class="sc-icon o">🛍️</div><span class="sc-tag warn"><?= $s_pending ?> pending</span></div>
    <div class="sc-val"><?= number_format($s_orders) ?></div>
    <div class="sc-lbl">Total Orders</div>
  </div>
  <div class="stat-card">
    <div class="sc-top"><div class="sc-icon b">👥</div><span class="sc-tag up">Active</span></div>
    <div class="sc-val"><?= number_format($s_customers) ?></div>
    <div class="sc-lbl">Customers</div>
  </div>
  <div class="stat-card">
    <div class="sc-top"><div class="sc-icon r">📦</div><span class="sc-tag neu"><?= $s_cats ?> cats</span></div>
    <div class="sc-val"><?= number_format($s_products) ?></div>
    <div class="sc-lbl">Products</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:18px;margin-bottom:18px">
  <!-- Recent Orders -->
  <div class="card">
    <div class="card-header"><span class="card-title">🛒 Recent Orders</span><a href="orders.php" class="btn btn-ghost btn-sm">View All →</a></div>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Order ID</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
      <?php while($o=$recent->fetch_assoc()): ?>
      <tr>
        <td><strong style="color:var(--blue)">#<?= $o['order_id'] ?></strong></td>
        <td style="font-weight:600"><?= sanitize($o['customer_name']) ?></td>
        <td><strong style="color:var(--green)"><?= formatRM($o['total_price']) ?></strong></td>
        <td><span class="badge badge-<?= $o['delivery_status'] ?>"><?= ucfirst($o['delivery_status']) ?></span></td>
        <td style="color:var(--text3);font-size:12px"><?= date('d M Y',strtotime($o['order_date'])) ?></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table></div>
  </div>
  <!-- Status -->
  <div class="card">
    <div class="card-header"><span class="card-title">📊 Delivery Status</span></div>
    <div class="card-body">
      <?php foreach(['pending'=>['🟡','#a16207',$s_pending],'shipped'=>['🚚','#1d6fa4',$s_shipped],'delivered'=>['✅','#1a5c38',$s_delivered]] as $s=>[$ic,$col,$cnt]): $p=pct($cnt,$s_orders); ?>
      <div style="margin-bottom:16px">
        <div style="display:flex;justify-content:space-between;margin-bottom:5px">
          <span style="font-size:13px;font-weight:600"><?= $ic ?> <?= ucfirst($s) ?></span>
          <span style="font-size:13px;color:var(--text3);font-weight:600"><?= $cnt ?></span>
        </div>
        <div class="prog-bar"><div class="prog-fill" style="width:<?= $p ?>%;background:<?= $col ?>"></div></div>
        <div style="font-size:10.5px;color:var(--text3);text-align:right;margin-top:2px"><?= $p ?>%</div>
      </div>
      <?php endforeach; ?>
      <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:6px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3);margin-bottom:5px">Collected</div>
        <div style="font-family:'Playfair Display',serif;font-size:21px;font-weight:800;color:var(--green)"><?= formatRM($s_paid) ?></div>
      </div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
  <!-- Low Stock -->
  <div class="card">
    <div class="card-header"><span class="card-title">⚠️ Low Stock</span><a href="products.php" class="btn btn-ghost btn-sm">Manage</a></div>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Product</th><th>Category</th><th>Stock</th></tr></thead>
      <tbody>
      <?php $any=false; while($p=$low->fetch_assoc()): $any=true; ?>
      <tr>
        <td><div style="display:flex;align-items:center;gap:9px">
          <?php if($p['product_image']): ?><img src="<?= sanitize($p['product_image']) ?>" class="p-thumb" onerror="this.style.display='none'" alt="">
          <?php else: ?><div class="p-thumb-ph">📦</div><?php endif; ?>
          <span style="font-weight:600;font-size:13px"><?= sanitize($p['name']) ?></span>
        </div></td>
        <td style="color:var(--text3);font-size:12px"><?= sanitize($p['category_name']) ?></td>
        <td><span style="font-weight:800;color:<?= $p['stock_quantity']<=5?'var(--red)':'var(--orange)' ?>"><?= $p['stock_quantity'] ?></span></td>
      </tr>
      <?php endwhile; if(!$any): ?><tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text3)">✅ All stocked well</td></tr><?php endif; ?>
      </tbody>
    </table></div>
  </div>
  <!-- Top Products -->
  <div class="card">
    <div class="card-header"><span class="card-title">🏆 Top Selling</span></div>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Product</th><th>Units Sold</th></tr></thead>
      <tbody>
      <?php $any2=false;$r=1; while($p=$top->fetch_assoc()): $any2=true; $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; ?>
      <tr>
        <td><div style="display:flex;align-items:center;gap:9px">
          <?php if($p['product_image']): ?><img src="<?= sanitize($p['product_image']) ?>" class="p-thumb" onerror="this.style.display='none'" alt="">
          <?php else: ?><div class="p-thumb-ph">📦</div><?php endif; ?>
          <div><div style="font-size:10px;margin-bottom:1px"><?= $medals[$r-1]??'#'.$r ?></div><div style="font-weight:600;font-size:13px"><?= sanitize($p['name']) ?></div></div>
        </div></td>
        <td><strong style="color:var(--blue);font-size:15px"><?= $p['sold'] ?></strong><span style="font-size:11px;color:var(--text3)"> units</span></td>
      </tr>
      <?php $r++;endwhile; if(!$any2): ?><tr><td colspan="2" style="text-align:center;padding:30px;color:var(--text3)">No sales data yet</td></tr><?php endif; ?>
      </tbody>
    </table></div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
