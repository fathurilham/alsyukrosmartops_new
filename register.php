<?php
require_once 'config/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit(); }
$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name=trim($_POST['name']??''); $username=trim($_POST['username']??'');
    $email=trim($_POST['email']??''); $password=$_POST['password']??'';
    $confirm=$_POST['confirm']??''; $role=$_POST['role']??'staff'; $unit=trim($_POST['unit']??'');
    if (!$name||!$username||!$email||!$password) { $error='Semua field bertanda * wajib diisi.'; }
    elseif ($password!==$confirm) { $error='Password dan konfirmasi tidak cocok.'; }
    elseif (strlen($password)<6) { $error='Password minimal 6 karakter.'; }
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $error='Format email tidak valid.'; }
    else {
        $users=getUsers(); $exists=false;
        foreach($users as $u){if($u['username']===$username||$u['email']===$email){$exists=true;break;}}
        if($exists){$error='Username atau email sudah terdaftar.';}
        else{
            $users[]=['id'=>count($users)+1,'name'=>$name,'username'=>$username,'email'=>$email,
                'password'=>password_hash($password,PASSWORD_DEFAULT),'role'=>$role,'unit'=>$unit?:'Umum','created_at'=>date('Y-m-d')];
            saveUsers($users); header('Location: index.php?registered=1'); exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Registrasi — AL-SYUKROSMART OPS</title>
<link rel="icon" type="image/png" href="logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--gd:#0f4525;--gm:#1a6b3c;--gl:#2d9b5a;--gp:#e8f5ed;--gold:#c9973a;--goldf:#f0c060;
  --white:#fff;--off:#f7faf8;--text:#1a2e22;--muted:#5a7a64;--border:#cce0d4}
html,body{min-height:100%;font-family:'DM Sans',sans-serif;background:var(--off)}

/* PAGE SHELL */
.reg-page{min-height:100vh;display:flex;flex-direction:column;}
.reg-header{background:var(--gd);padding:20px 40px;display:flex;align-items:center;gap:16px;position:relative;overflow:hidden;}
.reg-header::after{content:'';position:absolute;inset:0;
  background-image:radial-gradient(circle,rgba(255,255,255,.05) 1px,transparent 1px);
  background-size:24px 24px;}
.reg-header img{height:44px;position:relative;z-index:1}
.reg-header-t{position:relative;z-index:1}
.reg-header-t strong{display:block;color:#fff;font-size:16px;font-weight:700}
.reg-header-t span{color:rgba(255,255,255,.65);font-size:12px}
.reg-header-back{margin-left:auto;position:relative;z-index:1;
  color:rgba(255,255,255,.7);text-decoration:none;font-size:13.5px;
  padding:8px 16px;border:1px solid rgba(255,255,255,.25);border-radius:8px;transition:.2s}
.reg-header-back:hover{background:rgba(255,255,255,.1);color:#fff}

.reg-body{flex:1;display:flex;align-items:flex-start;justify-content:center;padding:40px 20px;}
.reg-card{background:#fff;border-radius:18px;box-shadow:0 8px 40px rgba(15,69,37,.1);
  width:100%;max-width:700px;overflow:hidden;}
.reg-card-header{background:linear-gradient(135deg,var(--gd),var(--gm));
  padding:28px 36px;color:#fff;}
.reg-card-header h2{font-family:'Playfair Display',serif;font-size:24px;margin-bottom:4px}
.reg-card-header p{opacity:.75;font-size:13.5px}

.reg-form{padding:32px 36px;}

.falert{padding:12px 15px;border-radius:10px;font-size:13.5px;display:flex;align-items:center;gap:10px;margin-bottom:20px}
.ferr{background:#fef0ef;border:1px solid #fccac6;color:#c0392b}

/* steps indicator */
.steps{display:flex;gap:0;margin-bottom:28px;}
.step{flex:1;padding:10px;text-align:center;background:var(--off);border:1px solid var(--border);
  font-size:12px;color:var(--muted);font-weight:600;transition:.2s}
.step:first-child{border-radius:8px 0 0 8px}
.step:last-child{border-radius:0 8px 8px 0}
.step.active{background:var(--gm);color:#fff;border-color:var(--gm)}
.step-num{display:block;font-size:18px;margin-bottom:2px}

.section-title{font-size:13px;font-weight:700;color:var(--gd);margin-bottom:14px;
  display:flex;align-items:center;gap:8px;text-transform:uppercase;letter-spacing:.5px}
.section-title::after{content:'';flex:1;height:1px;background:var(--border)}

.frow{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.frow.c3{grid-template-columns:1fr 1fr 1fr}
.fgrp{margin-bottom:16px}
.flabel{display:block;font-size:12px;font-weight:600;color:var(--text);
  margin-bottom:6px;letter-spacing:.3px;text-transform:uppercase}
.flabel .req{color:#e74c3c}
.finwrap{position:relative}
.ficon{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;opacity:.4;pointer-events:none}
.finput{width:100%;padding:11px 14px 11px 40px;border:1.5px solid var(--border);border-radius:9px;
  font-size:14px;font-family:'DM Sans',sans-serif;color:var(--text);background:var(--off);
  outline:none;transition:.25s;appearance:none}
.finput:focus{border-color:var(--gl);background:#fff;box-shadow:0 0 0 3px rgba(45,155,90,.1)}
.finput.no-icon{padding-left:14px}
.feye{position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;font-size:15px;opacity:.35;padding:4px;transition:.2s}
.feye:hover{opacity:.8}

/* role cards */
.role-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px}
.role-card{padding:12px 10px;border:1.5px solid var(--border);border-radius:9px;
  cursor:pointer;text-align:center;transition:.2s;background:#fff}
.role-card:hover{border-color:var(--gl);background:var(--gp)}
.role-card.selected{border-color:var(--gm);background:var(--gp);box-shadow:0 0 0 3px rgba(45,155,90,.15)}
.role-card input[type=radio]{display:none}
.role-card .rc-icon{font-size:22px;margin-bottom:5px}
.role-card .rc-name{font-size:12px;font-weight:600;color:var(--gd)}
.role-card .rc-desc{font-size:10.5px;color:var(--muted);margin-top:2px}

.btn-reg{width:100%;padding:14px;background:linear-gradient(135deg,var(--gm),var(--gl));
  color:#fff;border:none;border-radius:10px;font-size:15px;font-family:'DM Sans',sans-serif;
  font-weight:600;cursor:pointer;transition:.3s;margin-top:8px}
.btn-reg:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(26,107,60,.3)}

.login-link{text-align:center;font-size:13.5px;color:var(--muted);margin-top:18px}
.login-link a{color:var(--gm);font-weight:600;text-decoration:none}
.login-link a:hover{text-decoration:underline}

@media(max-width:640px){.frow,.frow.c3{grid-template-columns:1fr}.role-grid{grid-template-columns:repeat(2,1fr)}.reg-form{padding:20px}}
</style>
</head>
<body>
<div class="reg-page">
  <header class="reg-header">
    <img src="logo.png" alt="Logo">
    <div class="reg-header-t">
      <strong>AL-SYUKROSMART OPS</strong>
      <span>Perguruan Islam Al Syukro Universal</span>
    </div>
    <a href="index.php" class="reg-header-back">← Kembali ke Login</a>
  </header>

  <div class="reg-body">
    <div class="reg-card">
      <div class="reg-card-header">
        <h2>✨ Buat Akun Baru</h2>
        <p>Daftarkan diri Anda untuk mengakses sistem operasional Al-Syukro Universal</p>
      </div>
      <div class="reg-form">
        <?php if($error): ?><div class="falert ferr">⚠️ <?=htmlspecialchars($error)?></div><?php endif; ?>

        <form method="POST">
          <!-- STEP 1: Identitas -->
          <div class="section-title">👤 Data Identitas</div>
          <div class="frow">
            <div class="fgrp">
              <label class="flabel">Nama Lengkap <span class="req">*</span></label>
              <div class="finwrap"><span class="ficon">👤</span>
                <input class="finput" type="text" name="name" placeholder="Nama lengkap" value="<?=htmlspecialchars($_POST['name']??'')?>" required>
              </div>
            </div>
            <div class="fgrp">
              <label class="flabel">Username <span class="req">*</span></label>
              <div class="finwrap"><span class="ficon">🆔</span>
                <input class="finput" type="text" name="username" placeholder="Username unik" value="<?=htmlspecialchars($_POST['username']??'')?>" required>
              </div>
            </div>
          </div>
          <div class="frow">
            <div class="fgrp">
              <label class="flabel">Email <span class="req">*</span></label>
              <div class="finwrap"><span class="ficon">✉️</span>
                <input class="finput" type="email" name="email" placeholder="email@alsyukro.sch.id" value="<?=htmlspecialchars($_POST['email']??'')?>" required>
              </div>
            </div>
            <div class="fgrp">
              <label class="flabel">Unit Kerja</label>
              <div class="finwrap"><span class="ficon">🏢</span>
                <input class="finput" type="text" name="unit" placeholder="Contoh: Kurikulum" value="<?=htmlspecialchars($_POST['unit']??'')?>">
              </div>
            </div>
          </div>

          <!-- STEP 2: Role -->
          <div class="section-title" style="margin-top:4px;">🎭 Pilih Role / Jabatan <span class="req">*</span></div>
          <div class="role-grid" id="roleGrid">
            <?php
            $roles=[
              ['staff','🧑','Staff','Pinjam, aktivitas, arsip'],
              ['karyawan','👔','Karyawan','Absensi, profil, dokumen'],
              ['admin_hr','🧑‍💼','Admin HR','Kelola SDM & absensi'],
              ['eo','🎪','Event Organizer','Kelola event & laporan'],
              ['admin_fasilitas','🏗️','Admin Fasilitas','Fasilitas & maintenance'],
              ['teknisi','🔧','Teknisi','Tugas & update status'],
              ['admin_arsip','📁','Admin Arsip','Kelola & backup arsip'],
              ['manager','🎓','Manager','Analitik & approval'],
            ];
            foreach($roles as $r):
              $sel=($_POST['role']??'')===$r[0]?'selected':'';
            ?>
            <label class="role-card <?=$sel?>" onclick="selectRole(this,'<?=$r[0]?>')">
              <input type="radio" name="role" value="<?=$r[0]?>" <?=$sel?'checked':''?>>
              <div class="rc-icon"><?=$r[1]?></div>
              <div class="rc-name"><?=$r[2]?></div>
              <div class="rc-desc"><?=$r[3]?></div>
            </label>
            <?php endforeach; ?>
          </div>

          <!-- STEP 3: Password -->
          <div class="section-title" style="margin-top:4px;">🔐 Buat Password</div>
          <div class="frow">
            <div class="fgrp">
              <label class="flabel">Password <span class="req">*</span></label>
              <div class="finwrap"><span class="ficon">🔒</span>
                <input class="finput" type="password" name="password" id="pw1" placeholder="Min. 6 karakter" required>
                <button type="button" class="feye" onclick="togglePw('pw1',this)">👁️</button>
              </div>
            </div>
            <div class="fgrp">
              <label class="flabel">Konfirmasi Password <span class="req">*</span></label>
              <div class="finwrap"><span class="ficon">🔐</span>
                <input class="finput" type="password" name="confirm" id="pw2" placeholder="Ulangi password" required>
                <button type="button" class="feye" onclick="togglePw('pw2',this)">👁️</button>
              </div>
            </div>
          </div>

          <button type="submit" class="btn-reg">📝 &nbsp;Daftar Sekarang</button>
        </form>
        <p class="login-link">Sudah punya akun? <a href="index.php">Login di sini →</a></p>
      </div>
    </div>
  </div>
</div>
<script>
function selectRole(el,val){
  document.querySelectorAll('.role-card').forEach(c=>c.classList.remove('selected'));
  el.classList.add('selected');
  el.querySelector('input').checked=true;
}
function togglePw(id,btn){
  const p=document.getElementById(id);
  p.type=p.type==='password'?'text':'password';
  btn.textContent=p.type==='password'?'👁️':'🙈';
}
</script>
</body>
</html>
