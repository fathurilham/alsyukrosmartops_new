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

function sideNavItem($href, $icon, $label, $activeMod, $curMod, $curSub = '', $activeSub = '', $badge = 0) {
    $isActive = ($curMod === $activeMod && ($activeSub === '' || $curSub === $activeSub));
    $cls      = $isActive ? 'nav-item active' : 'nav-item';
    $bdg      = $badge ? "<span class='nav-badge'>$badge</span>" : '';
    return "<a href='$href' class='$cls'><span class='nav-icon'>$icon</span> $label $bdg</a>";
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
?>

<div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99;"></div>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="logo-icon">
                <img src="logo.png" alt="Logo" style="height:34px;width:auto;display:block;object-fit:contain;">
            </div>
            <div class="logo-text">
                <strong>AL-SYUKROSMART</strong>
                <span>OPS System</span>
            </div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar" style="background:<?= $roleColor ?>;"><?= $userInitial ?></div>
        <div class="user-info">
            <strong><?= htmlspecialchars($user['name'] ?? 'User') ?></strong>
            <span><?= $roleName ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">

        <?= sideSection('Beranda') ?>
        <?= sideNavItem('dashboard.php', '🏠', 'Dashboard', '', $mod) ?>

        <?php if ($role === 'admin'): ?>
        <?= sideSection('Inventaris') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=barang',     '📦', 'Kelola Data Barang',       'inventory', $mod, $sub, 'barang') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=approval',   '✅', 'Persetujuan Peminjaman',   'inventory', $mod, $sub, 'approval', $pendingPeminjamanCount) ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=laporan_inv','📄', 'Laporan Inventaris',       'inventory', $mod, $sub, 'laporan_inv') ?>

        <?= sideSection('SDM & Karyawan') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=data_karyawan',       '👥','Kelola Data Karyawan',  'karyawan',$mod,$sub,'data_karyawan') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=rekap_absensi',       '📋','Rekap Absensi',          'karyawan',$mod,$sub,'rekap_absensi') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=monitoring_aktivitas','📝','Monitoring Aktivitas',   'karyawan',$mod,$sub,'monitoring_aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=monitoring_kinerja',  '📊','Monitoring Kinerja',     'karyawan',$mod,$sub,'monitoring_kinerja') ?>

        <?= sideSection('Event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=approval_event',  '✅','Approval Event',    'event',$mod,$sub,'approval_event',$pendingEventCount) ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=monitoring_event','📡','Monitoring Event',  'event',$mod,$sub,'monitoring_event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=daftar_event',    '🎪','Daftar Event',      'event',$mod,$sub,'daftar_event') ?>

        <?= sideSection('Maintenance') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=kelola_fasilitas','🏗️','Kelola Data Fasilitas',   'maintenance',$mod,$sub,'kelola_fasilitas') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=jadwal_maint',   '📅','Jadwal Maintenance',       'maintenance',$mod,$sub,'jadwal_maint') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=laporan_maint',  '📊','Laporan Maintenance',      'maintenance',$mod,$sub,'laporan_maint') ?>

        <?= sideSection('Arsip Digital') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=kelola_arsip',   '📁','Kelola Arsip Digital',       'arsip',$mod,$sub,'kelola_arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=klasifikasi',    '🏷️','Kelola Klasifikasi Arsip',   'arsip',$mod,$sub,'klasifikasi') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=pencarian_arsip','🔍','Pencarian Arsip',            'arsip',$mod,$sub,'pencarian_arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=backup_restore', '💾','Backup &amp; Restore',      'arsip',$mod,$sub,'backup_restore') ?>

        <?= sideSection('Laporan & Analitik') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=dashboard_analitik','📈','Dashboard Analitik',        'laporan',$mod,$sub,'dashboard_analitik') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=generate_laporan',  '📄','Generate Laporan Analitik','laporan',$mod,$sub,'generate_laporan') ?>

        <?= sideSection('Administrasi') ?>
        <?= sideNavItem('dashboard.php?mod=users','🔑','Kelola Pengguna','users',$mod) ?>

        <?php elseif ($role === 'staff'): ?>
        <?= sideSection('Inventaris') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=pinjam', '📤','Pinjam Barang',            'inventory',$mod,$sub,'pinjam') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=kembali','📥','Kembalikan Barang',        'inventory',$mod,$sub,'kembali') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=riwayat','📋','Lihat Riwayat Peminjaman', 'inventory',$mod,$sub,'riwayat') ?>

        <?= sideSection('Aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=aktivitas&sub=input_aktivitas',  '✏️','Input Aktivitas Harian','aktivitas',$mod,$sub,'input_aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=aktivitas&sub=riwayat_aktivitas','📋','Riwayat Aktivitas',     'aktivitas',$mod,$sub,'riwayat_aktivitas') ?>

        <?= sideSection('Event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=event_saya','🎫','Lihat Event Saya','event',$mod,$sub,'event_saya') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=reminder',  '🔔','Lihat Reminder',  'event',$mod,$sub,'reminder') ?>

        <?= sideSection('Maintenance') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=request_maint','🛠️','Ajukan Request','maintenance',$mod,$sub,'request_maint') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=lacak_request','🔍','Lacak Request', 'maintenance',$mod,$sub,'lacak_request') ?>

        <?= sideSection('Arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=cari_arsip',   '🔍','Cari Arsip',    'arsip',$mod,$sub,'cari_arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=upload_dok',   '⬆️','Upload Dokumen','arsip',$mod,$sub,'upload_dok') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=riwayat_arsip','📋','Riwayat Upload','arsip',$mod,$sub,'riwayat_arsip') ?>

        <?php elseif ($role === 'karyawan'): ?>
        <?= sideSection('Menu Saya') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=profil','👤','Lihat / Edit Profil','karyawan',$mod,$sub,'profil') ?>

        <?= sideSection('Absensi & Aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=absensi&sub=absensi_harian',      '📋','Absensi Harian',       'absensi', $mod,$sub,'absensi_harian') ?>
        <?= sideNavItem('dashboard.php?mod=aktivitas&sub=input_aktivitas',   '✏️','Input Aktivitas Harian','aktivitas',$mod,$sub,'input_aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=aktivitas&sub=riwayat_aktivitas', '📝','Riwayat Aktivitas',    'aktivitas',$mod,$sub,'riwayat_aktivitas') ?>

        <?= sideSection('Maintenance') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=request_maint','🛠️','Request Maintenance','maintenance',$mod,$sub,'request_maint') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=lacak_request','🔍','Lacak Request',      'maintenance',$mod,$sub,'lacak_request') ?>

        <?= sideSection('Dokumen') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=upload_dok',   '⬆️','Upload Dokumen','arsip',$mod,$sub,'upload_dok') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=riwayat_arsip','📋','Riwayat Upload','arsip',$mod,$sub,'riwayat_arsip') ?>

        <?= sideSection('Notifikasi') ?>
        <?= sideNavItem('dashboard.php?mod=notifikasi','🔔','Lihat Notifikasi','notifikasi',$mod) ?>

        <?php elseif ($role === 'admin_hr'): ?>
        <?= sideSection('Manajemen Karyawan') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=data_karyawan','👥','Kelola Data Karyawan','karyawan',$mod,$sub,'data_karyawan') ?>

        <?= sideSection('Absensi') ?>
        <?= sideNavItem('dashboard.php?mod=absensi&sub=rekap_absensi','📋','Rekap Absensi','absensi',$mod,$sub,'rekap_absensi') ?>
        <?= sideNavItem('dashboard.php?mod=absensi&sub=cek_absensi', '🔍','Cek Absensi',  'absensi',$mod,$sub,'cek_absensi') ?>

        <?= sideSection('Aktivitas & Kinerja') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=monitoring_aktivitas','📝','Monitoring Aktivitas','karyawan',$mod,$sub,'monitoring_aktivitas') ?>
        <?= sideNavItem('dashboard.php?mod=karyawan&sub=monitoring_kinerja',  '📊','Monitoring Kinerja',  'karyawan',$mod,$sub,'monitoring_kinerja') ?>

        <?= sideSection('Laporan') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=laporan_sdm',    '📄','Laporan SDM',    'laporan',$mod,$sub,'laporan_sdm') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=laporan_kinerja','📈','Laporan Kinerja','laporan',$mod,$sub,'laporan_kinerja') ?>

        <?php elseif ($role === 'eo'): ?>
        <?= sideSection('Kelola Event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=buat_event',   '➕','Buat Event',                    'event',$mod,$sub,'buat_event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=kelola_event', '✏️','Kelola / Edit Event',            'event',$mod,$sub,'kelola_event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=assign',       '👥','Assign Personel &amp; Fasilitas','event',$mod,$sub,'assign') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=update_status','🔄','Update Status Event',            'event',$mod,$sub,'update_status') ?>

        <?= sideSection('Laporan') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=laporan_event','📊','Laporan Event','laporan',$mod,$sub,'laporan_event') ?>

        <?php elseif ($role === 'admin_fasilitas'): ?>
        <?= sideSection('Fasilitas') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=kelola_fasilitas','🏗️','Kelola Data Fasilitas',    'maintenance',$mod,$sub,'kelola_fasilitas') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=jadwal_maint',   '📅','Kelola Jadwal Maintenance','maintenance',$mod,$sub,'jadwal_maint') ?>

        <?= sideSection('Laporan') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=laporan_maint','📊','Laporan Maintenance','laporan',$mod,$sub,'laporan_maint') ?>

        <?php elseif ($role === 'teknisi'): ?>
        <?= sideSection('Tugas Saya') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=tugas_maint', '🗒️','Lihat Tugas',           'maintenance',$mod,$sub,'tugas_maint') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=jadwal_maint','📅','Jadwal Maintenance',    'maintenance',$mod,$sub,'jadwal_maint') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=proses_maint','🔧','Proses / Update Status','maintenance',$mod,$sub,'proses_maint') ?>

        <?php elseif ($role === 'manager'): ?>
        <?= sideSection('Analitik & Laporan') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=dashboard_analitik','📈','Dashboard Analitik',        'laporan',$mod,$sub,'dashboard_analitik') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=generate_laporan',  '📄','Generate Laporan Analitik','laporan',$mod,$sub,'generate_laporan') ?>

        <?= sideSection('Approval & Evaluasi') ?>
        <?= sideNavItem('dashboard.php?mod=inventory&sub=approval',  '✅','Approval Peminjaman','inventory',$mod,$sub,'approval',$pendingPeminjamanCount) ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=evaluasi_event','🏆','Evaluasi Event',     'event',$mod,$sub,'evaluasi_event') ?>
        <?= sideNavItem('dashboard.php?mod=event&sub=daftar_event',  '🎪','Daftar Event',       'event',$mod,$sub,'daftar_event') ?>

        <?= sideSection('Monitoring') ?>
        <?= sideNavItem('dashboard.php?mod=maintenance&sub=laporan_maint','🔧','Laporan Maintenance','maintenance',$mod,$sub,'laporan_maint') ?>

        <?= sideSection('Arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=lihat_arsip',  '📂','Lihat Arsip',   'arsip',$mod,$sub,'lihat_arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=laporan_arsip','📊','Laporan Arsip', 'arsip',$mod,$sub,'laporan_arsip') ?>

        <?php elseif ($role === 'admin_arsip'): ?>
        <?= sideSection('Kelola Arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=kelola_arsip',   '📁','Kelola Arsip Digital',     'arsip',$mod,$sub,'kelola_arsip') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=klasifikasi',    '🏷️','Kelola Klasifikasi Arsip', 'arsip',$mod,$sub,'klasifikasi') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=pencarian_arsip','🔍','Pencarian Arsip',          'arsip',$mod,$sub,'pencarian_arsip') ?>

        <?= sideSection('Sistem') ?>
        <?= sideNavItem('dashboard.php?mod=arsip&sub=backup_restore','💾','Backup &amp; Restore','arsip',$mod,$sub,'backup_restore') ?>

        <?= sideSection('Laporan') ?>
        <?= sideNavItem('dashboard.php?mod=laporan&sub=laporan_arsip','📊','Laporan Arsip','laporan',$mod,$sub,'laporan_arsip') ?>

        <?php endif; ?>

        <?= sideSection('Akun Saya') ?>
        <?= sideNavItem('dashboard.php?mod=profile','👤','Profil Saya','profile',$mod) ?>

    </nav>


</aside>
