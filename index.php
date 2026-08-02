<?php
require_once 'config/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit(); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) { $error = 'Username dan password wajib diisi.'; }
    else {
        global $conn;
        $found = null;
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while ($user = $res->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $found = $user;
                break;
            }
        }
        if ($found) {
            $_SESSION['user_id']=$found['id']; $_SESSION['user_role']=$found['role']; $_SESSION['user_name']=$found['name'];
            header('Location: dashboard.php'); exit();
        } else { $error = 'Username atau password salah.'; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login — AL-SYUKROSMART OPS</title>
<link rel="icon" type="image/png" href="logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root {
  --primary: #059669; /* Emerald 600 */
  --primary-hover: #047857; /* Emerald 700 */
  --bg-color: #f3f4f6; /* Gray 100 */
  --card-bg: #ffffff;
  --text-main: #111827; /* Gray 900 */
  --text-muted: #6b7280; /* Gray 500 */
  --border-color: #e5e7eb; /* Gray 200 */
  --radius: 16px;
}
html, body {
  height: 100%;
  font-family: 'Inter', sans-serif;
  background: var(--bg-color);
  color: var(--text-main);
}
.wrap {
  display: flex;
  min-height: 100vh;
}
/* LEFT SIDE */
.lft {
  width: 45%;
  background: linear-gradient(135deg, #065f46 0%, #059669 100%);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 60px;
  color: #fff;
}
.lft::before {
  content: ''; position: absolute; inset: 0;
  background-image: radial-gradient(circle at 20% 150%, rgba(255,255,255,0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% -20%, rgba(255,255,255,0.15) 0%, transparent 40%);
}
.lft-content {
  position: relative;
  z-index: 1;
  max-width: 480px;
}
.logo-box {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  padding: 12px 20px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.2);
  margin-bottom: 40px;
}
.logo-box img {
  height: 40px;
  width: auto;
}
.logo-text {
  line-height: 1.3;
}
.logo-text strong { display: block; font-size: 14px; font-weight: 700; }
.logo-text span { font-size: 12px; opacity: 0.8; }
.headline {
  font-size: 42px;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -1px;
  margin-bottom: 20px;
}
.sub-txt {
  font-size: 16px;
  line-height: 1.6;
  opacity: 0.9;
  margin-bottom: 40px;
  font-weight: 400;
}
.feat-list {
  list-style: none;
}
.feat-list li {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  font-size: 15px;
  font-weight: 500;
}
.feat-list li:last-child { border-bottom: none; }
.feat-list li svg { width: 22px; height: 22px; stroke-width: 2; flex-shrink: 0; }

/* RIGHT SIDE */
.rgt {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background: var(--bg-color);
}
.login-card {
  background: var(--card-bg);
  width: 100%;
  max-width: 440px;
  padding: 48px;
  border-radius: var(--radius);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
}
.fwelcome {
  margin-bottom: 32px;
}
.fwelcome h2 {
  font-size: 28px;
  font-weight: 700;
  letter-spacing: -0.5px;
  margin-bottom: 8px;
  color: var(--text-main);
}
.fwelcome p {
  color: var(--text-muted);
  font-size: 15px;
}

.falert {
  padding: 14px 16px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 24px;
}
.falert svg { width: 20px; height: 20px; flex-shrink: 0; }
.ferr { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
.fok { background: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }

.fgrp { margin-bottom: 20px; }
.flabel {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-main);
  margin-bottom: 8px;
}
.finwrap {
  position: relative;
}
.finput {
  width: 100%;
  padding: 14px 16px;
  border: 1.5px solid var(--border-color);
  border-radius: 10px;
  font-size: 15px;
  font-family: inherit;
  color: var(--text-main);
  background: #f9fafb;
  outline: none;
  transition: all 0.2s ease;
}
.finput:focus {
  border-color: var(--primary);
  background: #fff;
  box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.1);
}
.finput::placeholder { color: #9ca3af; }
.feye {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-muted);
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.feye svg { width: 20px; height: 20px; stroke-width: 2; }
.btn-submit {
  width: 100%;
  padding: 14px;
  margin-top: 8px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-submit:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 32px 0;
  color: var(--text-muted);
  font-size: 13px;
  font-weight: 500;
}
.divider::before, .divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid var(--border-color);
}
.divider::before { margin-right: 12px; }
.divider::after { margin-left: 12px; }

.dbox {
  background: #f9fafb;
  border: 1px dashed var(--border-color);
  border-radius: 12px;
  padding: 16px;
}
.dbox h4 {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-muted);
  margin-bottom: 16px;
  font-weight: 600;
  text-align: center;
}
.dgrid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.dpill {
  padding: 10px;
  background: #fff;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s ease;
  font-family: inherit;
}
.dpill:hover {
  border-color: var(--primary);
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.dpill strong {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--text-main);
  font-weight: 600;
  margin-bottom: 2px;
}
.dpill strong svg { width: 16px; height: 16px; }
.dpill span {
  font-size: 12px;
  color: var(--text-muted);
}

.reglink {
  text-align: center;
  font-size: 14px;
  color: var(--text-muted);
  margin-top: 32px;
}
.reglink a {
  color: var(--primary);
  font-weight: 600;
  text-decoration: none;
}
.reglink a:hover {
  text-decoration: underline;
}

@media(max-width: 900px) {
  .wrap { flex-direction: column; }
  .lft { width: 100%; padding: 40px 24px; display: flex; align-items: center; justify-content: center; text-align: center; }
  .logo-box { margin-bottom: 24px; }
  .rgt { width: 100%; padding: 24px; }
  .login-card { padding: 32px 24px; }
}
</style>
</head>
<body>
<div class="wrap">
  <div class="lft">
    <div class="lft-content">
      <div class="logo-box">
        <img src="logo.png" alt="Logo Al Syukro">
        <div class="logo-text">
          <strong>Perguruan Islam<br>Al Syukro Universal</strong>
          <span>Tangerang Selatan</span>
        </div>
      </div>
      <h1 class="headline">Sistem Operasional Terpadu</h1>
      <p class="sub-txt">Kelola seluruh kegiatan akademik dan operasional dalam satu platform yang terintegrasi, aman, dan efisien.</p>
      <ul class="feat-list">
        <li><i data-lucide="package"></i> Inventaris &amp; Peminjaman</li>
        <li><i data-lucide="users"></i> SDM &amp; Absensi</li>
        <li><i data-lucide="calendar-days"></i> Event Management</li>
        <li><i data-lucide="wrench"></i> Maintenance Fasilitas</li>
        <li><i data-lucide="folder"></i> Arsip Digital</li>
      </ul>
    </div>
  </div>

  <div class="rgt">
    <div class="login-card">
      <div class="fwelcome">
        <h2>Masuk ke Akun</h2>
        <p>Silakan masukkan kredensial Anda untuk melanjutkan.</p>
      </div>

      <?php if($error): ?><div class="falert ferr"><i data-lucide="alert-triangle"></i> <span><?=htmlspecialchars($error)?></span></div><?php endif; ?>
      <?php if(isset($_GET['registered'])): ?><div class="falert fok"><i data-lucide="check-circle"></i> <span>Registrasi berhasil! Silakan login.</span></div><?php endif; ?>

      <form method="POST">
        <div class="fgrp">
          <label class="flabel">Username atau Email</label>
          <div class="finwrap">
            <input class="finput" type="text" name="username" id="username"
              value="<?=htmlspecialchars($_POST['username']??'')?>" placeholder="Masukkan username..." required autofocus>
          </div>
        </div>
        <div class="fgrp">
          <label class="flabel">Password</label>
          <div class="finwrap">
            <input class="finput" type="password" name="password" id="pw" placeholder="Masukkan password..." required>
            <button type="button" class="feye" id="eyeBtn"><i data-lucide="eye"></i></button>
          </div>
        </div>
        <button type="submit" class="btn-submit">Masuk</button>
      </form>

      <div class="divider">Masuk Cepat (Mode Demo)</div>

      <div class="dbox">
        <h4>Pilih Peran Demo</h4>
        <div class="dgrid">
          <button class="dpill" data-u="admin"      data-p="admin123"><strong><i data-lucide="crown"></i> Administrator</strong><span>admin</span></button>
          <button class="dpill" data-u="adminhr"    data-p="hr123456"><strong><i data-lucide="briefcase"></i> Admin HR</strong><span>adminhr</span></button>
          <button class="dpill" data-u="eo"         data-p="eo123456"><strong><i data-lucide="tent"></i> EO</strong><span>eo</span></button>
          <button class="dpill" data-u="adminfas"   data-p="fas12345"><strong><i data-lucide="building"></i> Fasilitas</strong><span>adminfas</span></button>
          <button class="dpill" data-u="teknisi"    data-p="teks1234"><strong><i data-lucide="wrench"></i> Teknisi</strong><span>teknisi</span></button>
          <button class="dpill" data-u="manager"    data-p="mgr12345"><strong><i data-lucide="graduation-cap"></i> Manager</strong><span>manager</span></button>
          <button class="dpill" data-u="adminarsip" data-p="arsip123"><strong><i data-lucide="folder"></i> Arsip</strong><span>adminarsip</span></button>
          <button class="dpill" data-u="staff1"     data-p="staff123"><strong><i data-lucide="user"></i> Staff</strong><span>staff1</span></button>
        </div>
      </div>
      <p class="reglink">Belum punya akun? <a href="register.php">Daftar Sekarang</a></p>
    </div>
  </div>
</div>
<script>
lucide.createIcons();
document.querySelectorAll('.dpill').forEach(b=>{
  b.addEventListener('click',()=>{document.getElementById('username').value=b.dataset.u;document.getElementById('pw').value=b.dataset.p});
});
const pw=document.getElementById('pw'),eye=document.getElementById('eyeBtn');
eye.addEventListener('click',()=>{
  const type = pw.type === 'password' ? 'text' : 'password';
  pw.type = type;
  eye.innerHTML = type === 'password' ? '<i data-lucide="eye"></i>' : '<i data-lucide="eye-off"></i>';
  lucide.createIcons({ root: eye });
});
</script>
</body>
</html>
