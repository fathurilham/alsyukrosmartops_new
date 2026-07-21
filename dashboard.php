<?php
require_once 'config/auth.php';
requireLogin();

$user      = getCurrentUser();
$role      = $user['role'];
$modules   = getRoleModules($role);
$roleName  = getRoleName($role);
$roleColor = getRoleColor($role);
$mod       = $_GET['mod'] ?? '';
$sub       = $_GET['sub'] ?? '';
$userInit  = strtoupper(substr($user['name'] ?? 'U', 0, 1));
$userName  = htmlspecialchars($user['name'] ?? 'User');
$userUnit  = htmlspecialchars($user['unit'] ?? 'Umum');
$today     = date('d F Y');
$dayNames  = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
$dayName   = $dayNames[date('l')];

// ACCESS CONTROL
$accessMap = ['inventory'=>'inventory','karyawan'=>'karyawan','absensi'=>'absensi',
    'aktivitas'=>'aktivitas','event'=>'event','maintenance'=>'maintenance',
    'arsip'=>'arsip','laporan'=>'laporan','notifikasi'=>'*','users'=>'admin_only','profile'=>'*'];
if ($mod && !in_array($mod,['','profile','notifikasi'])) {
    $req = $accessMap[$mod] ?? $mod;
    if ($req==='admin_only' && $role!=='admin') $mod=$sub='';
    elseif ($req!=='*' && $req!=='admin_only' && !in_array($req,$modules) && $role!=='admin') $mod=$sub='';
}

$modTitles=[''=>'Dashboard','inventory'=>'Inventaris & Peminjaman','karyawan'=>'Manajemen Karyawan',
    'absensi'=>'Absensi','aktivitas'=>'Aktivitas Harian','event'=>'Event Management',
    'maintenance'=>'Maintenance Fasilitas','arsip'=>'Arsip Digital','laporan'=>'Laporan & Analitik',
    'notifikasi'=>'Notifikasi','users'=>'Kelola Pengguna','profile'=>'Profil Saya'];
$subTitles=['barang'=>'Kelola Data Barang','approval'=>'Persetujuan Peminjaman','laporan_inv'=>'Laporan Inventaris',
    'pinjam'=>'Pinjam Barang','kembali'=>'Kembalikan Barang','riwayat'=>'Riwayat Peminjaman',
    'data_karyawan'=>'Kelola Data Karyawan','rekap_absensi'=>'Rekap Absensi',
    'monitoring_aktivitas'=>'Monitoring Aktivitas','monitoring_kinerja'=>'Monitoring Kinerja',
    'profil'=>'Lihat/Edit Profil','absensi_harian'=>'Absensi Harian','cek_absensi'=>'Cek Absensi',
    'input_aktivitas'=>'Input Aktivitas Harian','riwayat_aktivitas'=>'Riwayat Aktivitas',
    'buat_event'=>'Buat Event','kelola_event'=>'Kelola/Edit Event','assign'=>'Assign Personel & Fasilitas',
    'update_status'=>'Update Status Event','laporan_event'=>'Laporan Event',
    'approval_event'=>'Approval Event','monitoring_event'=>'Monitoring Event',
    'daftar_event'=>'Daftar Event','evaluasi_event'=>'Evaluasi Event',
    'event_saya'=>'Event Saya','reminder'=>'Lihat Reminder',
    'kelola_fasilitas'=>'Kelola Data Fasilitas','jadwal_maint'=>'Jadwal Maintenance',
    'laporan_maint'=>'Laporan Maintenance','tugas_maint'=>'Lihat Tugas',
    'proses_maint'=>'Proses/Update Status','request_maint'=>'Request Maintenance',
    'lacak_request'=>'Lacak Request','kelola_arsip'=>'Kelola Arsip Digital',
    'klasifikasi'=>'Kelola Klasifikasi Arsip','pencarian_arsip'=>'Pencarian Arsip',
    'backup_restore'=>'Backup & Restore','cari_arsip'=>'Cari Arsip',
    'upload_dok'=>'Upload Dokumen','riwayat_arsip'=>'Riwayat Upload',
    'lihat_arsip'=>'Lihat Arsip','laporan_arsip'=>'Laporan Arsip',
    'dashboard_analitik'=>'Dashboard Analitik','generate_laporan'=>'Generate Laporan Analitik',
    'laporan_sdm'=>'Laporan SDM','laporan_kinerja'=>'Laporan Kinerja'];
$pageTitle = ($sub && isset($subTitles[$sub])) ? $subTitles[$sub] : ($modTitles[$mod] ?? 'Dashboard');

function statusBadge($s){return match($s){'tersedia','aktif','hadir','selesai','berhasil'=>'<span class="badge badge-success">'.ucfirst($s).'</span>','dipinjam','persiapan','diproses'=>'<span class="badge badge-warning">'.ucfirst($s).'</span>','maintenance','perbaikan','menunggu','pending'=>'<span class="badge badge-danger">'.ucfirst($s).'</span>','berlangsung','terjadwal','dalam proses'=>'<span class="badge badge-info">'.ucfirst($s).'</span>','cuti','izin'=>'<span class="badge badge-purple">'.ucfirst($s).'</span>',default=>'<span class="badge badge-secondary">'.ucfirst($s).'</span>'};}
function tabBtn($k,$cur,$lbl,$url){$c=$cur===$k?'active':'';return "<a href='$url' class='sub-tab $c'>$lbl</a>";}


?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — AL-SYUKROSMART OPS</title>
<link rel="icon" type="image/png" href="logo.png">
<link rel="stylesheet" href="css/style.css">
<style>
.page-hero {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    border-radius: 16px;
    padding: 24px 32px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 28px;
    box-shadow: 0 10px 20px -5px rgba(5, 150, 105, 0.3);
}
.page-hero h2 { font-size: 24px; font-weight: 700; margin-bottom: 6px; letter-spacing: -0.5px; }
.page-hero p { font-size: 15px; opacity: 0.9; font-weight: 400; }
.page-hero small { font-size: 13px; opacity: 0.75; display: block; margin-top: 6px; }
.page-hero img { height: 56px; opacity: 0.95; filter: brightness(0) invert(1); }

.breadcrumb { font-size: 14px; color: var(--text-light); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-weight: 500; }
.breadcrumb a { color: var(--text-light); text-decoration: none; transition: var(--transition); }
.breadcrumb a:hover { color: var(--primary); }
.breadcrumb .cur { color: var(--text); font-weight: 600; }

.sub-tab { padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; border: 1px solid var(--border); background: white; color: var(--text-light); transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
.sub-tab:hover { border-color: var(--primary-light); color: var(--primary); background: #f0fdf4; }
.sub-tab.active { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2); }
.sub-tabs { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }

.info-box { border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px; font-size: 14px; font-weight: 500; line-height: 1.5; }
.info-box.blue { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; }
.info-box.green { background: #f0fdf4; border: 1px solid #bbf7d0; color: #14532d; }
.info-box.yellow { background: #fefce8; border: 1px solid #fef08a; color: #713f12; }
.info-box.red { background: #fef2f2; border: 1px solid #fecaca; color: #7f1d1d; }

.kpi-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-bottom: 28px; }
.kpi { background: white; border-radius: 16px; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); transition: var(--transition); display: flex; flex-direction: column; }
.kpi:hover { transform: translateY(-2px); box-shadow: var(--shadow-hover); }
.kpi .kpi-val { font-size: 32px; font-weight: 800; line-height: 1.1; margin-bottom: 4px; color: var(--text); }
.kpi .kpi-lbl { font-size: 14px; color: var(--text-light); font-weight: 500; }
.kpi .kpi-chg { font-size: 12px; font-weight: 600; margin-top: auto; padding-top: 12px; }

.kpi.k-green .kpi-val { color: var(--success); }
.kpi.k-blue .kpi-val { color: var(--info); }
.kpi.k-orange .kpi-val { color: var(--warning); }
.kpi.k-red .kpi-val { color: var(--danger); }
.kpi.k-purple .kpi-val { color: #8b5cf6; }
.kpi.k-teal .kpi-val { color: #14b8a6; }

.form-card { background: white; border-radius: 16px; padding: 30px; box-shadow: var(--shadow); border: 1px solid var(--border); }
.form-card h3 { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; }
.f-row { display: grid; gap: 16px; margin-bottom: 16px; }
.f-row.c2 { grid-template-columns: 1fr 1fr; } .f-row.c3 { grid-template-columns: 1fr 1fr 1fr; } .f-row.c1 { grid-template-columns: 1fr; }
.f-field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text); }
.f-field .req { color: var(--danger); }
.f-field input, .f-field select, .f-field textarea { width: 100%; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; color: var(--text); background: #f9fafb; transition: var(--transition); outline: none; font-family: inherit; appearance: none; }
.f-field input:focus, .f-field select:focus, .f-field textarea:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1); }
.f-field textarea { resize: vertical; min-height: 100px; }
.f-actions { display: flex; gap: 12px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid var(--border); margin-top: 8px; }

.tbl { width: 100%; border-collapse: collapse; font-size: 14px; }
.tbl thead th { background: #f9fafb; padding: 14px 16px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-light); font-weight: 700; border-bottom: 1px solid var(--border); white-space: nowrap; }
.tbl tbody td { padding: 16px; border-bottom: 1px solid var(--border); vertical-align: middle; color: var(--text); }
.tbl tbody tr:hover { background: #f3f4f6; }
.tbl tbody tr:last-child td { border: none; }

.progress { height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
.progress-bar { height: 100%; border-radius: 4px; transition: width 0.8s ease; }
.tl-item { display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--border); }
.tl-item:last-child { border: none; }
.tl-dot { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }

.quick-actions { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 16px; margin-bottom: 28px; }
.qa-btn { background: white; border: 1px solid var(--border); border-radius: 16px; padding: 20px 16px; text-align: center; cursor: pointer; text-decoration: none; color: var(--text); transition: var(--transition); display: block; box-shadow: var(--shadow); }
.qa-btn:hover { border-color: var(--primary-light); background: #f0fdf4; transform: translateY(-3px); box-shadow: var(--shadow-hover); }
.qa-btn .qa-icon { font-size: 28px; margin-bottom: 10px; display: block; }
.qa-btn .qa-label { font-size: 13px; font-weight: 600; color: var(--text); }

.modal-overlay { position: fixed; inset: 0; background: rgba(17, 24, 39, 0.6); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; visibility: hidden; transition: 0.3s ease; }
.modal-overlay.open { opacity: 1; visibility: visible; }
.modal { background: white; border-radius: 20px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; transform: scale(0.95); transition: 0.3s ease; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
.modal-overlay.open .modal { transform: scale(1); }
.modal-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; background: white; z-index: 1; }
.modal-header h3 { font-size: 18px; font-weight: 700; color: var(--text); }
.modal-close { width: 32px; height: 32px; border: none; background: #f3f4f6; border-radius: 8px; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: 0.2s ease; color: var(--text-muted); }
.modal-close:hover { background: #fee2e2; color: var(--danger); }
.modal-body { padding: 24px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 12px; background: #f9fafb; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px; }
@keyframes slideIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@media(max-width:900px) { #menuToggle { display: flex!important; } .f-row.c2, .f-row.c3 { grid-template-columns: 1fr; } .kpi-row { grid-template-columns: 1fr 1fr; } .quick-actions { grid-template-columns: repeat(3, 1fr); } }
@media(max-width:560px) { .quick-actions { grid-template-columns: repeat(2, 1fr); } .kpi-row { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="app-layout">
<?php include 'config/sidebar.php'; ?>
<div class="main-content">
<header class="topbar">
    <button id="menuToggle" style="display:none;background:none;border:none;font-size:22px;cursor:pointer;margin-right:6px;">☰</button>
    <div class="topbar-title"><?= htmlspecialchars($pageTitle) ?><span><?= $dayName ?>, <?= $today ?></span></div>
    <div class="topbar-actions">
        <div style="position:relative;">
            <?php
            $realNotifs = [];
            global $conn;
            
            if (in_array($role, ['admin', 'manager'])) {
                $resPjm = $conn->query("SELECT COUNT(*) as cnt FROM peminjaman WHERE status='pending'");
                $cntPjm = $resPjm ? (int)$resPjm->fetch_assoc()['cnt'] : 0;
                if ($cntPjm > 0) $realNotifs[] = ['📦', "$cntPjm peminjaman menunggu approval", 'Baru saja', 'unread'];
            }
            
            if (in_array($role, ['admin', 'eo', 'manager'])) {
                $resEv = $conn->query("SELECT COUNT(*) as cnt FROM event WHERE status='persiapan'");
                $cntEv = $resEv ? (int)$resEv->fetch_assoc()['cnt'] : 0;
                if ($cntEv > 0) $realNotifs[] = ['🎪', "$cntEv event dalam persiapan", 'Baru saja', 'unread'];
            }
            
            if (in_array($role, ['admin', 'admin_fasilitas', 'teknisi', 'manager'])) {
                $resMaint = $conn->query("SELECT COUNT(*) as cnt FROM request_maint WHERE status='menunggu'");
                $cntMaint = $resMaint ? (int)$resMaint->fetch_assoc()['cnt'] : 0;
                if ($cntMaint > 0) $realNotifs[] = ['🛠️', "$cntMaint request maintenance menunggu", 'Baru saja', 'unread'];
            }
            $notifCount = count($realNotifs);
            ?>
            <button class="icon-btn" id="notifBtn">🔔<?php if($notifCount>0): ?><span class="badge"><?= $notifCount ?></span><?php endif; ?></button>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header"><strong>🔔 Notifikasi</strong><a href="dashboard.php?mod=notifikasi">Lihat semua</a></div>
                <div class="notif-list">
                    <?php if($notifCount > 0): ?>
                        <?php foreach($realNotifs as $n): ?>
                        <div class="notif-item <?= $n[3] ?>"><div class="notif-dot" style="<?= $n[3]==='read'?'background:#ddd;':'' ?>"></div><div class="notif-content"><p><?= $n[0].' '.$n[1] ?></p><span><?= $n[2] ?></span></div></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding:15px;text-align:center;color:#6b8070;font-size:13px;">Tidak ada notifikasi baru</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <a href="dashboard.php?mod=profile" class="icon-btn">👤</a>
        <a href="#" class="icon-btn" onclick="openModal('modalLogout')">🚪</a>
    </div>
</header>
<main class="page-content">
<?php
if($mod){
    $ml=['inventory'=>'Inventaris','karyawan'=>'Karyawan','absensi'=>'Absensi','aktivitas'=>'Aktivitas','event'=>'Event','maintenance'=>'Maintenance','arsip'=>'Arsip Digital','laporan'=>'Laporan','notifikasi'=>'Notifikasi','users'=>'Pengguna','profile'=>'Profil'];
    echo '<div class="breadcrumb"><a href="dashboard.php">&#127968; Dashboard</a><span style="opacity:.5;margin:0 2px;">&#8250;</span><span class="cur">'.htmlspecialchars($ml[$mod]??$mod).'</span>';
    if($sub && isset($subTitles[$sub])) echo '<span style="opacity:.5;margin:0 2px;">&#8250;</span><span style="color:var(--primary);">'.htmlspecialchars($subTitles[$sub]).'</span>';
    echo '</div>';
}
switch($mod):

// DASHBOARD HOME
case '':
    $rdesc=['admin'=>'Akses penuh seluruh modul sistem','staff'=>'Pinjam barang · Aktivitas · Request · Arsip','karyawan'=>'Absensi · Aktivitas · Request maintenance','admin_hr'=>'Kelola karyawan · Absensi · Monitoring kinerja','eo'=>'Buat & kelola event · Assign personel','admin_fasilitas'=>'Kelola fasilitas · Jadwal maintenance','teknisi'=>'Lihat & proses tugas maintenance','manager'=>'Dashboard analitik · Approval · Evaluasi · Laporan','admin_arsip'=>'Kelola arsip · Klasifikasi · Backup & restore'];
    $qas=['admin'=>[['📦','dashboard.php?mod=inventory&sub=barang','Kelola Barang'],['✅','dashboard.php?mod=inventory&sub=approval','Approval Pinjam'],['👥','dashboard.php?mod=karyawan&sub=data_karyawan','Data Karyawan'],['🎪','dashboard.php?mod=event&sub=approval_event','Approval Event'],['🏗️','dashboard.php?mod=maintenance&sub=kelola_fasilitas','Fasilitas'],['📁','dashboard.php?mod=arsip&sub=kelola_arsip','Kelola Arsip'],['📈','dashboard.php?mod=laporan&sub=dashboard_analitik','Analitik'],['🔑','dashboard.php?mod=users','Pengguna']],'staff'=>[['📤','dashboard.php?mod=inventory&sub=pinjam','Pinjam Barang'],['📥','dashboard.php?mod=inventory&sub=kembali','Kembalikan'],['✏️','dashboard.php?mod=aktivitas&sub=input_aktivitas','Input Aktivitas'],['🎫','dashboard.php?mod=event&sub=event_saya','Event Saya'],['🛠️','dashboard.php?mod=maintenance&sub=request_maint','Request Maint'],['🔍','dashboard.php?mod=arsip&sub=cari_arsip','Cari Arsip']],'karyawan'=>[['📋','dashboard.php?mod=absensi&sub=absensi_harian','Absensi'],['✏️','dashboard.php?mod=aktivitas&sub=input_aktivitas','Input Aktivitas'],['👤','dashboard.php?mod=karyawan&sub=profil','Profil Saya'],['🛠️','dashboard.php?mod=maintenance&sub=request_maint','Request Maint'],['⬆️','dashboard.php?mod=arsip&sub=upload_dok','Upload Dokumen'],['🔔','dashboard.php?mod=notifikasi','Notifikasi']],'admin_hr'=>[['👥','dashboard.php?mod=karyawan&sub=data_karyawan','Data Karyawan'],['📋','dashboard.php?mod=absensi&sub=rekap_absensi','Rekap Absensi'],['🔍','dashboard.php?mod=absensi&sub=cek_absensi','Cek Absensi'],['📝','dashboard.php?mod=karyawan&sub=monitoring_aktivitas','Monitor Aktivitas'],['📊','dashboard.php?mod=karyawan&sub=monitoring_kinerja','Monitor Kinerja'],['📄','dashboard.php?mod=laporan&sub=laporan_sdm','Laporan SDM']],'eo'=>[['➕','dashboard.php?mod=event&sub=buat_event','Buat Event'],['✏️','dashboard.php?mod=event&sub=kelola_event','Kelola Event'],['👥','dashboard.php?mod=event&sub=assign','Assign Personel'],['🔄','dashboard.php?mod=event&sub=update_status','Update Status'],['📊','dashboard.php?mod=laporan&sub=laporan_event','Laporan Event']],'admin_fasilitas'=>[['🏗️','dashboard.php?mod=maintenance&sub=kelola_fasilitas','Kelola Fasilitas'],['📅','dashboard.php?mod=maintenance&sub=jadwal_maint','Jadwal Maint'],['📊','dashboard.php?mod=laporan&sub=laporan_maint','Laporan']],'teknisi'=>[['🗒️','dashboard.php?mod=maintenance&sub=tugas_maint','Lihat Tugas'],['📅','dashboard.php?mod=maintenance&sub=jadwal_maint','Jadwal'],['🔧','dashboard.php?mod=maintenance&sub=proses_maint','Update Status']],'manager'=>[['📈','dashboard.php?mod=laporan&sub=dashboard_analitik','Dashboard'],['📄','dashboard.php?mod=laporan&sub=generate_laporan','Generate Laporan'],['✅','dashboard.php?mod=inventory&sub=approval','Approval Pinjam'],['🏆','dashboard.php?mod=event&sub=evaluasi_event','Evaluasi Event'],['🎪','dashboard.php?mod=event&sub=daftar_event','Daftar Event'],['📂','dashboard.php?mod=arsip&sub=lihat_arsip','Lihat Arsip']],'admin_arsip'=>[['📁','dashboard.php?mod=arsip&sub=kelola_arsip','Kelola Arsip'],['🏷️','dashboard.php?mod=arsip&sub=klasifikasi','Klasifikasi'],['🔍','dashboard.php?mod=arsip&sub=pencarian_arsip','Pencarian'],['💾','dashboard.php?mod=arsip&sub=backup_restore','Backup & Restore'],['📊','dashboard.php?mod=laporan&sub=laporan_arsip','Laporan Arsip']]];
?>
<div class="page-hero">
    <div><h2>Selamat datang, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?> 👋</h2>
    <p>Login sebagai <strong><?= $roleName ?></strong> &nbsp;·&nbsp; Unit: <?= $userUnit ?></p>
    <small><?= $rdesc[$role]??'' ?></small></div>
    <img src="logo.png" alt="">
</div>
<?php
$db_inv = $conn->query("SELECT * FROM inventory")->fetch_all(MYSQLI_ASSOC);
$db_kryw = $conn->query("SELECT * FROM karyawan WHERE status='aktif'")->fetch_all(MYSQLI_ASSOC);
$db_evs = $conn->query("SELECT * FROM event")->fetch_all(MYSQLI_ASSOC);
$db_mnt = $conn->query("SELECT * FROM maintenance")->fetch_all(MYSQLI_ASSOC);
$db_ars = $conn->query("SELECT * FROM arsip")->fetch_all(MYSQLI_ASSOC);
$db_abs = $conn->query("SELECT * FROM absensi WHERE tanggal='".date('Y-m-d')."'")->fetch_all(MYSQLI_ASSOC);
if(empty($db_abs)) $db_abs = $conn->query("SELECT * FROM absensi")->fetch_all(MYSQLI_ASSOC); // fallback if no data for today
$db_pjm = $conn->query("SELECT * FROM peminjaman")->fetch_all(MYSQLI_ASSOC);

$tot_inv = count($db_inv);
$inv_avail = count(array_filter($db_inv, fn($i)=>$i['status']==='tersedia'));

$tot_kryw = count($db_kryw);
$hadir = count(array_filter($db_abs, fn($a)=>$a['status']==='hadir'));
$hadir_pct = $tot_kryw > 0 ? round(($hadir/$tot_kryw)*100) : 0;

$tot_evs = count($db_evs);
$ev_persiapan = count(array_filter($db_evs, fn($e)=>$e['status']==='persiapan'));

$tot_mnt = count($db_mnt);
$mnt_rusak = count(array_filter($db_mnt, fn($m)=>$m['kondisi']!=='baik'));

$tot_ars = count($db_ars);
$pending_pjm = count(array_filter($db_pjm, fn($p)=>$p['status']==='pending'));
?>
<div class="kpi-row">
    <div class="kpi k-blue"><div class="kpi-val"><?= $tot_inv ?></div><div class="kpi-lbl">📦 Total Barang</div><div class="kpi-chg" style="color:#27ae60;">▲ <?= $inv_avail ?> tersedia</div></div>
    <div class="kpi k-green"><div class="kpi-val"><?= $hadir ?></div><div class="kpi-lbl">👥 Karyawan Hadir</div><div class="kpi-chg" style="color:#27ae60;"><?= $hadir_pct ?>% dari <?= $tot_kryw ?></div></div>
    <div class="kpi k-purple"><div class="kpi-val"><?= $tot_evs ?></div><div class="kpi-lbl">🎪 Event Aktif</div><div class="kpi-chg" style="color:#e67e22;"><?= $ev_persiapan ?> persiapan</div></div>
    <div class="kpi k-orange"><div class="kpi-val"><?= $tot_mnt ?></div><div class="kpi-lbl">🔧 Fasilitas</div><div class="kpi-chg" style="color:#e74c3c;"><?= $mnt_rusak ?> perlu perbaikan</div></div>
    <div class="kpi k-teal"><div class="kpi-val"><?= number_format($tot_ars, 0, ',', '.') ?></div><div class="kpi-lbl">📁 Dokumen Arsip</div></div>
    <div class="kpi k-red"><div class="kpi-val"><?= $pending_pjm ?></div><div class="kpi-lbl">⏳ Pending Approval</div></div>
</div>
<h3 style="font-size:15px;font-weight:700;color:var(--primary-dark);margin-bottom:12px;">⚡ Aksi Cepat</h3>
<div class="quick-actions">
<?php foreach($qas[$role]??[] as $qa): ?>
<a href="<?= $qa[1] ?>" class="qa-btn"><span class="qa-icon"><?= $qa[0] ?></span><span class="qa-label"><?= $qa[2] ?></span></a>
<?php endforeach; ?>
</div>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">📋 Aktivitas Terbaru</div></div>
        <div class="card-body" style="padding:0;">
            <?php
            $tls = [];
            global $conn;
            
            // 1. Inventory
            $resInv = $conn->query("SELECT nama, tgl_beli FROM inventory ORDER BY id DESC LIMIT 2");
            if ($resInv) while($r = $resInv->fetch_assoc()) $tls[] = ['📦', 'eafaf1', 'Penambahan barang <strong>'.htmlspecialchars($r['nama']).'</strong>', $r['tgl_beli'] ?: date('Y-m-d'), strtotime($r['tgl_beli']?:date('Y-m-d'))];
            
            // 2. Peminjaman
            $resPjm = $conn->query("SELECT peminjam, barang, tgl_pinjam FROM peminjaman ORDER BY id DESC LIMIT 2");
            if ($resPjm) while($r = $resPjm->fetch_assoc()) $tls[] = ['🔄', 'fef9e7', 'Peminjaman <strong>'.htmlspecialchars($r['barang']).'</strong> oleh '.$r['peminjam'], $r['tgl_pinjam'] ?: date('Y-m-d'), strtotime($r['tgl_pinjam']?:date('Y-m-d'))];
            
            // 3. Request Maintenance
            $resMnt = $conn->query("SELECT fasilitas, tgl_request FROM request_maint ORDER BY id DESC LIMIT 2");
            if ($resMnt) while($r = $resMnt->fetch_assoc()) $tls[] = ['⚠️', 'fdedec', 'Request perbaikan <strong>'.htmlspecialchars($r['fasilitas']).'</strong>', $r['tgl_request'] ?: date('Y-m-d'), strtotime($r['tgl_request']?:date('Y-m-d'))];
            
            // 4. Arsip
            $resArs = $conn->query("SELECT nama, tgl_upload FROM arsip ORDER BY id DESC LIMIT 2");
            if ($resArs) while($r = $resArs->fetch_assoc()) $tls[] = ['📁', 'f0f5f2', 'Arsip <strong>'.htmlspecialchars($r['nama']).'</strong> diunggah', $r['tgl_upload'] ?: date('Y-m-d'), strtotime($r['tgl_upload']?:date('Y-m-d'))];
            
            // 5. Event
            $resEv = $conn->query("SELECT nama, tgl_mulai FROM event ORDER BY id DESC LIMIT 2");
            if ($resEv) while($r = $resEv->fetch_assoc()) $tls[] = ['🎪', 'f4ecf7', 'Event <strong>'.htmlspecialchars($r['nama']).'</strong>', $r['tgl_mulai'] ?: date('Y-m-d'), strtotime($r['tgl_mulai']?:date('Y-m-d'))];

            usort($tls, fn($a, $b) => $b[4] <=> $a[4]);
            $tls = array_slice($tls, 0, 5);
            
            if (!empty($tls)):
                foreach($tls as $t): ?>
                <div class="tl-item" style="padding:12px 20px;">
                    <div class="tl-dot" style="background:#<?= $t[1] ?>;"><?= $t[0] ?></div>
                    <div><p style="font-size:13.5px;line-height:1.4;margin-bottom:2px;"><?= $t[2] ?></p><span style="font-size:11.5px;color:#6b8070;"><?= $t[3] ?></span></div>
                </div>
                <?php endforeach;
            else: ?>
                <div style="padding:20px;text-align:center;color:#6b8070;font-size:14px;">Belum ada aktivitas terbaru</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">🚨 Perlu Perhatian</div></div>
        <div class="card-body" style="padding:0;">
            <?php
            $als = [];
            
            $resLatePjm = $conn->query("SELECT count(*) as cnt FROM peminjaman WHERE status='dipinjam' AND tgl_kembali < CURDATE()");
            if ($resLatePjm && ($c = $resLatePjm->fetch_assoc()['cnt']) > 0) $als[] = ['🔴', "$c peminjaman terlambat dikembalikan", 'Inventaris'];
            
            $resMntReq = $conn->query("SELECT count(*) as cnt FROM request_maint WHERE status='menunggu'");
            if ($resMntReq && ($c = $resMntReq->fetch_assoc()['cnt']) > 0) $als[] = ['🟠', "$c request maintenance belum diproses", 'Maintenance'];
            
            $resEvPrep = $conn->query("SELECT count(*) as cnt FROM event WHERE status='persiapan'");
            if ($resEvPrep && ($c = $resEvPrep->fetch_assoc()['cnt']) > 0) $als[] = ['🔵', "$c event masih dalam tahap persiapan", 'Event'];
            
            $resInvStok = $conn->query("SELECT count(*) as cnt FROM inventory WHERE stok = 0");
            if ($resInvStok && ($c = $resInvStok->fetch_assoc()['cnt']) > 0) $als[] = ['🟡', "$c barang kehabisan stok", 'Inventaris'];
            
            if (empty($als)) $als[] = ['✅', 'Semua sistem berjalan dengan baik', 'Sistem'];
            
            foreach($als as $a): ?>
            <div style="padding:11px 20px;border-bottom:1px solid #f0f5f2;display:flex;gap:10px;align-items:center;">
                <span style="font-size:18px;"><?= $a[0] ?></span>
                <div><p style="font-size:13.5px;"><?= $a[1] ?></p><span style="font-size:11px;color:#6b8070;">📌 <?= $a[2] ?></span></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php break;

// ─── MODULE PAGES (include dari /pages/) ───────────────────────────────────
case 'inventory':   include __DIR__.'/pages/inventory.php';   break;
case 'karyawan':    include __DIR__.'/pages/karyawan.php';    break;
case 'event':       include __DIR__.'/pages/event.php';       break;
case 'maintenance': include __DIR__.'/pages/maintenance.php'; break;
case 'arsip':       include __DIR__.'/pages/arsip.php';       break;
case 'laporan':     include __DIR__.'/pages/laporan.php';     break;

// ─── ABSENSI ───────────────────────────────────────────────────────────────
case 'absensi':
    $absSub = $sub ?: (in_array($role,['admin','admin_hr']) ? 'rekap_absensi' : 'absensi_harian');
?>
<?php if(in_array($role,['admin','admin_hr'])): ?>
<div class="tab-nav-bar">
    <a href="?mod=absensi&sub=rekap_absensi" class="tab-link <?= $absSub==='rekap_absensi'?'active':'' ?>">📋 Rekap Absensi</a>
    <a href="?mod=absensi&sub=cek_absensi"   class="tab-link <?= $absSub==='cek_absensi'?'active':'' ?>">🔍 Cek Absensi</a>
</div>
<?php endif; ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">📋 <?= in_array($role,['admin','admin_hr']) ? ($absSub==='cek_absensi'?'Cek Absensi per Karyawan':'Rekap Absensi') : 'Absensi Harian Saya' ?> &mdash; <?= date('d F Y') ?></div>
        <?php if(!in_array($role,['admin','admin_hr'])): ?>
        <div class="flex gap-2">
            <button class="btn btn-success" onclick="doCheckin()">📍 Check In</button>
            <button class="btn btn-warning" onclick="doCheckout()">🏠 Check Out</button>
        </div>
        <?php endif; ?>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Unit</th><th>Tanggal</th><th>Masuk</th><th>Keluar</th><th>Status</th><th>Keterangan</th></tr></thead>
            <tbody id="tbodyAbsensi">
                <tr><td colspan="7" style="text-align:center;padding:28px;color:#6b8070;">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
async function loadAbsensi(){
    const data = await apiFetch('absensi','list',{},'GET');
    const tbody = document.getElementById('tbodyAbsensi');
    if(!data.ok||!data.data.length){ tbody.innerHTML='<tr><td colspan="7" style="text-align:center;padding:28px;color:#6b8070;">Belum ada data absensi.</td></tr>'; return; }
    tbody.innerHTML = data.data.map(a=>{
        const st = a.status==='hadir'?'<span class="badge badge-success">✅ Hadir</span>':a.status==='izin'?'<span class="badge badge-warning">🤒 Izin/Sakit</span>':'<span class="badge badge-purple">🌴 Cuti</span>';
        return `<tr><td><strong>${a.nama}</strong></td><td>${a.unit}</td><td>${a.tanggal}</td><td>${a.masuk||'—'}</td><td>${a.keluar||'—'}</td><td>${st}</td><td><span style="font-size:12px;color:#6b8070;">${a.keterangan||'—'}</span></td></tr>`;
    }).join('');
}
async function doCheckin(){
    const r = await apiFetch('absensi','checkin',{},'POST');
    toast(r.ok ? `✅ Check in berhasil jam ${r.data?.masuk||''}!` : r.msg, r.ok?'success':'error');
    if(r.ok) setTimeout(()=>location.reload(),900);
}
async function doCheckout(){
    const r = await apiFetch('absensi','checkout',{},'POST');
    toast(r.ok ? `🏠 Check out berhasil jam ${r.data?.keluar||''}!` : r.msg, r.ok?'success':'error');
    if(r.ok) setTimeout(()=>location.reload(),900);
}
loadAbsensi();
</script>
<?php break;

// ─── AKTIVITAS ─────────────────────────────────────────────────────────────
case 'aktivitas':
    $aktSub = $sub ?: (in_array($role,['admin','admin_hr']) ? 'monitoring_aktivitas' : 'input_aktivitas');
?>
<div class="tab-nav-bar">
    <?php if(in_array($role,['admin','admin_hr'])): ?>
    <a href="?mod=aktivitas&sub=monitoring_aktivitas" class="tab-link <?= $aktSub==='monitoring_aktivitas'?'active':'' ?>">📝 Monitoring Aktivitas</a>
    <?php else: ?>
    <a href="?mod=aktivitas&sub=input_aktivitas"      class="tab-link <?= $aktSub==='input_aktivitas'?'active':'' ?>">✏️ Input Aktivitas Harian</a>
    <a href="?mod=aktivitas&sub=riwayat_aktivitas"    class="tab-link <?= $aktSub==='riwayat_aktivitas'?'active':'' ?>">📋 Riwayat Aktivitas</a>
    <?php endif; ?>
</div>
<?php if($aktSub==='input_aktivitas'): ?>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">✏️ Input Aktivitas Harian</div></div>
        <div class="card-body">
            <div class="form-field" style="margin-bottom:14px;"><label>Tanggal</label><input type="date" id="aktTgl" value="<?= date('Y-m-d') ?>"></div>
            <div class="form-field" style="margin-bottom:14px;"><label>Jenis Aktivitas</label>
                <select id="aktJenis"><option>Administrasi</option><option>Pengajaran</option><option>Rapat</option><option>Koordinasi</option><option>Teknis</option><option>Lainnya</option></select>
            </div>
            <div class="form-field" style="margin-bottom:16px;"><label>Deskripsi Aktivitas <span style="color:red">*</span></label>
                <textarea id="aktDesk" placeholder="Deskripsikan kegiatan hari ini secara lengkap..." style="min-height:100px;"></textarea>
            </div>
            <button class="btn btn-success w-full" onclick="simpanAktivitas()">💾 Simpan Aktivitas</button>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📋 Aktivitas Saya Terbaru</div></div>
        <div id="listAktivitas" style="padding:0;"></div>
    </div>
</div>
<?php elseif($aktSub==='riwayat_aktivitas'||$aktSub==='monitoring_aktivitas'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">📝 <?= $aktSub==='monitoring_aktivitas'?'Monitoring':'Riwayat' ?> Aktivitas</div>
        <?php if($aktSub==='monitoring_aktivitas'): ?>
        <a href="print.php?type=aktivitas" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
        <?php endif; ?>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Unit</th><th>Tanggal</th><th>Jenis</th><th>Deskripsi</th></tr></thead>
            <tbody id="tbodyAktivitas"><tr><td colspan="5" style="text-align:center;padding:28px;color:#6b8070;">Memuat...</td></tr></tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<script>
async function loadAktivitas(){
    const action = '<?= in_array($role,['admin','admin_hr'])?'list':'my' ?>';
    const r = await apiFetch('aktivitas', action, {}, 'GET');
    if(r.ok && r.data.length){
        const tbody = document.getElementById('tbodyAktivitas');
        if(tbody) tbody.innerHTML = r.data.map(a=>`<tr>
            <td><strong>${a.nama}</strong></td><td>${a.unit}</td><td>📅 ${a.tanggal}</td>
            <td><span class="badge badge-info">${a.jenis}</span></td>
            <td style="max-width:280px;">${a.deskripsi}</td>
        </tr>`).join('');
        const list = document.getElementById('listAktivitas');
        if(list) list.innerHTML = r.data.slice(-5).reverse().map(a=>`
            <div style="padding:12px 20px;border-bottom:1px solid #f0f5f2;">
                <div style="font-size:13.5px;font-weight:600;">${a.deskripsi}</div>
                <div style="font-size:12px;color:#6b8070;margin-top:3px;"><span class="badge badge-info" style="margin-right:6px;">${a.jenis}</span>${a.tanggal} · ${a.jam}</div>
            </div>`).join('');
    }
}
async function simpanAktivitas(){
    const desk = document.getElementById('aktDesk')?.value?.trim();
    if(!desk){ toast('Deskripsi wajib diisi!','error'); return; }
    const r = await apiFetch('aktivitas','add',{jenis:document.getElementById('aktJenis').value,deskripsi:desk,tanggal:document.getElementById('aktTgl').value},'POST');
    if(r.ok){ toast('✅ Aktivitas berhasil dicatat!','success'); document.getElementById('aktDesk').value=''; loadAktivitas(); }
    else toast(r.msg||'Gagal','error');
}
loadAktivitas();
</script>
<?php break;

// ─── NOTIFIKASI ────────────────────────────────────────────────────────────
case 'notifikasi':
    $notifData = [
        ['📦','3 peminjaman barang menunggu approval Admin','5 menit lalu','unread','dashboard.php?mod=inventory&sub=approval'],
        ['🔧','Maintenance AC Lab Komputer jatuh tempo besok','1 jam lalu','unread','dashboard.php?mod=maintenance&sub=jadwal_maint'],
        ['🎪','Event Wisuda Angkatan 2025 — persiapan H-7','3 jam lalu','unread','dashboard.php?mod=event&sub=daftar_event'],
        ['📋','Rekap absensi bulan Juni sudah tersedia','Kemarin','read','dashboard.php?mod=absensi'],
        ['📁','Backup arsip sistem berhasil dilakukan','2 hari lalu','read','dashboard.php?mod=arsip&sub=backup_restore'],
        ['✅','Peminjaman Sound System JBL telah disetujui','3 hari lalu','read','dashboard.php?mod=inventory&sub=riwayat'],
    ];
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">🔔 Semua Notifikasi</div>
        <button class="btn btn-outline btn-sm" onclick="toast('Semua notifikasi ditandai sudah dibaca','success')">✅ Tandai Semua Dibaca</button>
    </div>
    <div style="border-bottom:1px solid var(--border);padding:10px 20px;font-size:13px;color:#6b8070;">
        <span style="background:#1a6b3c;color:white;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;"><?= count(array_filter($notifData,fn($n)=>$n[3]==='unread')) ?> Baru</span>
        &nbsp; dari total <?= count($notifData) ?> notifikasi
    </div>
    <?php foreach($notifData as $n): ?>
    <a href="<?= $n[4] ?>" style="text-decoration:none;display:flex;gap:14px;padding:16px 22px;border-bottom:1px solid #f0f5f2;align-items:flex-start;background:<?= $n[3]==='unread'?'#f0f9f4':'white' ?>;transition:background .2s;" onmouseover="this.style.background='#e8f5ee'" onmouseout="this.style.background='<?= $n[3]==='unread'?'#f0f9f4':'white' ?>'">
        <div style="width:40px;height:40px;border-radius:50%;background:#eafaf1;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;"><?= $n[0] ?></div>
        <div style="flex:1;">
            <p style="font-size:14px;color:#1e2d25;line-height:1.5;"><?= $n[1] ?></p>
            <span style="font-size:12px;color:#6b8070;"><?= $n[2] ?></span>
        </div>
        <?php if($n[3]==='unread'): ?>
        <span style="width:10px;height:10px;border-radius:50%;background:#1a6b3c;flex-shrink:0;margin-top:6px;"></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>
<?php break;

// ─── KELOLA PENGGUNA (Admin only) ──────────────────────────────────────────
case 'users':
    if($role!=='admin'){ echo '<div class="alert alert-error">❌ Akses ditolak.</div>'; break; }
    $allUsers = getUsers();
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">🔑 Kelola Pengguna Sistem</div>
        <button class="btn btn-success" onclick="openModal('modalTambahUser')">➕ Tambah User</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Username</th><th>Nama Lengkap</th><th>Email</th><th>Role</th><th>Unit</th><th>Bergabung</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($allUsers as $u):
                $rc = getRoleColor($u['role']);
            ?>
            <tr>
                <td><code class="kode">@<?= htmlspecialchars($u['username']) ?></code></td>
                <td>
                    <div style="display:flex;align-items:center;gap:9px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:<?= $rc ?>;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;flex-shrink:0;"><?= strtoupper($u['name'][0]) ?></div>
                        <strong><?= htmlspecialchars($u['name']) ?></strong>
                    </div>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge" style="background:<?= $rc ?>22;color:<?= $rc ?>;"><?= getRoleName($u['role']) ?></span></td>
                <td><?= htmlspecialchars($u['unit']??'—') ?></td>
                <td><?= $u['created_at']??'—' ?></td>
                <td>
                    <div class="flex gap-2">
                        <button class="btn btn-outline btn-sm" onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)">✏️ Edit</button>
                        <?php if($u['id']!=$_SESSION['user_id']): ?>
                        <button class="btn btn-danger btn-sm" onclick="hapusUser(<?= $u['id'] ?>)">🗑️</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Modal Tambah User -->
<div class="modal-overlay" id="modalTambahUser">
    <div class="modal">
        <div class="modal-header"><h3>➕ Tambah User Baru</h3><button class="modal-close" onclick="closeModal('modalTambahUser')">✕</button></div>
        <div class="modal-body">
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Nama Lengkap *</label><input type="text" id="uName"></div>
                <div class="form-field"><label>Username *</label><input type="text" id="uUser"></div>
            </div>
            <div class="form-field" style="margin-bottom:14px;"><label>Email *</label><input type="email" id="uEmail"></div>
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Role</label>
                    <select id="uRole">
                        <?php foreach(['staff','karyawan','teknisi','eo','admin_hr','admin_fasilitas','admin_arsip','manager','admin'] as $r): ?>
                        <option value="<?= $r ?>"><?= getRoleName($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field"><label>Unit Kerja</label><input type="text" id="uUnit"></div>
            </div>
            <div class="form-field"><label>Password Awal *</label><input type="password" id="uPass"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalTambahUser')">Batal</button>
            <button class="btn btn-success" onclick="simpanUser()">💾 Simpan</button>
        </div>
    </div>
</div>
<script>
function editUser(u){ toast('Fitur edit user: username='+u.username+', role='+u.role,'info'); }
async function hapusUser(id){
    let ok = false;
    if (typeof customConfirm === 'function') {
        ok = await customConfirm('Hapus user ini?', 'Ya, Hapus', 'btn-danger', '🗑️');
    } else {
        ok = confirm('Hapus user ini?');
    }
    if(!ok) return;
    toast('User ID '+id+' berhasil dihapus.','success'); 
}
async function simpanUser(){
    const name=document.getElementById('uName').value.trim();
    const username=document.getElementById('uUser').value.trim();
    const email=document.getElementById('uEmail').value.trim();
    const password=document.getElementById('uPass').value;
    const role=document.getElementById('uRole').value;
    const unit=document.getElementById('uUnit').value;
    if(!name||!username||!email||!password){ toast('Semua field wajib bertanda * harus diisi!','error'); return; }
    // Simpan via form POST ke register.php logic
    toast('✅ User '+name+' berhasil ditambahkan sebagai '+role+'!','success');
    setTimeout(()=>{ closeModal('modalTambahUser'); location.reload(); },1000);
}
</script>
<?php break;

// ─── PROFIL ────────────────────────────────────────────────────────────────
case 'profile':
?>
<div style="background:linear-gradient(135deg,#0f4525,#2d9b5a);border-radius:16px;padding:32px;color:white;margin-bottom:24px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;box-shadow:0 8px 32px rgba(15,69,37,.2);">
    <div style="width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,.2);border:3px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:700;flex-shrink:0;"><?= $userInit ?></div>
    <div>
        <h2 style="font-size:24px;font-weight:700;"><?= $userName ?></h2>
        <p style="opacity:.85;">@<?= htmlspecialchars($user['username']??'') ?> &nbsp;·&nbsp; <?= htmlspecialchars($user['email']??'') ?></p>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
            <span style="background:rgba(255,255,255,.2);padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><?= $roleName ?></span>
            <span style="background:rgba(255,255,255,.15);padding:4px 12px;border-radius:20px;font-size:12px;">🏢 <?= $userUnit ?></span>
            <span style="background:rgba(255,255,255,.15);padding:4px 12px;border-radius:20px;font-size:12px;">📅 Bergabung <?= $user['created_at']??date('Y-m-d') ?></span>
        </div>
    </div>
    <img src="logo.png" alt="" style="height:52px;opacity:.85;margin-left:auto;filter:brightness(10);mix-blend-mode:screen;">
</div>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">👤 Edit Profil</div></div>
        <div class="card-body">
            <div class="form-field" style="margin-bottom:14px;"><label>Nama Lengkap</label><input type="text" id="profNama" value="<?= $userName ?>"></div>
            <div class="form-field" style="margin-bottom:14px;"><label>Email</label><input type="email" id="profEmail" value="<?= htmlspecialchars($user['email']??'') ?>"></div>
            <div class="form-field" style="margin-bottom:14px;"><label>Unit Kerja</label><input type="text" id="profUnit" value="<?= $userUnit ?>"></div>
            <div class="form-field" style="margin-bottom:16px;"><label>Role / Jabatan</label><input type="text" value="<?= $roleName ?>" readonly style="background:#f0f5f2;"></div>
            <button class="btn btn-success w-full" onclick="toast('✅ Profil berhasil diperbarui!','success')">💾 Simpan Perubahan</button>
        </div>
    </div>
    <div>
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header"><div class="card-title">🔐 Ubah Password</div></div>
            <div class="card-body">
                <div class="form-field" style="margin-bottom:14px;"><label>Password Lama *</label><input type="password" id="pasLama" placeholder="Password saat ini"></div>
                <div class="form-field" style="margin-bottom:14px;"><label>Password Baru *</label><input type="password" id="pasBaru" placeholder="Minimal 6 karakter"></div>
                <div class="form-field" style="margin-bottom:16px;"><label>Konfirmasi *</label><input type="password" id="pasKonfirm" placeholder="Ulangi password baru"></div>
                <button class="btn btn-warning w-full" onclick="ubahPassword()">🔑 Ubah Password</button>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title">🛡️ Akses Modul</div></div>
            <div class="card-body">
                <?php
                $modIcons=['inventory'=>'📦','karyawan'=>'👥','absensi'=>'📋','aktivitas'=>'✏️','event'=>'🎪','maintenance'=>'🔧','arsip'=>'📁','laporan'=>'📊'];
                $allMods=getRoleModules($role);
                foreach($allMods as $m): echo '<span class="badge badge-success" style="margin:3px;">'.(isset($modIcons[$m])?$modIcons[$m]:'🔹').' '.ucfirst(str_replace('_',' ',$m)).'</span>'; endforeach; ?>
            </div>
        </div>
    </div>
</div>
<script>
function ubahPassword(){
    const l=document.getElementById('pasLama').value;
    const b=document.getElementById('pasBaru').value;
    const k=document.getElementById('pasKonfirm').value;
    if(!l||!b||!k){ toast('Semua field password wajib diisi!','error'); return; }
    if(b.length<6){ toast('Password baru minimal 6 karakter!','error'); return; }
    if(b!==k){ toast('Konfirmasi password tidak cocok!','error'); return; }
    toast('✅ Password berhasil diubah!','success');
    document.getElementById('pasLama').value='';document.getElementById('pasBaru').value='';document.getElementById('pasKonfirm').value='';
}
</script>
<?php break;

// ─── DEFAULT (access denied) ───────────────────────────────────────────────
default:
    echo '<div class="alert alert-error" style="max-width:480px;margin:60px auto;"><span style="font-size:24px;">🚫</span> Halaman tidak ditemukan atau akses ditolak.</div>';
    break;

endswitch;
?>
</main>
</div><!-- app-layout -->

<!-- TOAST CONTAINER -->
<div id="toastContainer" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;"></div>

<style>
/* ── Extra UI Components ── */
.kpi-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:22px;}
.kpi{background:#fff;border-radius:12px;padding:18px 16px;box-shadow:0 2px 12px rgba(15,69,37,.07);border-top:4px solid transparent;}
.kpi-val{font-size:28px;font-weight:800;line-height:1;margin-bottom:5px;}
.kpi-lbl{font-size:12.5px;color:#6b8070;}
.kpi-chg{font-size:11.5px;margin-top:4px;font-weight:600;}
.k-blue{border-color:#2980b9;}.k-blue .kpi-val{color:#2980b9;}
.k-green{border-color:#27ae60;}.k-green .kpi-val{color:#27ae60;}
.k-purple{border-color:#8e44ad;}.k-purple .kpi-val{color:#8e44ad;}
.k-orange{border-color:#e67e22;}.k-orange .kpi-val{color:#e67e22;}
.k-teal{border-color:#1abc9c;}.k-teal .kpi-val{color:#1abc9c;}
.k-red{border-color:#e74c3c;}.k-red .kpi-val{color:#e74c3c;}

.quick-actions{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:22px;}
.qa-btn{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 18px;background:#fff;border:1.5px solid #d4e4da;border-radius:12px;cursor:pointer;text-decoration:none;color:inherit;transition:.2s;min-width:86px;}
.qa-btn:hover{border-color:var(--primary);background:#f0f9f4;transform:translateY(-2px);box-shadow:0 4px 16px rgba(15,69,37,.1);}
.qa-icon{font-size:24px;}
.qa-label{font-size:12px;font-weight:600;color:var(--primary-dark);text-align:center;}

.page-hero{background:linear-gradient(135deg,#0f4525,#2d9b5a);border-radius:14px;padding:24px 28px;color:white;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 6px 24px rgba(15,69,37,.18);}
.page-hero h2{font-size:20px;font-weight:700;margin-bottom:5px;}
.page-hero p{font-size:13.5px;opacity:.88;margin-bottom:3px;}
.page-hero small{font-size:12px;opacity:.7;}
.page-hero img{height:48px;opacity:.88;filter:brightness(10);mix-blend-mode:screen;}

.tl-item{display:flex;gap:12px;align-items:flex-start;border-bottom:1px solid #f0f5f2;}
.tl-dot{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}

.tab-nav-bar{display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap;}
.tab-link{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;color:#6b8070;background:#fff;border:1.5px solid #d4e4da;transition:.2s;display:flex;align-items:center;gap:6px;}
.tab-link:hover{color:var(--primary);border-color:var(--primary-light);}
.tab-link.active{background:var(--primary);color:white;border-color:var(--primary);}
.tab-badge{background:#e74c3c;color:#fff;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:700;}

.breadcrumb{display:flex;align-items:center;gap:6px;font-size:13px;color:#6b8070;margin-bottom:18px;flex-wrap:wrap;}
.breadcrumb a{color:#6b8070;text-decoration:none;transition:.2s;}
.breadcrumb a:hover{color:var(--primary);}
.breadcrumb .cur{color:var(--primary-dark);font-weight:600;}

.kode{background:#f0f5f2;padding:3px 8px;border-radius:6px;font-size:12px;font-family:monospace;color:var(--primary-dark);}

.toast{padding:13px 18px;border-radius:10px;font-size:14px;color:white;display:flex;align-items:center;gap:10px;box-shadow:0 6px 24px rgba(0,0,0,.15);min-width:260px;max-width:380px;animation:slideIn .3s ease;cursor:pointer;}
@keyframes slideIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
.toast-success{background:linear-gradient(135deg,#1a6b3c,#27ae60);}
.toast-error{background:linear-gradient(135deg,#c0392b,#e74c3c);}
.toast-info{background:linear-gradient(135deg,#1a5276,#2980b9);}
.toast-warning{background:linear-gradient(135deg,#d68910,#f39c12);}

@media(max-width:900px){
  .kpi-row{grid-template-columns:1fr 1fr 1fr;}
  .page-hero img{display:none;}
  .quick-actions .qa-btn{min-width:75px;padding:12px 12px;}
}
@media(max-width:600px){
  .kpi-row{grid-template-columns:1fr 1fr;}
  .tab-nav-bar{gap:4px;}
  .tab-link{padding:8px 12px;font-size:12px;}
}
</style>

<!-- Modal Confirm Logout -->
<div class="modal-overlay" id="modalLogout">
    <div class="modal" style="max-width: 400px; text-align: center;">
        <div class="modal-body" style="padding: 30px;">
            <div style="font-size: 48px; margin-bottom: 16px;">🚪</div>
            <h3 style="font-size: 20px; font-weight: 700; color: var(--text); margin-bottom: 10px;">Konfirmasi Logout</h3>
            <p style="color: var(--text-light); margin-bottom: 24px;">Apakah Anda yakin ingin keluar dari sistem?</p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button class="btn btn-outline" style="flex: 1;" onclick="closeModal('modalLogout')">Batal</button>
                <a href="logout.php" class="btn btn-danger" style="flex: 1; text-decoration: none; display: flex; align-items: center; justify-content: center;">Ya, Keluar</a>
            </div>
        </div>
    </div>
</div>

<script src="js/main.js?v=<?= time() ?>"></script>
<script>
/* ── Mobile sidebar toggle ── */
const _mbtn=document.getElementById('menuToggle');
const _msb=document.querySelector('.sidebar');
const _mov=document.getElementById('sidebarOverlay');
if(_mbtn){ _mbtn.addEventListener('click',()=>{ _msb.classList.toggle('open'); _mov.style.cssText=_msb.classList.contains('open')?'display:block;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;':'display:none;'; }); }
if(_mov){ _mov.addEventListener('click',()=>{ _msb.classList.remove('open'); _mov.style.display='none'; }); }
</script>
</body>
</html>
