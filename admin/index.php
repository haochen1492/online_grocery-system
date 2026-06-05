<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
if(isLoggedIn()){header('Location: pages/dashboard.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $u=sanitize($_POST['username']??'');
    $p=$_POST['password']??'';
    if($u&&$p){
        $db=getDB();
        $st=$db->prepare("SELECT * FROM admin WHERE username=? LIMIT 1");
        $st->bind_param("s",$u);$st->execute();
        $row=$st->get_result()->fetch_assoc();
        if($row&&password_verify($p,$row['password'])){
            $_SESSION['admin_id']=$row['admin_id'];
            $_SESSION['admin_username']=$row['username'];
            $_SESSION['admin_role']=$row['admin_role'];
            redirect('pages/dashboard.php','Welcome back, '.$row['username'].'! 👋');
        }else $error='Invalid username or password.';
    }else $error='Please enter both fields.';
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — Infinity Grocer</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
<style>
:root{--g:#1a5c38;--g2:#27764a;--g3:#4caf76;--gbg:#edf7f1;--bg:#f4f6f3;--sur:#fff;--bor:#e4e8e2;--txt:#1a1f1c;--txt2:#5a6a5e;--red:#b91c1c;--rbg:#fef2f2}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;min-height:100vh;display:flex;background:var(--bg)}
/* LEFT */
.left{width:460px;flex-shrink:0;background:var(--g);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 50px;position:relative;overflow:hidden}
.left::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 25% 75%,rgba(76,175,118,.22) 0%,transparent 55%),radial-gradient(circle at 80% 15%,rgba(255,255,255,.05) 0%,transparent 50%)}
.ldots{position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.06) 1px,transparent 1px);background-size:26px 26px}
.lc{position:relative;z-index:1;text-align:center;width:100%}
.lico{width:74px;height:74px;background:rgba(255,255,255,.14);border:2px solid rgba(255,255,255,.18);border-radius:22px;display:flex;align-items:center;justify-content:center;font-size:34px;margin:0 auto 22px}
.lc h1{font-family:'Playfair Display',serif;font-size:34px;font-weight:800;color:#fff;line-height:1.1;margin-bottom:12px}
.lc p{color:rgba(255,255,255,.58);font-size:14px;line-height:1.7;max-width:300px;margin:0 auto}
.tasks{margin-top:32px;display:flex;flex-direction:column;gap:10px;width:100%}
.task{display:flex;align-items:center;gap:11px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.11);border-radius:11px;padding:11px 15px;color:rgba(255,255,255,.8);font-size:13px;font-weight:500;text-align:left}
.task .ti{font-size:18px}
.task strong{color:#fff;font-weight:700}
/* RIGHT */
.right{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 56px}
.box{width:100%;max-width:370px}
.box h2{font-family:'Playfair Display',serif;font-size:26px;font-weight:800;margin-bottom:5px}
.box .sub{color:var(--txt2);font-size:13.5px;margin-bottom:30px}
.fg{margin-bottom:16px}
label{display:block;font-size:11px;font-weight:700;color:#8a9a8e;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px}
.iw{position:relative}
.ii{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none}
input{width:100%;background:var(--sur);border:1.5px solid var(--bor);border-radius:10px;padding:11px 13px 11px 42px;color:var(--txt);font-family:'Inter',sans-serif;font-size:14px;outline:none;transition:all .17s}
input:focus{border-color:var(--g2);box-shadow:0 0 0 3px rgba(39,118,74,.1)}
input::placeholder{color:#ccc}
.err{display:flex;align-items:center;gap:8px;background:var(--rbg);border:1.5px solid #fca5a5;color:var(--red);padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:18px}
.btn-login{width:100%;background:var(--g);color:#fff;border:none;border-radius:10px;padding:13px;font-family:'Inter',sans-serif;font-size:14px;font-weight:700;cursor:pointer;transition:all .17s;margin-top:5px;box-shadow:0 4px 14px rgba(26,92,56,.26)}
.btn-login:hover{background:var(--g2);transform:translateY(-1px);box-shadow:0 6px 20px rgba(26,92,56,.32)}
.forgot-wrap{display:flex;justify-content:flex-end;margin-bottom:4px}
.forgot-link{font-size:12.5px;color:var(--txt2);text-decoration:none;font-weight:500;transition:color .15s}
.forgot-link:hover{color:var(--g2)}
.hint{margin-top:18px;padding:13px 15px;background:var(--gbg);border:1px solid #a7e3be;border-radius:10px;font-size:12.5px;color:var(--g);line-height:1.65}
.hint strong{font-weight:700}
.copy{margin-top:20px;text-align:center;font-size:12px;color:#bbb}
@media(max-width:820px){.left{display:none}.right{padding:28px 20px}}
</style></head><body>
<div class="left">
  <div class="ldots"></div>
  <div class="lc">
    <div class="lico">🛒</div>
    <h1>Infinity Grocer<br>Admin</h1>
    <p>Infinity Grocer Admin Page.</p>
    <div class="tasks">
      <div class="task"><span class="ti">👑</span><div><strong>Option 1</strong> — Superadmin adds &amp; manages admins</div></div>
      <div class="task"><span class="ti">📦</span><div><strong>Option 2</strong> — Add categories &amp; products (visible to customers)</div></div>
      <div class="task"><span class="ti">👥</span><div><strong>Option 3</strong> — View customer list (from Student A)</div></div>
      <div class="task"><span class="ti">🛍️</span><div><strong>Option 4</strong> — View orders &amp; product list</div></div>
      <div class="task"><span class="ti">🔄</span><div><strong>Option 5</strong> — Change order delivery status</div></div>
      <div class="task"><span class="ti">📈</span><div><strong>Option 6</strong> — Generate sales report</div></div>
    </div>
  </div>
</div>
<div class="right">
  <div class="box">
    <h2>Welcome back 👋</h2>
    <p class="sub">Sign in to your Infinity Grocer admin account</p>
    <?php if($error): ?><div class="err">⚠ <?= $error ?></div><?php endif; ?>
    <form method="POST">
      <div class="fg">
        <label>Username</label>
        <div class="iw"><span class="ii">👤</span>
          <input type="text" name="username" placeholder="Enter username" value="<?= sanitize($_POST['username']??'') ?>" required autofocus>
        </div>
      </div>
      <div class="fg">
        <label>Password</label>
        <div class="iw"><span class="ii">🔒</span>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
      </div>
      <div class="forgot-wrap"><a href="forgot_password.php" class="forgot-link">Forgot password?</a></div>
      <button type="submit" class="btn-login">Sign In →</button>
    </form>
    <div class="hint">
      <strong>Superadmin:</strong> superadmin / superadmin123<br>
      <strong>Admin:</strong> admin / admin123
    </div>
    <p class="copy">© <?= date('Y') ?> Infinity Grocer</p>
  </div>
</div>
</body></html>
