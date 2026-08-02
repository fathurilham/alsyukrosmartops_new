<?php
/**
 * SIDEBAR NAVIGASI – AL-SYUKROSMART OPS
 * Struktur navigasi disesuaikan PERSIS dengan:
 *   Hal.1  → Use Case Diagram (akses fungsi per actor)
 *   Hal.2  → Flowchart Arsip   (Admin Arsip | Manager | Staff)
 *   Hal.3  → Flowchart Maintenance (Admin Fasilitas | Teknisi | Staff)
 *   Hal.4  → Flowchart HR/Karyawan (Admin HR | Karyawan)
 *   Hal.7  → Flowchart Event   (EO | Admin | Staff)
 *   Hal.8  → Flowchart Inventory (Admin | Staff)
 */
$user        = getCurrentUser();
$role        = $user['role']      ?? 'staff';
$modules     = getRoleModules($role);
$roleName    = getRoleName($role);
$roleColor   = getRoleColor($role);
$userInitial = strtoupper(substr($user['name'] ?? 'U', 0, 1));
$mod         = $_GET['mod']  ?? '';
$sub         = $_GET['sub']  ?? '';

function sideNavItem($href, $icon, $label, $activeMod, $curMod, $curSub = '', $activeSub = '', $badge = 0, $badgeId = '') {
    $isActive = ($curMod === $activeMod && ($activeSub === '' || $curSub === $activeSub));
    $cls      = $isActive ? 'nav-item active' : 'nav-item';
    
    $style = ($badge > 0) ? '' : 'display:none;';
    $idAttr = $badgeId ? " id='$badgeId'" : '';
    // Selalu render span jika ada badgeId agar JS bisa menampilkannya nanti
    $bdg = ($badge > 0 || $badgeId) ? "<span class='nav-badge'$idAttr style='$style'>" . ($badge > 0 ? $badge : '') . "</span>" : '';
    
    return "<a href='$href' class='$cls'><span class='nav-icon'><i data-lucide='$icon' style='width:20px;height:20px;stroke-width:2.25px;'></i></span> $label $bdg</a>";
}

function sideSection($label) {
    return "<div class='nav-section-label'>$label</div>";
}
?>

<?php
global $conn;

$pendingPeminjamanCount = 0;
if (isset($conn)) {
    $resPjm = $conn->query("SELECT COUNT(*) as cnt FROM peminjaman WHERE status='pending'");
    if ($resPjm) $pendingPeminjamanCount = (int)$resPjm->fetch_assoc()['cnt'];
}

$pendingEventCount = 0;
if (isset($conn)) {
    $resEv = $conn->query("SELECT COUNT(*) as cnt FROM event WHERE status='persiapan'");
    if ($resEv) $pendingEventCount = (int)$resEv->fetch_assoc()['cnt'];
}

$pendingMaintCount = 0;
if (isset($conn)) {
    $resMaint = $conn->query("SELECT COUNT(*) as cnt FROM request_maint WHERE status='menunggu'");
    if ($resMaint) $pendingMaintCount = (int)$resMaint->fetch_assoc()['cnt'];
}
?>

<div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99;"></div>

<aside class="sidebar">
    <div class="sidebar-header" style="border-bottom: 1px solid #f0f0f0; padding: 20px 24px;">
        <div class="sidebar-brand" style="display:flex; align-items:center; gap:12px;">
            <div class="logo-icon" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); flex-shrink: 0;">
                <img src="logo.png" alt="Logo" style="height:20px; width:auto; display:block; object-fit:contain;">
            </div>
            <div class="logo-text">
                <strong style="display:block; font-size:15px; color:#111827; font-weight:800; line-height:1.2;">AL-SYUKROSMART</strong>
                <span style="display:block; font-size:12px; color:#6B7280; margin-top:2px;">OPS System</span>
            </div>
        </div>
    </div>

    <div class="sidebar-user" style="padding: 24px 24px; display: flex; align-items: center; gap: 14px; background: #f8fafc; border-bottom: 1px solid #f0f0f0; margin-bottom: 10px;">
        <div class="user-avatar" style="width: 48px; height: 48px; font-size: 20px; background: #EF4444; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); flex-shrink: 0;">
            <?= $userInitial ?>
        </div>
        <div class="user-info">
            <strong style="display: block; font-size: 15px; color: #111827; font-weight: 700; margin-bottom: 4px;"><?= htmlspecialchars($user['name'] ?? 'User') ?></strong>
            <span style="display: inline-block; font-size: 11.5px; color: #4B5563; background: #E5E7EB; padding: 2px 10px; border-radius: 12px; font-weight: 600;"><?= $roleName ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">

        <?= sideSection('Beranda') ?>
        <?= sideNavItem('dashboard.php', 'home', 'Dashboard', '', $mod) ?>

        <?php if ($role === 'admin'): ?>
        <?= sideSection('Inventaris') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=barang',     'package', 'Kelola Data Barang',       'inventory', $mod, $sub, 'barang') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=approval',   'check-circle', 'Persetujuan Peminjaman',   'inventory', $mod, $sub, 'approval', $pendingPeminjamanCount, 'badge-peminjaman') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=kembali',    'download-cloud', 'Kembalikan Barang',      'inventory', $mod, $sub, 'kembali') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=riwayat',    'clipboard-list', 'Data Pengembalian',        'inventory', $mod, $sub, 'riwayat') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=laporan_inv','file-text', 'Laporan Inventaris',       'inventory', $mod, $sub, 'laporan_inv') ?>

        <?= sideSection('SDM & Karyawan') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=data_karyawan',       'users','Kelola Data Karyawan',  'karyawan',$mod,$sub,'data_karyawan') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=rekap_absensi',       'clipboard-list','Rekap Absensi',          'karyawan',$mod,$sub,'rekap_absensi') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=monitoring_aktivitas','file-signature','Monitoring Aktivitas',   'karyawan',$mod,$sub,'monitoring_aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=monitoring_kinerja',  'bar-chart-2','Monitoring Kinerja',     'karyawan',$mod,$sub,'monitoring_kinerja') ?>

        <?= sideSection('Event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=approval_event',  'check-circle','Approval Event',    'event',$mod,$sub,'approval_event', $pendingEventCount, 'badge-event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=monitoring_event','radio','Monitoring Event',  'event',$mod,$sub,'monitoring_event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=daftar_event',    'tent','Daftar Event',      'event',$mod,$sub,'daftar_event') ?>

        <?= sideSection('Maintenance') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=kelola_fasilitas','hammer','Kelola Data Fasilitas',   'maintenance',$mod,$sub,'kelola_fasilitas') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=jadwal_maint',   'calendar','Jadwal Maintenance',       'maintenance',$mod,$sub,'jadwal_maint') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=laporan_maint',  'bar-chart-2','Laporan Maintenance',      'maintenance',$mod,$sub,'laporan_maint') ?>

        <?= sideSection('Arsip Digital') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=kelola_arsip',   'folder','Kelola Arsip Digital',       'arsip',$mod,$sub,'kelola_arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=klasifikasi',    'tag','Kelola Klasifikasi Arsip',   'arsip',$mod,$sub,'klasifikasi') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=pencarian_arsip','search','Pencarian Arsip',            'arsip',$mod,$sub,'pencarian_arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=backup_restore', 'save','Backup &amp; Restore',      'arsip',$mod,$sub,'backup_restore') ?>

        <?= sideSection('Laporan & Analitik') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=dashboard_analitik','trending-up','Dashboard Analitik',        'laporan',$mod,$sub,'dashboard_analitik') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=generate_laporan',  'file-text','Generate Laporan Analitik','laporan',$mod,$sub,'generate_laporan') ?>

        <?= sideSection('Administrasi') ?>
        <?= sideNavItem('dashboard.php?mod=users','key','Kelola Pengguna','users',$mod) ?>

        <?php elseif ($role === 'staff'): ?>
        <?= sideSection('Inventaris') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=pinjam', 'upload-cloud','Pinjam Barang',            'inventory',$mod,$sub,'pinjam') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=riwayat','clipboard-list','Lihat Riwayat Peminjaman', 'inventory',$mod,$sub,'riwayat') ?>

        <?= sideSection('Aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=aktivitas&sub=input_aktivitas',  'pencil','Input Aktivitas Harian','aktivitas',$mod,$sub,'input_aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=aktivitas&sub=riwayat_aktivitas','clipboard-list','Riwayat Aktivitas',     'aktivitas',$mod,$sub,'riwayat_aktivitas') ?>

        <?= sideSection('Event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=event_saya','ticket','Lihat Event Saya','event',$mod,$sub,'event_saya') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=reminder',  'bell','Lihat Reminder',  'event',$mod,$sub,'reminder') ?>

        <?= sideSection('Maintenance') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=request_maint','wrench','Ajukan Request','maintenance',$mod,$sub,'request_maint') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=lacak_request','search','Lacak Request', 'maintenance',$mod,$sub,'lacak_request') ?>

        <?= sideSection('Arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=cari_arsip',   'search','Cari Arsip',    'arsip',$mod,$sub,'cari_arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=upload_dok',   'upload','Upload Dokumen','arsip',$mod,$sub,'upload_dok') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=riwayat_arsip','clipboard-list','Riwayat Upload','arsip',$mod,$sub,'riwayat_arsip') ?>

        <?php elseif ($role === 'karyawan'): ?>
        <?= sideSection('Menu Saya') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=profil','user','Lihat / Edit Profil','karyawan',$mod,$sub,'profil') ?>

        <?= sideSection('Absensi & Aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=absensi&sub=absensi_harian',      'clipboard-list','Absensi Harian',       'absensi', $mod,$sub,'absensi_harian') ?>
        <?= sideNavItem('dashboard.php?mod=aktivitas&sub=input_aktivitas',   'pencil','Input Aktivitas Harian','aktivitas',$mod,$sub,'input_aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=aktivitas&sub=riwayat_aktivitas', 'file-signature','Riwayat Aktivitas',    'aktivitas',$mod,$sub,'riwayat_aktivitas') ?>

        <?= sideSection('Maintenance') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=request_maint','wrench','Request Maintenance','maintenance',$mod,$sub,'request_maint') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=lacak_request','search','Lacak Request',      'maintenance',$mod,$sub,'lacak_request') ?>

        <?= sideSection('Dokumen') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=upload_dok',   'upload','Upload Dokumen','arsip',$mod,$sub,'upload_dok') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=riwayat_arsip','clipboard-list','Riwayat Upload','arsip',$mod,$sub,'riwayat_arsip') ?>

        <?= sideSection('Notifikasi') ?>
        <?= sideNavItem('dashboard.php?mod=notifikasi','bell','Lihat Notifikasi','notifikasi',$mod) ?>

        <?php elseif ($role === 'admin_hr'): ?>
        <?= sideSection('Manajemen Karyawan') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=data_karyawan','users','Kelola Data Karyawan','karyawan',$mod,$sub,'data_karyawan') ?>

        <?= sideSection('Absensi') ?>
        <?= sideNavItem('dashboard.php?mod=absensi&sub=rekap_absensi','clipboard-list','Rekap Absensi','absensi',$mod,$sub,'rekap_absensi') ?>
        <?= sideNavItem('dashboard.php?mod=absensi&sub=cek_absensi', 'search','Cek Absensi',  'absensi',$mod,$sub,'cek_absensi') ?>

        <?= sideSection('Aktivitas & Kinerja') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=monitoring_aktivitas','file-signature','Monitoring Aktivitas','karyawan',$mod,$sub,'monitoring_aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=monitoring_kinerja',  'bar-chart-2','Monitoring Kinerja',  'karyawan',$mod,$sub,'monitoring_kinerja') ?>

        <?= sideSection('Laporan') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=laporan_sdm',    'file-text','Laporan SDM',    'laporan',$mod,$sub,'laporan_sdm') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=laporan_kinerja','trending-up','Laporan Kinerja','laporan',$mod,$sub,'laporan_kinerja') ?>

        <?php elseif ($role === 'eo'): ?>
        <?= sideSection('Kelola Event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=buat_event',   'plus-circle','Buat Event',                    'event',$mod,$sub,'buat_event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=kelola_event', 'pencil','Kelola / Edit Event',            'event',$mod,$sub,'kelola_event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=assign',       'users','Assign Personel &amp; Fasilitas','event',$mod,$sub,'assign') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=update_status','refresh-cw','Update Status Event',            'event',$mod,$sub,'update_status') ?>

        <?= sideSection('Laporan') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=laporan_event','bar-chart-2','Laporan Event','laporan',$mod,$sub,'laporan_event') ?>

        <?php elseif ($role === 'admin_fasilitas'): ?>
        <?= sideSection('Fasilitas') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=kelola_fasilitas','hammer','Kelola Data Fasilitas',    'maintenance',$mod,$sub,'kelola_fasilitas') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=jadwal_maint',   'calendar','Kelola Jadwal Maintenance','maintenance',$mod,$sub,'jadwal_maint') ?>

        <?= sideSection('Laporan') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=laporan_maint','bar-chart-2','Laporan Maintenance','laporan',$mod,$sub,'laporan_maint') ?>

        <?php elseif ($role === 'teknisi'): ?>
        <?= sideSection('Tugas Saya') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=tugas_maint', 'file-text','Lihat Tugas',           'maintenance',$mod,$sub,'tugas_maint', $pendingMaintCount, 'badge-maint') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=jadwal_maint','calendar','Jadwal Maintenance',    'maintenance',$mod,$sub,'jadwal_maint') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=proses_maint','tool','Proses / Update Status','maintenance',$mod,$sub,'proses_maint') ?>

        <?php elseif ($role === 'manager'): ?>
        <?= sideSection('Analitik & Laporan') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=dashboard_analitik','trending-up','Dashboard Analitik',        'laporan',$mod,$sub,'dashboard_analitik') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=generate_laporan',  'file-text','Generate Laporan Analitik','laporan',$mod,$sub,'generate_laporan') ?>

        <?= sideSection('Approval & Evaluasi') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=approval',  'check-circle','Approval Peminjaman','inventory',$mod,$sub,'approval',$pendingPeminjamanCount, 'badge-peminjaman') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=evaluasi_event','award','Evaluasi Event',     'event',$mod,$sub,'evaluasi_event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=daftar_event',  'tent','Daftar Event',       'event',$mod,$sub,'daftar_event') ?>

        <?= sideSection('Monitoring') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=laporan_maint','tool','Laporan Maintenance','maintenance',$mod,$sub,'laporan_maint') ?>

        <?= sideSection('Arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=lihat_arsip',  'folder-open','Lihat Arsip',   'arsip',$mod,$sub,'lihat_arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=laporan_arsip','bar-chart-2','Laporan Arsip', 'arsip',$mod,$sub,'laporan_arsip') ?>

        <?php elseif ($role === 'admin_arsip'): ?>
        <?= sideSection('Kelola Arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=kelola_arsip',   'folder','Kelola Arsip Digital',     'arsip',$mod,$sub,'kelola_arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=klasifikasi',    'tag','Kelola Klasifikasi Arsip', 'arsip',$mod,$sub,'klasifikasi') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=pencarian_arsip','search','Pencarian Arsip',          'arsip',$mod,$sub,'pencarian_arsip') ?>

        <?= sideSection('Sistem') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=backup_restore','save','Backup &amp; Restore','arsip',$mod,$sub,'backup_restore') ?>

        <?= sideSection('Laporan') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=laporan_arsip','bar-chart-2','Laporan Arsip','laporan',$mod,$sub,'laporan_arsip') ?>

        <?php endif; ?>

        <?= sideSection('Akun Saya') ?>
        <?= sideNavItem('dashboard.php?mod=profile','user','Profil Saya','profile',$mod) ?>

    </nav>


</aside>
