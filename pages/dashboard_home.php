<?php
// ===== DASHBOARD HOME =====
// Aggregated stats & quick actions per role
$inv   = $conn->query("SELECT * FROM inventory")->fetch_all(MYSQLI_ASSOC);
$pjm   = $conn->query("SELECT * FROM peminjaman ORDER BY CAST(SUBSTRING(kode, 5) AS UNSIGNED) ASC")->fetch_all(MYSQLI_ASSOC);
$kryw  = $conn->query("SELECT * FROM karyawan")->fetch_all(MYSQLI_ASSOC);
$evs   = $conn->query("SELECT * FROM event")->fetch_all(MYSQLI_ASSOC);
$maint = $conn->query("SELECT * FROM maintenance")->fetch_all(MYSQLI_ASSOC);
$arsip = $conn->query("SELECT * FROM arsip")->fetch_all(MYSQLI_ASSOC);
$abs   = $conn->query("SELECT * FROM absensi")->fetch_all(MYSQLI_ASSOC);

// Role modules config for quick action cards
$allModCards = [
    'inventory'   => ['📦','Inventaris & Peminjaman','Kelola aset dan proses peminjaman barang','#eaf4fb','#2980b9', count($inv).' barang', count(array_filter($pjm,fn($p)=>$p['status']==='pending')).' pending'],
    'karyawan'    => ['👥','Manajemen Karyawan','Data pegawai, absensi, dan monitoring kinerja','#eafaf1','#27ae60', count($kryw).' karyawan', count(array_filter($abs,fn($a)=>$a['status']==='hadir')).' hadir'],
    'event'       => ['🎪','Event Management','Rencanakan dan kelola event dari A sampai Z','#f4ecf7','#9b59b6', count($evs).' event', count(array_filter($evs,fn($e)=>$e['status']==='persiapan')).' persiapan'],
    'maintenance' => ['🔧','Maintenance Fasilitas','Pantau kondisi dan jadwal perawatan fasilitas','#fef9e7','#e67e22', count($maint).' fasilitas', count(array_filter($maint,fn($m)=>$m['kondisi']!=='baik')).' perlu perbaikan'],
    'arsip'       => ['📁','Arsip Digital','Simpan dan kelola dokumen operasional','#f0f5f2','#5a7a64', count($arsip).' dokumen', count(array_unique(array_column($arsip,'kategori'))).' kategori'],
    'laporan'     => ['📊','Laporan & Analitik','Dashboard analitik dan generate laporan','#eaf4fb','#2980b9', '6 modul', '87% efisiensi'],
];
?>

<!-- WELCOME BANNER -->
<div style="background:linear-gradient(135deg,#0f4525 0%,#1a6b3c 55%,#2d9b5a 100%);border-radius:14px;padding:26px 30px;color:white;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;position:relative;overflow:hidden;">
    <div style="position:absolute;right:-30px;top:-30px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
    <div style="position:absolute;right:60px;bottom:-40px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,0.07);"></div>
    <div style="position:relative;z-index:1;">
        <h2 style="font-size:22px;font-weight:700;margin-bottom:5px;">
            Selamat datang, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?> 👋
        </h2>
        <p style="opacity:.85;font-size:14px;">Login sebagai <strong><?= $roleName ?></strong> · <?= htmlspecialchars($user['unit']??'—') ?></p>
        <p style="opacity:.7;font-size:13px;margin-top:4px;" id="dashDate"></p>
        <?php
        $desc=['admin'=>'Akses penuh ke seluruh modul sistem','staff'=>'Pinjam barang · Input aktivitas · Ajukan request · Upload arsip','karyawan'=>'Absensi harian · Input aktivitas · Request maintenance','admin_hr'=>'Kelola karyawan · Rekap absensi · Monitoring aktivitas & kinerja','eo'=>'Buat event · Kelola event · Assign personel & fasilitas','admin_fasilitas'=>'Kelola fasilitas · Jadwal maintenance · Laporan maintenance','teknisi'=>'Lihat tugas · Jadwal maintenance · Proses & update status','manager'=>'Dashboard analitik · Approval peminjaman · Evaluasi event · Semua laporan','admin_arsip'=>'Kelola arsip · Klasifikasi · Pencarian arsip · Backup & restore'];
        if(isset($desc[$role])): ?>
        <p style="opacity:.6;font-size:12px;margin-top:2px;font-style:italic;"><?= $desc[$role] ?></p>
        <?php endif; ?>
    </div>
    <img src="logo.png" alt="Logo" style="height:54px;opacity:.9;filter:brightness(10) saturate(0);mix-blend-mode:screen;position:relative;z-index:1;">
</div>
<script>document.getElementById('dashDate').textContent = new Date().toLocaleDateString('id-ID',{weekday:'long',year:'numeric',month:'long',day:'numeric'});</script>

<!-- STATS OVERVIEW -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card c-blue">
        <div class="stat-icon c-blue">📦</div>
        <div class="stat-info"><h3><?= count($inv) ?></h3><p>Total Barang</p><div class="stat-change up">▲ <?= count(array_filter($inv,fn($i)=>$i['status']==='tersedia')) ?> tersedia</div></div>
    </div>
    <div class="stat-card c-green">
        <div class="stat-icon c-green">👥</div>
        <div class="stat-info"><h3><?= count($kryw) ?></h3><p>Total Karyawan</p><div class="stat-change up">▲ <?= count(array_filter($abs,fn($a)=>$a['status']==='hadir')) ?> hadir hari ini</div></div>
    </div>
    <div class="stat-card c-purple">
        <div class="stat-icon c-purple">🎪</div>
        <div class="stat-info"><h3><?= count($evs) ?></h3><p>Total Event</p><div class="stat-change"><?= count(array_filter($evs,fn($e)=>$e['status']==='persiapan')) ?> dalam persiapan</div></div>
    </div>
    <div class="stat-card c-orange">
        <div class="stat-icon c-orange">🔧</div>
        <div class="stat-info"><h3><?= count($maint) ?></h3><p>Total Fasilitas</p><div class="stat-change down"><?= count(array_filter($maint,fn($m)=>$m['kondisi']!=='baik')) ?> perlu perbaikan</div></div>
    </div>
</div>

<!-- MODUL CARDS (sesuai hak akses) -->
<h3 style="font-size:16px;font-weight:700;margin-bottom:16px;color:var(--primary-dark);">📌 Modul yang Dapat Diakses</h3>
<div class="module-grid" style="margin-bottom:28px;">
<?php
$roleModMap=['inventory','karyawan','event','maintenance','arsip','laporan'];
$displayMods = $role==='admin' ? $roleModMap : array_intersect($roleModMap, $modules);
foreach($displayMods as $modKey):
    if(!isset($allModCards[$modKey])) continue;
    $m=$allModCards[$modKey];
    // First link per role
    $links=['inventory'=>match($role){'admin','admin_fasilitas'=>'?mod=inventory&sub=barang','manager'=>'?mod=inventory&sub=approval','staff'=>'?mod=inventory&sub=pinjam',default=>'?mod=inventory&sub=riwayat'},'karyawan'=>match($role){'admin','admin_hr'=>'?mod=karyawan&sub=data_karyawan','karyawan'=>'?mod=karyawan&sub=profil',default=>'?mod=karyawan&sub=data_karyawan'},'event'=>match($role){'admin'=>'?mod=event&sub=approval_event','eo'=>'?mod=event&sub=buat_event','manager'=>'?mod=event&sub=daftar_event','staff'=>'?mod=event&sub=event_saya',default=>'?mod=event&sub=daftar_event'},'maintenance'=>match($role){'admin','admin_fasilitas'=>'?mod=maintenance&sub=kelola_fasilitas','teknisi'=>'?mod=maintenance&sub=tugas_maint','manager'=>'?mod=maintenance&sub=laporan_maint',default=>'?mod=maintenance&sub=request_maint'},'arsip'=>match($role){'admin','admin_arsip'=>'?mod=arsip&sub=kelola_arsip','manager'=>'?mod=arsip&sub=lihat_arsip',default=>'?mod=arsip&sub=cari_arsip'},'laporan'=>'?mod=laporan&sub=dashboard_analitik'];
    $href = $links[$modKey] ?? "?mod=$modKey";
?>
<a href="<?= $href ?>" class="module-card" style="border-top-color:<?= $m[4] ?>;">
    <span class="mod-icon"><?= $m[0] ?></span>
    <h3><?= $m[1] ?></h3>
    <p><?= $m[2] ?></p>
    <div class="mod-stats">
        <div class="mod-stat"><strong><?= $m[5] ?></strong></div>
        <div class="mod-stat" style="color:<?= $m[4] ?>;"><strong><?= $m[6] ?></strong></div>
    </div>
</a>
<?php endforeach; ?>
</div>

<!-- AKTIVITAS TERBARU + PERHATIAN -->
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">🔔 Aktivitas Terbaru</div></div>
        <div class="card-body" style="padding:0;">
            <div class="activity-list" style="padding:0 22px;">
                <?php
                $actItems=[['📦','#eaf4fb',count(array_filter($pjm,fn($p)=>$p['status']==='pending')).' permintaan peminjaman menunggu persetujuan','5 menit lalu'],['🔧','#fdedec',count(array_filter($maint,fn($m)=>$m['kondisi']==='perbaikan')).' fasilitas memerlukan perbaikan segera','1 jam lalu'],['🎪','#f4ecf7',count(array_filter($evs,fn($e)=>$e['status']==='persiapan')).' event dalam tahap persiapan','3 jam lalu'],['✅','#eafaf1',count(array_filter($abs,fn($a)=>$a['status']==='hadir')).' karyawan telah tercatat absensi harian','Pagi ini'],['📁','#f0f5f2',count($arsip).' dokumen tersimpan dalam arsip digital','Kemarin']];
                foreach($actItems as $act): ?>
                <div class="activity-item">
                    <div class="activity-icon" style="background:<?= $act[1] ?>;"><?= $act[0] ?></div>
                    <div class="activity-content">
                        <p><?= $act[2] ?></p>
                        <span><?= $act[3] ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">⚠️ Perlu Perhatian</div></div>
        <div class="card-body" style="padding:0;">
            <?php
            $alerts=[];
            $pCount=count(array_filter($pjm,fn($p)=>$p['status']==='pending'));
            if($pCount>0) $alerts[]=['🔴',"$pCount peminjaman barang belum mendapat persetujuan",'Inventaris','?mod=inventory&sub=approval'];
            $mCount=count(array_filter($maint,fn($m)=>$m['kondisi']!=='baik'));
            if($mCount>0) $alerts[]=['🟠',"$mCount fasilitas perlu perbaikan atau sedang maintenance",'Maintenance','?mod=maintenance&sub=kelola_fasilitas'];
            $eCount=count(array_filter($evs,fn($e)=>$e['status']==='persiapan'));
            if($eCount>0) $alerts[]=['🟡',"$eCount event dalam persiapan, pastikan personel & fasilitas siap",'Event','?mod=event&sub=daftar_event'];
            $aCount=count(array_filter($abs,fn($a)=>in_array($a['status'],['izin','cuti'])));
            if($aCount>0) $alerts[]=['🔵',"$aCount karyawan tidak hadir hari ini (izin/cuti)",'Absensi','?mod=karyawan&sub=rekap_absensi'];
            $alerts[]=['⚪','Backup arsip digital terakhir dilakukan 3 hari lalu','Arsip','?mod=arsip&sub=backup_restore'];
            foreach($alerts as $al): ?>
            <a href="<?= $al[3] ?>" style="display:flex;gap:12px;padding:13px 22px;border-bottom:1px solid #e8f0eb;text-decoration:none;color:inherit;transition:.15s;" onmouseover="this.style.background='#f8faf9'" onmouseout="this.style.background=''">
                <span style="font-size:20px;"><?= $al[0] ?></span>
                <div>
                    <p style="font-size:13.5px;line-height:1.4;"><?= $al[1] ?></p>
                    <span style="font-size:11px;color:#6b8070;">📌 <?= $al[2] ?></span>
                </div>
                <span style="margin-left:auto;color:#6b8070;font-size:18px;">›</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
