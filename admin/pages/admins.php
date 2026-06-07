<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/send_verification_email.php';
requireSuperAdmin();
$db=getDB();
$page_title='Manage Admins';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=  $_POST['action']??'';
    $username=sanitize($_POST['username']??'');
    $email=   sanitize($_POST['email']??'');
    $password=$_POST['password']??'';
    $role=    in_array($_POST['role']??'',['admin','superadmin'])?$_POST['role']:'admin';
    
    if($action==='add'){
        if(!$username||!$password||!$email) {
          redirect('admins.php','Username, email, and password required.','error');exit;
        }
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
          redirect('admins.php','Invalid email format. Please enter a valid email address.','error');
          exit;
        }
        $hash=password_hash($password,PASSWORD_DEFAULT);
        $st=$db->prepare("SELECT admin_id FROM admin WHERE username=?");
        $st->bind_param("s",$username);
        $st->execute();
        $result=$st->get_result();
        if($result->num_rows>0){
            redirect('admins.php','Username already exists. Please choose a different username.','error');
            exit;
        }
        $st=$db->prepare("INSERT INTO admin (username,email,password,admin_role) VALUES (?,?,?,?)");
        $st->bind_param("ssss",$username,$email,$hash,$role);
        if ($st->execute()){
            redirect('admins.php',"Admin '$username' added!");
        } else {
          if($db->errno===1062) {
            redirect('admins.php','Email already exists. Please use a different email address.','error');
          } else {
            redirect('admins.php','Error adding admin.','error');
          }
        }
    }elseif($action==='edit'){
        $id=(int)$_POST['admin_id'];
        if(empty($email) || !filter_var($email,FILTER_VALIDATE_EMAIL)){
            redirect('admins.php','Invalid email format. Please enter a valid email address.','error');
            exit;
        }
        $stmt=$db->prepare("SELECT * FROM admin WHERE email =? and admin_id !=?");
        $stmt->bind_param("si",$email,$id);
        $stmt->execute();
        if($stmt->get_result()->num_rows>0){
            redirect('admins.php','Email already used on another admin. Please use a different email address.','error');
            exit;
        }
        if($id===$_SESSION['admin_id']&&$role!=='superadmin') {
          redirect('admins.php','Cannot demote yourself.','error');
          exit;
        }
        if($password){
            $hash=password_hash($password,PASSWORD_DEFAULT);
            $st=$db->prepare("UPDATE admin SET username=?,email=?,password=?,admin_role=? WHERE admin_id=?");
            $st->bind_param("ssssi",$username,$email,$hash,$role,$id);
        }else{
            $st=$db->prepare("UPDATE admin SET username=?,email=?,admin_role=? WHERE admin_id=?");
            $st->bind_param("sssi",$username,$email,$role,$id);
        }
        $st->execute();
        redirect('admins.php','Admin updated!');
    }elseif($action==='delete'){
        $id=(int)$_POST['admin_id'];
        if($id===$_SESSION['admin_id']) redirect('admins.php','Cannot delete your own account.','error');
        $db->query("DELETE FROM admin WHERE admin_id=$id");
        redirect('admins.php','Admin deleted.','info');
    }
}
$rows=[];$r=$db->query("SELECT * FROM admin ORDER BY created_at DESC");
while($x=$r->fetch_assoc())$rows[]=$x;
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h2>Manage Admins</h2><p>Task 1 — Only superadmin can add/edit/delete admin accounts</p></div>
  <button class="btn btn-primary" onclick="openModal('addM')">＋ Add Admin</button>
</div>
<div class="alert-banner alert-purple" style="margin-bottom:20px">
  <span style="font-size:20px">👑</span>
  <div><strong style="font-size:13.5px">Superadmin Exclusive</strong><br>
  <span style="font-size:12.5px;opacity:.85">You can create admin accounts and assign roles. Admins can manage products, categories, orders and view customers but cannot access this page.</span></div>
</div>
<div class="card">
  <div class="tbl-wrap"><table>
    <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if(empty($rows)): ?><tr><td colspan="6"><div class="empty-state"><div class="ei">🔑</div><p>No admins</p></div></td></tr>
    <?php else: $i=1;foreach($rows as $a): $self=($a['admin_id']==$_SESSION['admin_id']); ?>
    <tr>
      <td style="color:var(--text3)"><?= $i++ ?></td>
      <td><div style="display:flex;align-items:center;gap:11px">
        <div style="width:38px;height:38px;border-radius:50%;background:<?= $a['admin_role']==='superadmin'?'var(--purple-bg)':'var(--green-bg)' ?>;border:2px solid <?= $a['admin_role']==='superadmin'?'#ddd6fe':'var(--green3)' ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:<?= $a['admin_role']==='superadmin'?'var(--purple)':'var(--green)' ?>;flex-shrink:0"><?= strtoupper(substr($a['username'],0,1)) ?></div>
        <div><div style="font-weight:700"><?= sanitize($a['username']) ?></div><?php if($self): ?><div style="font-size:11px;color:var(--green);font-weight:600">← You</div><?php endif; ?></div>
      </div></td>
      <td><?= sanitize($a['email']) ?></td>
      <td><span class="badge badge-<?= $a['admin_role'] ?>"><?= ucfirst($a['admin_role']) ?></span></td>
      <td style="color:var(--text3);font-size:12px"><?= date('d M Y H:i',strtotime($a['created_at'])) ?></td>
      <td><div style="display:flex;gap:7px">
        <button class="btn btn-orange btn-sm" onclick='editAdmin(<?= json_encode($a) ?>)'>✏️ Edit</button>
        <?php if(!$self): ?>
        <form method="POST" style="display:inline" onsubmit="return confirm('Delete <?= addslashes(sanitize($a['username'])) ?>?')">
          <input type="hidden" name="action" value="delete"><input type="hidden" name="admin_id" value="<?= $a['admin_id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
        </form>
        <?php else: ?><span style="font-size:11px;color:var(--text3)">Cannot delete self</span><?php endif; ?>
      </div></td>
    </tr>
    <?php endforeach;endif; ?>
    </tbody>
  </table></div>
</div>

<!-- ADD -->
<div class="modal-overlay" id="addM">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">➕ Add New Admin</span><button class="modal-close" onclick="closeModal('addM')">✕</button></div>
    <form method="POST"><input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group"><label>Username *</label><input type="text" name="username" required placeholder="e.g. admin2"></div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" placeholder="e.g. admin2@example.com"></div>
        <div class="form-group"><label>Password *</label><input type="password" name="password" required placeholder="Min 6 characters"></div>
        <div class="form-group"><label>Role</label>
          <select name="role"><option value="admin">Admin</option><option value="superadmin">Superadmin</option></select>
          <div class="form-note">Superadmins can also access this Admins page.</div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('addM')">Cancel</button><button type="submit" class="btn btn-primary">Add Admin</button></div>
    </form>
  </div>
</div>

<!-- EDIT -->
<div class="modal-overlay" id="editM">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">✏️ Edit Admin</span><button class="modal-close" onclick="closeModal('editM')">✕</button></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="admin_id" id="ea_id">
      <div class="modal-body">
        <div class="form-group"><label>Username *</label><input type="text" name="username" id="ea_un" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" id="ea_email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" placeholder="e.g. admin2@example.com"></div>
        <div class="form-group"><label>New Password <span style="font-weight:400;text-transform:none;font-size:11px">(leave blank to keep)</span></label><input type="password" name="password" placeholder="••••••••"></div>
        <div class="form-group"><label>Role</label><select name="role" id="ea_role"><option value="admin">Admin</option><option value="superadmin">Superadmin</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('editM')">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
  </div>
</div>
<script>
function editAdmin(a){
  document.getElementById('ea_id').value=a.admin_id;
  document.getElementById('ea_un').value=a.username;
  document.getElementById('ea_email').value=a.email;
  document.getElementById('ea_role').value=a.admin_role;
  openModal('editM');
}
</script>
<?php require_once '../includes/footer.php'; ?>
