<?php
require_once '../includes/auth.php';
require_once '../db.php';
requireLogin();$db=getDB();$page_title='Categories';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';$name=sanitize($_POST['category_name']??'');
    if(!$name)redirect('categories.php','Name required.','error');
    if($action==='add'){
        $st=$db->prepare("INSERT INTO categories(category_name)VALUES(?)");$st->bind_param("s",$name);
        $st->execute()?redirect('categories.php',"Category '$name' added!"):redirect('categories.php','Already exists.','error');
    }elseif($action==='edit'){
        $id=(int)$_POST['category_id'];
        $st=$db->prepare("UPDATE categories SET category_name=? WHERE category_id=?");$st->bind_param("si",$name,$id);$st->execute();
        redirect('categories.php','Category updated!');
    }
}
if(isset($_GET['delete'])){
    $id=(int)$_GET['delete'];
    $used=$db->query("SELECT COUNT(*) c FROM products WHERE category_id=$id")->fetch_assoc()['c'];
    if($used>0)redirect('categories.php',"Cannot delete: $used product(s) use this category.",'error');
    $db->query("DELETE FROM categories WHERE category_id=$id");redirect('categories.php','Deleted.','info');
}
$s=sanitize($_GET['search']??'');$w=$s?"WHERE c.category_name LIKE '%$s%'":'';
$rows=[];$r=$db->query("SELECT c.*,COUNT(p.product_id) pc FROM categories c LEFT JOIN products p ON c.category_id=p.category_id $w GROUP BY c.category_id ORDER BY c.category_name");
while($x=$r->fetch_assoc())$rows[]=$x;
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h2>Categories</h2><p>Task 2 — Admin/Superadmin can add category &amp; product</p></div>
  <button class="btn btn-primary" onclick="openModal('addM')">＋ Add Category</button>
</div>
<div class="card">
  <div class="filters-row">
    <form method="GET" style="display:flex;gap:10px;align-items:center">
      <div class="search-bar"><span class="si">🔍</span><input type="text" name="search" placeholder="Search categories..." value="<?= $s ?>"></div>
      <?php if($s):?><a href="categories.php" class="btn btn-ghost btn-sm">✕</a><?php endif;?>
    </form>
    <span style="margin-left:auto;font-size:13px;color:var(--text3)"><?= count($rows) ?> categories</span>
  </div>
  <div class="tbl-wrap"><table>
    <thead><tr><th>#</th><th>Category Name</th><th>Products</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if(empty($rows)):?><tr><td colspan="4"><div class="empty-state"><div class="ei">🏷️</div><p>No categories yet</p></div></td></tr>
    <?php else:$i=1;foreach($rows as $c):?>
    <tr>
      <td style="color:var(--text3)"><?=$i++?></td>
      <td><div style="display:flex;align-items:center;gap:11px">
        <div style="width:36px;height:36px;background:var(--green-bg);border:1px solid var(--green-lt);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px">🏷️</div>
        <strong><?= sanitize($c['category_name'])?></strong>
      </div></td>
      <td><a href="products.php?category=<?=$c['category_id']?>" class="btn btn-blue btn-sm"><?=$c['pc']?> products →</a></td>
      <td><div style="display:flex;gap:7px">
        <button class="btn btn-orange btn-sm" onclick="editCat(<?=$c['category_id']?>,'<?= addslashes(sanitize($c['category_name']))?>')">✏️ Edit</button>
        <a href="categories.php?delete=<?=$c['category_id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">🗑 Delete</a>
      </div></td>
    </tr>
    <?php endforeach;endif;?>
    </tbody>
  </table></div>
</div>
<!-- ADD -->
<div class="modal-overlay" id="addM"><div class="modal">
  <div class="modal-header"><span class="modal-title">➕ Add Category</span><button class="modal-close" onclick="closeModal('addM')">✕</button></div>
  <form method="POST"><input type="hidden" name="action" value="add">
    <div class="modal-body"><div class="form-group"><label>Category Name *</label><input type="text" name="category_name" required placeholder="e.g. Fruits & Vegetables" autofocus></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('addM')">Cancel</button><button type="submit" class="btn btn-primary">Add</button></div>
  </form>
</div></div>
<!-- EDIT -->
<div class="modal-overlay" id="editM"><div class="modal">
  <div class="modal-header"><span class="modal-title">✏️ Edit Category</span><button class="modal-close" onclick="closeModal('editM')">✕</button></div>
  <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="category_id" id="ec_id">
    <div class="modal-body"><div class="form-group"><label>Category Name *</label><input type="text" name="category_name" id="ec_name" required></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('editM')">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
  </form>
</div></div>
<script>function editCat(id,name){document.getElementById('ec_id').value=id;document.getElementById('ec_name').value=name;openModal('editM')}</script>
<?php require_once '../includes/footer.php'; ?>

