<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireSuperAdmin(); // Task 1: Only superadmin can add admin
$db = getDB();
$page_title = 'Manage Admins';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'admin';

    if ($action === 'add') {
        if (!$username || !$password) {
            redirect('admins.php', 'Username and password are required.', 'error');
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO admin (username, password, role) VALUES (?,?,?)");
        $stmt->bind_param("sss", $username, $hash, $role);
        if ($stmt->execute()) redirect('admins.php', "Admin '$username' added successfully!");
        else redirect('admins.php', 'Username already exists.', 'error');

    } elseif ($action === 'edit') {
        $id = (int)$_POST['admin_id'];
        // Prevent changing own role
        if ($id === (int)$_SESSION['admin_id'] && $role !== 'superadmin') {
            redirect('admins.php', 'You cannot change your own role.', 'error');
        }
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE admin SET username=?, password=?, role=? WHERE admin_id=?");
            $stmt->bind_param("sssi", $username, $hash, $role, $id);
        } else {
            $stmt = $db->prepare("UPDATE admin SET username=?, role=? WHERE admin_id=?");
            $stmt->bind_param("ssi", $username, $role, $id);
        }
        $stmt->execute();
        redirect('admins.php', 'Admin updated!');

    } elseif ($action === 'delete') {
        $id = (int)$_POST['admin_id'];
        if ($id === (int)$_SESSION['admin_id']) {
            redirect('admins.php', 'You cannot delete your own account.', 'error');
        }
        $db->query("DELETE FROM admin WHERE admin_id=$id");
        redirect('admins.php', 'Admin deleted.', 'info');
    }
}

$admins = $db->query("SELECT * FROM admin ORDER BY created_at DESC");
$rows   = [];
while ($r = $admins->fetch_assoc()) $rows[] = $r;

require_once '../includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>Manage Admins</h2>
        <p>Only superadmin can add, edit or remove admin accounts — Task 1</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('addModal')">＋ Add Admin</button>
</div>

<!-- Info Banner -->
<div style="background:var(--purple-bg);border:1px solid #d5bef0;border-radius:var(--radius);padding:14px 18px;margin-bottom:22px;display:flex;align-items:center;gap:12px">
    <span style="font-size:20px">👑</span>
    <div>
        <div style="font-weight:700;color:var(--purple);font-size:13.5px">Superadmin Exclusive Area</div>
        <div style="font-size:12.5px;color:var(--purple);opacity:.8">You are logged in as superadmin. You can create new admin accounts and assign roles.</div>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5"><div class="empty-state"><div class="ei">🔑</div><p>No admins found</p></div></td></tr>
            <?php else: $i=1; foreach ($rows as $a): $isSelf = ($a['admin_id'] == $_SESSION['admin_id']); ?>
            <tr>
                <td style="color:var(--text3)"><?= $i++ ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:11px">
                        <div style="width:38px;height:38px;border-radius:50%;background:<?= $a['role']==='superadmin'?'var(--purple-bg)':'var(--green-bg)' ?>;border:2px solid <?= $a['role']==='superadmin'?'#d5bef0':'var(--green3)' ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:<?= $a['role']==='superadmin'?'var(--purple)':'var(--green)' ?>;flex-shrink:0">
                            <?= strtoupper(substr($a['username'],0,1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:700"><?= sanitize($a['username']) ?></div>
                            <?php if ($isSelf): ?>
                            <div style="font-size:11px;color:var(--green);font-weight:600">← You</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td><span class="badge badge-<?= $a['role'] ?>"><?= ucfirst($a['role']) ?></span></td>
                <td style="color:var(--text3);font-size:12px"><?= date('d M Y, H:i', strtotime($a['created_at'])) ?></td>
                <td>
                    <div style="display:flex;gap:7px;align-items:center">
                        <button class="btn btn-orange btn-sm" onclick='editAdmin(<?= json_encode($a) ?>)'>✏️ Edit</button>
                        <?php if (!$isSelf): ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete admin <?= addslashes(sanitize($a['username'])) ?>?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="admin_id" value="<?= $a['admin_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
                        </form>
                        <?php else: ?>
                        <span style="font-size:11px;color:var(--text3)">Cannot delete self</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">➕ Add New Admin</span>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required placeholder="e.g. admin2">
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required placeholder="Min 6 characters">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role">
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                    <div class="form-note">Superadmins have full access including managing other admins.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">✏️ Edit Admin</span>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="admin_id" id="ea_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" id="ea_username" required>
                </div>
                <div class="form-group">
                    <label>New Password <span style="font-weight:400;text-transform:none;font-size:11px">(leave blank to keep current)</span></label>
                    <input type="password" name="password" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="ea_role">
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editAdmin(a) {
    document.getElementById('ea_id').value       = a.admin_id;
    document.getElementById('ea_username').value = a.username;
    document.getElementById('ea_role').value     = a.role || 'admin';
    openModal('editModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>
