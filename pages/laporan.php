<?php
/**
 * MODUL LAPORAN & ANALITIK
 * Use Case hal.1: Manager=Dashboard Analitik, Generate Laporan Analitik
 */
$inv  = $conn->query("SELECT * FROM inventory")->fetch_all(MYSQLI_ASSOC);
$kryw = $conn->query("SELECT * FROM karyawan")->fetch_all(MYSQLI_ASSOC);
$evs  = $conn->query("SELECT * FROM event")->fetch_all(MYSQLI_ASSOC);
$mnt  = $conn->query("SELECT * FROM maintenance")->fetch_all(MYSQLI_ASSOC);
$arsip= $conn->query("SELECT * FROM arsip")->fetch_all(MYSQLI_ASSOC);

$sub = $_GET['sub'] ?? '';
if (!$sub) {
    $sub = match($role) {
        'admin','manager'         => 'dashboard_analitik',
        'admin_hr'                => 'laporan_sdm',
        'eo'                      => 'laporan_event',
        'admin_fasilitas','teknisi'=> 'laporan_maint',
        'admin_arsip'             => 'laporan_arsip',
        default                   => 'dashboard_analitik',
    };
}
?>

<!-- TAB NAV -->
<div class="tab-nav-bar">
<?php if (in_array($role,['admin','manager'])): ?>
    <a href="?mod=laporan&sub=dashboard_analitik" class="tab-link <?= $sub==='dashboard_analitik'?'active':'' ?>">📈 Dashboard Analitik</a>
    <a href="?mod=laporan&sub=generate_laporan"   class="tab-link <?= $sub==='generate_laporan'?'active':'' ?>">📄 Generate Laporan</a>
<?php endif; ?>
<?php if (in_array($role,['admin','admin_hr'])): ?>
    <a href="?mod=laporan&sub=laporan_sdm"     class="tab-link <?= $sub==='laporan_sdm'?'active':'' ?>">👥 Laporan SDM</a>
    <a href="?mod=laporan&sub=laporan_kinerja" class="tab-link <?= $sub==='laporan_kinerja'?'active':'' ?>">📊 Laporan Kinerja</a>
<?php endif; ?>
<?php if (in_array($role,['admin','eo','manager'])): ?>
    <a href="?mod=laporan&sub=laporan_event" class="tab-link <?= $sub==='laporan_event'?'active':'' ?>">🎪 Laporan Event</a>
<?php endif; ?>
<?php if (in_array($role,['admin','admin_fasilitas','teknisi','manager'])): ?>
    <a href="?mod=laporan&sub=laporan_maint" class="tab-link <?= $sub==='laporan_maint'?'active':'' ?>">🔧 Laporan Maintenance</a>
<?php endif; ?>
<?php if (in_array($role,['admin','admin_arsip','manager'])): ?>
    <a href="?mod=laporan&sub=laporan_arsip" class="tab-link <?= $sub==='laporan_arsip'?'active':'' ?>">📁 Laporan Arsip</a>
<?php endif; ?>
</div>

<?php
// ═══ DASHBOARD ANALITIK ═══
if ($sub === 'dashboard_analitik'): ?>

<!-- KPI CARDS -->
<div class="stats-grid">
    <div class="stat-card c-blue"><div class="stat-icon c-blue">📦</div>
        <div class="stat-info"><h3><?= count($inv) ?></h3><p>Total Inventaris</p>
            <div class="stat-change up">▲ <?= round(count(array_filter($inv,fn($i)=>$i['status']==='tersedia'))/count($inv)*100) ?>% tersedia</div>
        </div>
    </div>
    <div class="stat-card c-green"><div class="stat-icon c-green">👥</div>
        <div class="stat-info"><h3><?= count($kryw) ?></h3><p>Total Karyawan Aktif</p>
            <div class="stat-change up">▲ <?= count(array_filter($kryw,fn($k)=>$k['status']==='aktif')) ?> aktif</div>
        </div>
    </div>
    <div class="stat-card c-purple"><div class="stat-icon c-purple">🎪</div>
        <div class="stat-info"><h3><?= count($evs) ?></h3><p>Total Event</p>
            <div class="stat-change up">▲ <?= count(array_filter($evs,fn($e)=>$e['status']==='selesai')) ?> selesai</div>
        </div>
    </div>
    <div class="stat-card c-teal"><div class="stat-icon c-teal">📊</div>
        <div class="stat-info"><h3>87%</h3><p>Efisiensi Operasional</p>
            <div class="stat-change up">▲ +5% dari bulan lalu</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Progress per Modul -->
    <div class="card">
        <div class="card-header"><div class="card-title">📊 Performa per Modul</div></div>
        <div class="card-body">
            <?php
            $modPerf=[
                ['📦 Inventaris',round(count(array_filter($inv,fn($i)=>$i['status']==='tersedia'))/max(1,count($inv))*100),'#2980b9','Ketersediaan barang'],
                ['👥 SDM',round(count(array_filter($kryw,fn($k)=>$k['status']==='aktif'))/max(1,count($kryw))*100),'#27ae60','Karyawan aktif'],
                ['🎪 Event',round(count(array_filter($evs,fn($e)=>$e['status']==='selesai'))/max(1,count($evs))*100),'#9b59b6','Event selesai'],
                ['🔧 Fasilitas',round(count(array_filter($mnt,fn($m)=>$m['kondisi']==='baik'))/max(1,count($mnt))*100),'#e67e22','Kondisi baik'],
                ['📁 Arsip',min(100,round(count($arsip)/20*100)),'#5a7a64','Volume dokumen'],
            ];
            foreach($modPerf as $mp): ?>
            <div style="margin-bottom:18px;">
                <div class="flex justify-between" style="margin-bottom:6px;">
                    <span style="font-size:14px;font-weight:600;"><?= $mp[0] ?></span>
                    <span style="font-size:13px;font-weight:700;color:<?= $mp[2] ?>;"><?= $mp[1] ?>%</span>
                </div>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $mp[1] ?>%;background:<?= $mp[2] ?>;"></div></div>
                <div style="font-size:11px;color:#6b8070;margin-top:3px;"><?= $mp[3] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Ringkasan Cepat -->
    <div class="card">
        <div class="card-header"><div class="card-title">⚡ Ringkasan Cepat</div></div>
        <div class="card-body" style="padding:0;">
            <?php
            $summaries=[
                ['Total barang dalam gudang','#eaf4fb',count($inv),'📦'],
                ['Peminjaman aktif saat ini','#fff8e1',count(array_filter($conn->query("SELECT * FROM peminjaman")->fetch_all(MYSQLI_ASSOC),fn($p)=>$p['status']==='dipinjam')),'🔄'],
                ['Total Karyawan Aktif','#eafaf1',count(array_filter($kryw,fn($k)=>$k['status']==='aktif')),'✅'],
                ['Event dalam persiapan','#f4ecf7',count(array_filter($evs,fn($e)=>$e['status']==='persiapan')),'🎪'],
                ['Fasilitas perlu perbaikan','#fdedec',count(array_filter($mnt,fn($m)=>$m['kondisi']!=='baik')),'⚠️'],
                ['Total dokumen arsip','#f0f5f2',count($arsip),'📁'],
            ];
            foreach($summaries as $s): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid #e8f0eb;background:<?= $s[1] ?>;">
                <span style="font-size:14px;"><?= $s[3] ?> <?= $s[0] ?></span>
                <strong style="font-size:20px;"><?= $s[2] ?></strong>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// ═══ GENERATE LAPORAN ═══
elseif ($sub === 'generate_laporan'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">📄 Generate Laporan Analitik</div></div>
    <div class="card-body">
        <div class="module-grid">
            <?php
            $laps=[
                ['📦','Laporan Inventaris','Rekap stok, peminjaman, dan pengembalian barang','inventory','#eaf4fb'],
                ['👥','Laporan SDM','Data karyawan, kehadiran, dan monitoring kinerja','karyawan','#eafaf1'],
                ['🎪','Laporan Event','Evaluasi kegiatan dan dokumentasi event','event','#f4ecf7'],
                ['🔧','Laporan Maintenance','Status fasilitas dan rekap perbaikan','maintenance','#fef9e7'],
                ['📁','Laporan Arsip','Statistik dokumen dan aktivitas pengarsipan','arsip','#f0f5f2'],
                ['📊','Laporan Komprehensif','Ringkasan seluruh modul operasional','komprehensif','#eaf4fb'],
            ];
            foreach($laps as $l): ?>
            <div style="background:<?= $l[4] ?>;border-radius:12px;padding:22px;">
                <div style="font-size:32px;margin-bottom:10px;"><?= $l[0] ?></div>
                <h4 style="font-size:15px;font-weight:700;margin-bottom:5px;"><?= $l[1] ?></h4>
                <p style="font-size:13px;color:#6b8070;margin-bottom:16px;"><?= $l[2] ?></p>
                <div style="display:flex;gap:8px;">
                    <a href="print.php?type=<?= $l[3] ?>" target="_blank" class="btn btn-primary-sm btn-sm">🖨️ Cetak / PDF</a>
                    <button class="btn btn-outline btn-sm" onclick="toast('Preview laporan dibuka...','info')">👁️ Preview</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// ═══ LAPORAN SDM ═══
elseif ($sub === 'laporan_sdm'): ?>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">👥 Laporan SDM</div>
            <a href="print.php?type=karyawan" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px;">
                <?php $aktif=count(array_filter($kryw,fn($k)=>$k['status']==='aktif')); ?>
                <div style="background:#eafaf1;border-radius:8px;padding:14px;text-align:center;">
                    <div style="font-size:28px;font-weight:800;color:#27ae60;"><?= $aktif ?></div>
                    <div style="font-size:12px;color:#6b8070;">Karyawan Aktif</div>
                </div>
                <div style="background:#fdedec;border-radius:8px;padding:14px;text-align:center;">
                    <div style="font-size:28px;font-weight:800;color:#e74c3c;"><?= count($kryw)-$aktif ?></div>
                    <div style="font-size:12px;color:#6b8070;">Cuti / Tidak Aktif</div>
                </div>
            </div>
            <?php $units=[]; foreach($kryw as $k) $units[$k['unit']]=($units[$k['unit']]??0)+1; arsort($units);
            foreach($units as $u=>$j): $p=round($j/count($kryw)*100); ?>
            <div style="margin-bottom:14px;">
                <div class="flex justify-between" style="margin-bottom:5px;"><span style="font-size:13px;font-weight:600;"><?= $u ?></span><span style="font-size:12px;color:#6b8070;"><?= $j ?> orang</span></div>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $p ?>%;background:#27ae60;"></div></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php
// ═══ LAPORAN KINERJA ═══
elseif ($sub === 'laporan_kinerja'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">📊 Laporan Kinerja Karyawan</div>
        <a href="print.php?type=karyawan" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak Laporan</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Karyawan</th><th>Unit</th><th>Kehadiran</th><th>Aktivitas (bln ini)</th><th>Ketepatan</th><th>Skor Akhir</th><th>Grade</th></tr></thead>
            <tbody>
            <?php
            $aktivitas_all = $conn->query("SELECT nama, COUNT(*) as jml FROM aktivitas WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE()) GROUP BY nama")->fetch_all(MYSQLI_ASSOC);
            $akt_map = [];
            foreach($aktivitas_all as $a) $akt_map[$a['nama']] = $a['jml'];

            foreach($kryw as $k): 
                if (($k['status'] ?? '') !== 'aktif') continue;
                $nama = $k['nama'];
                $unit = $k['unit'];
                $kehadiran = $k['kehadiran'] ?? 0;
                $skor = $k['kinerja'] ?? 0;
                $jml_akt = $akt_map[$nama] ?? 0;
                
                if ($skor >= 95) $grade = 'A+';
                elseif ($skor >= 90) $grade = 'A';
                elseif ($skor >= 85) $grade = 'B+';
                elseif ($skor >= 80) $grade = 'B';
                elseif ($skor >= 70) $grade = 'C';
                else $grade = 'D';

                $gc = $grade[0] === 'A' ? 'badge-success' : ($grade[0] === 'B' ? 'badge-info' : 'badge-warning'); 
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($nama) ?></strong></td>
                <td><span class="badge badge-secondary"><?= htmlspecialchars($unit) ?></span></td>
                <td><div class="flex items-center gap-2"><?= $kehadiran ?>%<div class="progress-bar" style="width:60px;margin:0;"><div class="progress-fill" style="width:<?= $kehadiran ?>%;background:#27ae60;"></div></div></div></td>
                <td><?= $jml_akt ?> kegiatan</td>
                <td><?= $skor ?>%</td>
                <td><strong style="font-size:16px;"><?= $skor ?></strong></td>
                <td><span class="badge <?= $gc ?>"><?= $grade ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// ═══ LAPORAN EVENT ═══
elseif ($sub === 'laporan_event'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">🎪 Laporan Event</div>
        <a href="print.php?type=event" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama Event</th><th>Jenis</th><th>Tanggal</th><th>Personel</th><th>Status</th><th>Evaluasi</th></tr></thead>
            <tbody>
            <?php foreach($evs as $ev):
                $sb=match($ev['status']){'persiapan'=>'<span class="badge badge-warning">⏳ Persiapan</span>','berlangsung'=>'<span class="badge badge-primary">🔄 Berlangsung</span>','selesai'=>'<span class="badge badge-success">✅ Selesai</span>',default=>'<span class="badge badge-secondary">'.$ev['status'].'</span>'};
                $skor=$ev['status']==='selesai'?rand(80,98):null;
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($ev['nama']) ?></strong></td>
                <td><span class="badge badge-info"><?= $ev['jenis'] ?></span></td>
                <td>📅 <?= date('d M Y',strtotime($ev['tanggal_mulai'])) ?></td>
                <td>👥 <?= $ev['personel'] ?></td>
                <td><?= $sb ?></td>
                <td><?= $skor ? "<strong style='color:#27ae60;'>$skor%</strong> ⭐".number_format($skor/20,1) : '<span class="text-muted">—</span>' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// ═══ LAPORAN MAINTENANCE ═══
elseif ($sub === 'laporan_maint'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">🔧 Laporan Maintenance Fasilitas</div>
        <a href="print.php?type=maintenance" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Fasilitas</th><th>Lokasi</th><th>Kondisi</th><th>Maint. Terakhir</th><th>Maint. Berikutnya</th><th>Teknisi</th></tr></thead>
            <tbody>
            <?php foreach($mnt as $m):
                $kb=match($m['kondisi']){'baik'=>'<span class="badge badge-success">✅ Baik</span>','perbaikan'=>'<span class="badge badge-danger">⚠️ Perbaikan</span>','maintenance'=>'<span class="badge badge-warning">🔧 Maintenance</span>',default=>'<span class="badge badge-secondary">'.$m['kondisi'].'</span>'};
            ?>
            <tr>
                <td><code class="kode"><?= $m['kode'] ?></code></td>
                <td><strong><?= htmlspecialchars($m['nama']) ?></strong></td>
                <td>📍 <?= $m['lokasi'] ?></td>
                <td><?= $kb ?></td>
                <td><?= $m['maint_terakhir'] ?></td>
                <td><?= $m['maint_berikut'] ?></td>
                <td>🔧 <?= htmlspecialchars($m['teknisi']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// ═══ LAPORAN ARSIP ═══
elseif ($sub === 'laporan_arsip'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">📁 Laporan Arsip Digital</div>
        <a href="print.php?type=arsip" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Nama Dokumen</th><th>Kategori</th><th>Tahun</th><th>Tipe</th><th>Kerahasiaan</th><th>Diupload</th></tr></thead>
            <tbody>
            <?php foreach($arsip as $a): ?>
            <tr>
                <td><code class="kode"><?= $a['kode'] ?></code></td>
                <td><strong><?= htmlspecialchars($a['nama']) ?></strong></td>
                <td><span class="badge badge-info"><?= $a['kategori'] ?></span></td>
                <td><?= $a['tahun'] ?></td>
                <td><span class="badge badge-secondary"><?= strtoupper($a['tipe']) ?></span></td>
                <td><?php $rb=['publik'=>'badge-success','internal'=>'badge-info','rahasia'=>'badge-danger']; ?><span class="badge <?= $rb[$a['kerahasiaan']]??'badge-secondary' ?>"><?= $a['kerahasiaan'] ?></span></td>
                <td style="font-size:12px;"><?= $a['tgl_upload'] ?> · <?= $a['upload_by'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>
