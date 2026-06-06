<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();
$db = getDB();
$page_title = 'Change Password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass     = $_POST['new_password']     ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    // Get current admin from DB
    $id   = (int)$_SESSION['admin_id'];
    $st   = $db->prepare("SELECT password FROM admin WHERE admin_id = ?");
    $st->bind_param("i", $id);
    $st->execute();
    $row  = $st->get_result()->fetch_assoc();

    // Validate
    if (!password_verify($current_pass, $row['password'])) {
        redirect('change_password.php', 'Current password is incorrect.', 'error');
    }
    if (strlen($new_pass) < 6) {
        redirect('change_password.php', 'New password must be at least 6 characters.', 'error');
    }
    if ($new_pass !== $confirm_pass) {
        redirect('change_password.php', 'New passwords do not match.', 'error');
    }
    if (password_verify($new_pass, $row['password'])) {
        redirect('change_password.php', 'New password cannot be the same as current password.', 'error');
    }

    // Save new password
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $st2    = $db->prepare("UPDATE admin SET password = ? WHERE admin_id = ?");
    $st2->bind_param("si", $hashed, $id);
    $st2->execute();

    redirect('change_password.php', 'Password changed successfully! 🎉');
}

require_once '../includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>Change Password</h2>
        <p>Update your admin account password</p>
    </div>
</div>

<div style="max-width:480px">
    <div class="card">
        <div class="card-header">
            <span class="card-title">🔒 Change Your Password</span>
        </div>
        <div class="card-body">

            <!-- Current user info -->
            <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:var(--green-bg);border:1px solid var(--green3);border-radius:10px;margin-bottom:22px">
                <div style="width:42px;height:42px;border-radius:50%;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;flex-shrink:0">
                    <?= strtoupper(substr(getAdminName(), 0, 1)) ?>
                </div>
                <div>
                    <div style="font-weight:700;color:var(--green)"><?= sanitize(getAdminName()) ?></div>
                    <div style="font-size:12px;color:var(--green2)"><?= ucfirst(getAdminRole()) ?></div>
                </div>
            </div>

            <form method="POST" id="cpForm">
                <div class="form-group">
                    <label>Current Password *</label>
                    <div style="position:relative">
                        <input type="password" name="current_password"
                               id="curPass"
                               placeholder="Enter current password" required
                               style="padding-right:42px">
                        <button type="button" onclick="toggleVis('curPass',this)"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text3)">👁</button>
                    </div>
                </div>

                <hr style="border:none;border-top:1px solid var(--border);margin:20px 0">

                <div class="form-group">
                    <label>New Password *</label>
                    <div style="position:relative">
                        <input type="password" name="new_password"
                               id="newPass"
                               placeholder="At least 6 characters" required
                               oninput="checkStr(this.value); checkMatch()"
                               style="padding-right:42px">
                        <button type="button" onclick="toggleVis('newPass',this)"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text3)">👁</button>
                    </div>
                    <!-- Strength bar -->
                    <div style="height:4px;background:var(--border);border-radius:10px;margin-top:8px;overflow:hidden">
                        <div id="strFill" style="height:100%;border-radius:10px;transition:width .3s,background .3s;width:0"></div>
                    </div>
                    <div id="strText" style="font-size:11px;font-weight:600;margin-top:4px;color:var(--text3)"></div>
                </div>

                <div class="form-group">
                    <label>Confirm New Password *</label>
                    <div style="position:relative">
                        <input type="password" name="confirm_password"
                               id="confPass"
                               placeholder="Re-enter new password" required
                               oninput="checkMatch()"
                               style="padding-right:42px">
                        <button type="button" onclick="toggleVis('confPass',this)"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text3)">👁</button>
                    </div>
                    <div id="matchMsg" style="font-size:12px;font-weight:600;margin-top:6px;display:none"></div>
                </div>

                <div style="display:flex;gap:10px;margin-top:4px">
                    <button type="submit" id="subBtn" class="btn btn-primary">🔒 Change Password</button>
                    <a href="dashboard.php" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tips card -->
    <div class="card" style="margin-top:16px">
        <div class="card-header"><span class="card-title">💡 Password Tips</span></div>
        <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:10px">
                <?php
                $tips = [
                    ['✅', 'At least 6 characters long'],
                    ['✅', 'Mix uppercase and lowercase letters'],
                    ['✅', 'Include numbers (e.g. Admin123)'],
                    ['✅', 'Add special characters (e.g. @#$!)'],
                    ['❌', 'Do not use your username as password'],
                    ['❌', 'Do not share your password with anyone'],
                ];
                foreach ($tips as [$icon, $tip]):
                ?>
                <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--text2)">
                    <span><?= $icon ?></span> <?= $tip ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleVis(id, btn) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
    btn.textContent = el.type === 'password' ? '👁' : '🙈';
}

function checkStr(val) {
    const fill = document.getElementById('strFill');
    const text = document.getElementById('strText');
    if (!val) { fill.style.width = '0'; text.textContent = ''; return; }
    let s = 0;
    if (val.length >= 6)  s++;
    if (val.length >= 10) s++;
    if (/[A-Z]/.test(val)) s++;
    if (/[0-9]/.test(val)) s++;
    if (/[^A-Za-z0-9]/.test(val)) s++;
    const lv = [
        {p:'20%',c:'#ef4444',l:'🔴 Very Weak'},
        {p:'40%',c:'#f97316',l:'🟠 Weak'},
        {p:'60%',c:'#eab308',l:'🟡 Fair'},
        {p:'80%',c:'#22c55e',l:'🟢 Strong'},
        {p:'100%',c:'#16a34a',l:'💪 Very Strong'},
    ][s-1] || {p:'20%',c:'#ef4444',l:'🔴 Very Weak'};
    fill.style.width = lv.p;
    fill.style.background = lv.c;
    text.textContent = lv.l;
    text.style.color = lv.c;
}

function checkMatch() {
    const p1  = document.getElementById('newPass').value;
    const p2  = document.getElementById('confPass').value;
    const msg = document.getElementById('matchMsg');
    const btn = document.getElementById('subBtn');
    if (!p2) { msg.style.display = 'none'; btn.disabled = false; return; }
    if (p1 === p2) {
        msg.textContent = '✓ Passwords match';
        msg.style.cssText = 'display:block;color:var(--green);font-size:12px;font-weight:600;margin-top:6px';
        btn.disabled = false;
    } else {
        msg.textContent = '✕ Passwords do not match';
        msg.style.cssText = 'display:block;color:var(--red);font-size:12px;font-weight:600;margin-top:6px';
        btn.disabled = true;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
