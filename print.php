<?php
require_once 'config/auth.php';
requireLogin();
$user = getCurrentUser(); $role = $user['role'];
$type = $_GET['type'] ?? 'inventory';
$title = ['inventory'=>'Laporan Inventaris Barang','peminjaman'=>'Laporan Peminjaman Barang','karyawan'=>'Laporan Data Karyawan',
    'event'=>'Laporan Event Management','maintenance'=>'Laporan Maintenance Fasilitas',
    'arsip'=>'Laporan Arsip Digital','aktivitas'=>'Laporan Aktivitas Harian'][$type] ?? 'Laporan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><title><?=$title?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;font-size:13px;color:#1a2e22;background:#fff;padding:0}
@media screen{body{padding:20px;max-width:960px;margin:0 auto;background:#f7faf8}
  .print-card{background:#fff;border-radius:12px;padding:32px;box-shadow:0 4px 24px rgba(15,69,37,.1)}}
@media print{.no-print{display:none!important}.print-card{border-radius:0;box-shadow:none;padding:0}}

/* HEADER */
.rpt-header{border-bottom:3px solid #0f4525;padding-bottom:18px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-start}
.rpt-logo-wrap{display:flex;align-items:center;gap:14px}
.rpt-logo-wrap img{height:52px}
.rpt-org strong{display:block;font-size:15px;font-weight:700;color:#0f4525}
.rpt-org span{font-size:11.5px;color:#5a7a64}
.rpt-meta{text-align:right;font-size:12px;color:#5a7a64}
.rpt-meta strong{display:block;font-size:16px;color:#0f4525;font-weight:700;margin-bottom:2px}

/* TITLE */
.rpt-title{background:linear-gradient(135deg,#0f4525,#2d9b5a);color:#fff;
  padding:16px 22px;border-radius:8px;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between}
.rpt-title h2{font-size:17px;font-weight:700}
.rpt-title span{font-size:12px;opacity:.85}

/* SUMMARY BOXES */
.sum-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:22px}
.sum-box{padding:14px 16px;border-radius:8px;border-left:4px solid}
.sum-box.blue{background:#eaf4fb;border-color:#2980b9}
.sum-box.green{background:#eafaf1;border-color:#27ae60}
.sum-box.orange{background:#fef9e7;border-color:#f39c12}
.sum-box.red{background:#fdedec;border-color:#e74c3c}
.sum-box .sv{font-size:26px;font-weight:800;line-height:1;margin-bottom:3px}
.sum-box .sl{font-size:11.5px;color:#5a7a64}

/* TABLE */
table{width:100%;border-collapse:collapse;margin-bottom:20px}
thead th{background:#0f4525;color:#fff;padding:10px 13px;text-align:left;font-size:11.5px;font-weight:600;letter-spacing:.4px;text-transform:uppercase}
thead th:first-child{border-radius:0}
tbody td{padding:9px 13px;border-bottom:1px solid #e8f0eb;font-size:12.5px;vertical-align:middle}
tbody tr:nth-child(even){background:#f7faf8}
tbody tr:hover{background:#e8f5ed}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600}
.b-ok{background:#d4edda;color:#155724}.b-warn{background:#fff3cd;color:#856404}
.b-danger{background:#fdecea;color:#c0392b}.b-info{background:#d1ecf1;color:#0c5460}

/* FOOTER */
.rpt-footer{margin-top:28px;padding-top:16px;border-top:1px solid #cce0d4;
  display:flex;justify-content:space-between;font-size:11.5px;color:#5a7a64}
.sign-box{text-align:center;min-width:180px}
.sign-line{margin-top:50px;border-top:1px solid #aaa;padding-top:6px;font-size:12px;color:#1a2e22;font-weight:600}

/* PRINT BTN */
.print-bar{margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap}
.pbtn{padding:9px 20px;border-radius:8px;border:none;cursor:pointer;font-size:13.5px;font-family:'DM Sans',sans-serif;font-weight:600;transition:.2s;text-decoration:none;display:inline-flex;align-items:center;gap:7px}
.pbtn-primary{background:#0f4525;color:#fff}.pbtn-primary:hover{background:#1a6b3c}
.pbtn-outline{background:#fff;border:1.5px solid #cce0d4;color:#1a2e22}.pbtn-outline:hover{background:#e8f5ed}
.pbtn-danger{background:#fdecea;color:#c0392b;border:1.5px solid #fccac6}
</style>
</head>
<body>
<div class="print-card">
  <!-- PRINT BAR -->
  <div class="print-bar no-print">
    <button class="pbtn pbtn-primary" onclick="downloadPDF()">🖨️ Download PDF</button>
    <a href="javascript:history.back()" class="pbtn pbtn-outline">← Kembali</a>
    <span style="margin-left:auto;font-size:12px;color:#5a7a64;padding:9px 0;">
      Dicetak oleh: <strong><?=htmlspecialchars($user['name'])?></strong> · <?=date('d F Y H:i')?>
    </span>
  </div>

  <!-- HEADER -->
  <div class="rpt-header">
    <div class="rpt-logo-wrap">
      <img src="logo.png" alt="Logo">
      <div class="rpt-org">
        <strong>Perguruan Islam Al Syukro Universal</strong>
        <span>Jl. Raya Ciputat No.1, Tangerang Selatan</span><br>
        <span>Telp: (021) 7494671 · alsyukro.sch.id</span>
      </div>
    </div>
    <div class="rpt-meta">
      <strong><?=$title?></strong>
      <span>Tanggal: <?=date('d F Y')?></span><br>
      <span>Dicetak: <?=date('H:i:s')?></span><br>
      <span>Oleh: <?=htmlspecialchars($user['name'])?></span>
    </div>
  </div>

  <!-- TITLE BAR -->
  <div class="rpt-title">
    <h2>📊 <?=$title?></h2>
    <span>Periode: <?=date('F Y')?></span>
  </div>

  <?php
  function rf($n){
      global $conn;
      $res = $conn->query("SELECT * FROM `$n`");
      return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
  }

  if ($type === 'inventory'):
    $items = rf('inventory'); $pinjam = rf('peminjaman');
    $total=count($items); $tersedia=count(array_filter($items,fn($x)=>$x['status']==='tersedia'));
    $sdipinjam=count(array_filter($items,fn($x)=>$x['status']==='dipinjam'));
    $maint=count(array_filter($items,fn($x)=>$x['status']==='maintenance'));
  ?>
  <div class="sum-grid">
    <div class="sum-box blue"><div class="sv"><?=$total?></div><div class="sl">Total Barang</div></div>
    <div class="sum-box green"><div class="sv"><?=$tersedia?></div><div class="sl">Tersedia</div></div>
    <div class="sum-box orange"><div class="sv"><?=$sdipinjam?></div><div class="sl">Sedang Dipinjam</div></div>
    <div class="sum-box red"><div class="sv"><?=$maint?></div><div class="sl">Maintenance</div></div>
  </div>
  <table>
    <thead><tr><th>#</th><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Lokasi</th><th>Stok</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($items as $i=>$it):
      $sb=match($it['status']??''){
        'tersedia'=>'<span class="badge b-ok">✅ Tersedia</span>',
        'dipinjam'=>'<span class="badge b-warn">🔄 Dipinjam</span>',
        'maintenance'=>'<span class="badge b-danger">🔧 Maintenance</span>',
        default=>'<span class="badge b-info">'.$it['status'].'</span>'};
    ?><tr>
      <td><?=$i+1?></td><td><code><?=$it['kode']??'INV-'.str_pad($it['id'],3,'0',STR_PAD_LEFT)?></code></td>
      <td><strong><?=htmlspecialchars($it['nama']??'')?></strong></td>
      <td><?=htmlspecialchars($it['kategori']??'')?></td><td><?=htmlspecialchars($it['lokasi']??'')?></td>
      <td><?=$it['stok']??0?></td><td><?=$sb?></td>
    </tr><?php endforeach; ?>
    </tbody>
  </table>

  <?php elseif($type==='peminjaman'):
    $loans=rf('peminjaman'); $pending=array_filter($loans,fn($x)=>($x['status']??'')==='pending');
    $aktif=array_filter($loans,fn($x)=>($x['status']??'')==='dipinjam');
    $kembali=array_filter($loans,fn($x)=>($x['status']??'')==='dikembalikan');
  ?>
  <div class="sum-grid">
    <div class="sum-box blue"><div class="sv"><?=count($loans)?></div><div class="sl">Total Transaksi</div></div>
    <div class="sum-box red"><div class="sv"><?=count($pending)?></div><div class="sl">Pending Approval</div></div>
    <div class="sum-box orange"><div class="sv"><?=count($aktif)?></div><div class="sl">Sedang Dipinjam</div></div>
    <div class="sum-box green"><div class="sv"><?=count($kembali)?></div><div class="sl">Dikembalikan</div></div>
  </div>
  <table>
    <thead><tr><th>#</th><th>Kode</th><th>Peminjam</th><th>Barang</th><th>Jml</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($loans as $i=>$l):
      $sb=match($l['status']??''){
        'pending'=>'<span class="badge b-info">⏳ Pending</span>',
        'dipinjam'=>'<span class="badge b-warn">🔄 Dipinjam</span>',
        'dikembalikan'=>'<span class="badge b-ok">✅ Kembali</span>',
        'ditolak'=>'<span class="badge b-danger">❌ Ditolak</span>',
        default=>'<span class="badge b-info">'.$l['status'].'</span>'};
    ?><tr>
      <td><?=$i+1?></td><td><code><?=$l['kode']??'PJM-'.$l['id']?></code></td>
      <td><?=htmlspecialchars($l['peminjam']??'')?></td><td><?=htmlspecialchars($l['barang']??'')?></td>
      <td><?=$l['jumlah']??1?></td><td><?=$l['tgl_pinjam']??''?></td>
      <td><?=$l['tgl_kembali']??''?></td><td><?=$sb?></td>
    </tr><?php endforeach; ?>
    </tbody>
  </table>

  <?php elseif($type==='karyawan'):
    $karys=rf('karyawan'); $aktif=count(array_filter($karys,fn($x)=>($x['status']??'')==='aktif'));
  ?>
  <div class="sum-grid">
    <div class="sum-box blue"><div class="sv"><?=count($karys)?></div><div class="sl">Total Karyawan</div></div>
    <div class="sum-box green"><div class="sv"><?=$aktif?></div><div class="sl">Aktif</div></div>
    <div class="sum-box orange"><div class="sv"><?=count($karys)-$aktif?></div><div class="sl">Cuti/Tidak Aktif</div></div>
    <div class="sum-box blue" style="border-color:#9b59b6;background:#f4ecf7;">
      <div class="sv" style="color:#9b59b6;"><?=count($karys)>0?round(array_sum(array_column($karys,'kinerja'))/count($karys)).'%':'0%'?></div>
      <div class="sl">Rata-rata Kinerja</div>
    </div>
  </div>
  <table>
    <thead><tr><th>#</th><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Unit</th><th>Tgl Bergabung</th><th>Kehadiran</th><th>Kinerja</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($karys as $i=>$k):
      $sb=($k['status']??'')==='aktif'?'<span class="badge b-ok">Aktif</span>':'<span class="badge b-warn">Cuti</span>';
    ?><tr>
      <td><?=$i+1?></td><td><code><?=$k['nip']??''?></code></td>
      <td><strong><?=htmlspecialchars($k['nama']??'')?></strong></td>
      <td><?=htmlspecialchars($k['jabatan']??'')?></td><td><?=htmlspecialchars($k['unit']??'')?></td>
      <td><?=$k['tgl_bergabung']??''?></td><td><?=$k['kehadiran']??0?>%</td>
      <td><?=$k['kinerja']??0?>%</td><td><?=$sb?></td>
    </tr><?php endforeach; ?>
    </tbody>
  </table>

  <?php elseif($type==='event'):
    $events=rf('event'); $prep=count(array_filter($events,fn($x)=>($x['status']??'')==='persiapan'));
    $selesai=count(array_filter($events,fn($x)=>($x['status']??'')==='selesai'));
  ?>
  <div class="sum-grid">
    <div class="sum-box blue"><div class="sv"><?=count($events)?></div><div class="sl">Total Event</div></div>
    <div class="sum-box orange"><div class="sv"><?=$prep?></div><div class="sl">Persiapan</div></div>
    <div class="sum-box green"><div class="sv"><?=$selesai?></div><div class="sl">Selesai</div></div>
    <div class="sum-box blue" style="border-color:#e74c3c;background:#fdedec;">
      <div class="sv" style="color:#e74c3c;"><?=count($events)-$prep-$selesai?></div><div class="sl">Berlangsung</div>
    </div>
  </div>
  <table>
    <thead><tr><th>#</th><th>Kode</th><th>Nama Event</th><th>Jenis</th><th>Tgl Mulai</th><th>Lokasi</th><th>Personel</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($events as $i=>$e):
      $sb=match($e['status']??''){
        'persiapan'=>'<span class="badge b-warn">⏳ Persiapan</span>',
        'berlangsung'=>'<span class="badge b-info">🔄 Berlangsung</span>',
        'selesai'=>'<span class="badge b-ok">✅ Selesai</span>',
        default=>'<span class="badge">'.$e['status'].'</span>'};
    ?><tr>
      <td><?=$i+1?></td><td><code><?=$e['kode']??''?></code></td>
      <td><strong><?=htmlspecialchars($e['nama']??'')?></strong></td>
      <td><?=htmlspecialchars($e['jenis']??'')?></td><td><?=$e['tgl_mulai']??''?></td>
      <td><?=htmlspecialchars($e['lokasi']??'')?></td><td><?=$e['personel']??0?> orang</td>
      <td><?=$sb?></td>
    </tr><?php endforeach; ?>
    </tbody>
  </table>

  <?php elseif($type==='maintenance'):
    $mnts=rf('maintenance'); $done=count(array_filter($mnts,fn($x)=>($x['status']??'')==='selesai'));
  ?>
  <div class="sum-grid">
    <div class="sum-box blue"><div class="sv"><?=count($mnts)?></div><div class="sl">Total Pekerjaan</div></div>
    <div class="sum-box green"><div class="sv"><?=$done?></div><div class="sl">Selesai</div></div>
    <div class="sum-box orange"><div class="sv"><?=count($mnts)-$done?></div><div class="sl">Belum Selesai</div></div>
    <div class="sum-box red"><div class="sv"><?=count(array_filter($mnts,fn($x)=>($x['prioritas']??'')==='Darurat'))?></div><div class="sl">Darurat</div></div>
  </div>
  <table>
    <thead><tr><th>#</th><th>Kode</th><th>Fasilitas</th><th>Jenis</th><th>Prioritas</th><th>Teknisi</th><th>Tgl Jadwal</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($mnts as $i=>$m):
      $sb=match($m['status']??''){
        'selesai'=>'<span class="badge b-ok">✅ Selesai</span>',
        'dalam proses'=>'<span class="badge b-warn">🔧 Proses</span>',
        'terjadwal'=>'<span class="badge b-info">📅 Terjadwal</span>',
        default=>'<span class="badge">'.$m['status'].'</span>'};
      $pb=match($m['prioritas']??''){
        'Darurat'=>'<span class="badge b-danger">🔴 Darurat</span>',
        'Tinggi'=>'<span class="badge b-warn">🟡 Tinggi</span>',
        default=>'<span class="badge b-ok">⚪ Normal</span>'};
    ?><tr>
      <td><?=$i+1?></td><td><code><?=$m['kode']??''?></code></td>
      <td><strong><?=htmlspecialchars($m['fasilitas']??'')?></strong></td>
      <td><?=htmlspecialchars($m['jenis']??'')?></td><td><?=$pb?></td>
      <td><?=htmlspecialchars($m['teknisi']??'')?></td>
      <td><?=$m['tgl_jadwal']??''?></td><td><?=$sb?></td>
    </tr><?php endforeach; ?>
    </tbody>
  </table>

  <?php elseif($type==='arsip'):
    $arsips=rf('arsip');
    $pub=count(array_filter($arsips,fn($x)=>($x['kerahasiaan']??'')==='Publik'));
    $kats=count(array_unique(array_column($arsips,'kategori')));
  ?>
  <div class="sum-grid">
    <div class="sum-box blue"><div class="sv"><?=count($arsips)?></div><div class="sl">Total Dokumen</div></div>
    <div class="sum-box green"><div class="sv"><?=$kats?></div><div class="sl">Kategori</div></div>
    <div class="sum-box orange"><div class="sv"><?=$pub?></div><div class="sl">Publik</div></div>
    <div class="sum-box blue" style="border-color:#8e44ad;background:#f4ecf7"><div class="sv" style="color:#8e44ad"><?=count($arsips)-$pub?></div><div class="sl">Internal/Rahasia</div></div>
  </div>
  <table>
    <thead><tr><th>#</th><th>Kode</th><th>Nama Dokumen</th><th>Kategori</th><th>Tahun</th><th>Ukuran</th><th>Kerahasiaan</th><th>Diupload</th></tr></thead>
    <tbody>
    <?php foreach($arsips as $i=>$a): ?>
    <tr>
      <td><?=$i+1?></td><td><code><?=$a['kode']??''?></code></td>
      <td><strong><?=htmlspecialchars($a['nama']??'')?></strong></td>
      <td><span class="badge b-info"><?=$a['kategori']??''?></span></td>
      <td><?=$a['tahun']??''?></td><td><?=$a['ukuran']??''?></td>
      <td><span class="badge <?=($a['kerahasiaan']??'')==='Rahasia'?'b-danger':(($a['kerahasiaan']??'')==='Publik'?'b-ok':'b-warn')?>"><?=$a['kerahasiaan']??''?></span></td>
      <td><?=htmlspecialchars($a['uploader']??'')?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <?php endif; ?>

  <?php if($type==='aktivitas'):
    $akts=rf('aktivitas');
  ?>
  <div class="sum-grid">
    <div class="sum-box blue"><div class="sv"><?=count($akts)?></div><div class="sl">Total Aktivitas</div></div>
    <div class="sum-box green"><div class="sv"><?=count(array_unique(array_column($akts,'nama')))?></div><div class="sl">Karyawan Aktif</div></div>
    <div class="sum-box orange"><div class="sv"><?=date('d M Y')?></div><div class="sl">Tanggal Cetak</div></div>
  </div>
  <table>
    <thead><tr><th>#</th><th>Nama</th><th>Unit</th><th>Tanggal</th><th>Jenis</th><th>Deskripsi</th></tr></thead>
    <tbody>
    <?php foreach($akts as $i=>$a): ?>
    <tr>
      <td><?=$i+1?></td>
      <td><strong><?=htmlspecialchars($a['nama']??'')?></strong></td>
      <td><?=htmlspecialchars($a['unit']??'')?></td>
      <td><?=$a['tanggal']??''?></td>
      <td><span class="badge b-info"><?=$a['jenis']??''?></span></td>
      <td><?=htmlspecialchars($a['deskripsi']??'')?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <!-- FOOTER & TANDA TANGAN -->
  <div class="rpt-footer">
    <div>
      <div style="margin-bottom:6px;font-size:12px;">Keterangan: Laporan ini digenerate secara otomatis oleh sistem AL-SYUKROSMART OPS</div>
      <div style="font-size:11.5px;">© <?=date('Y')?> Perguruan Islam Al Syukro Universal · Semua data bersifat rahasia</div>
    </div>
    <div style="display:flex;gap:40px">
      <div class="sign-box">
        <div style="font-size:12px;color:#5a7a64">Diperiksa oleh,</div>
        <div class="sign-line">Kepala Unit Terkait</div>
      </div>
      <div class="sign-box">
        <div style="font-size:12px;color:#5a7a64">Disetujui oleh,</div>
        <div class="sign-line">Kepala Sekolah</div>
      </div>
    </div>
  </div>
</div>
<script>
function downloadPDF() {
    const element = document.querySelector('.print-card');
    const printBar = document.querySelector('.print-bar');
    
    // Sembunyikan tombol sebelum generate
    printBar.style.display = 'none';
    
    const opt = {
        margin:       10,
        filename:     '<?=str_replace(" ", "_", $title)?>_<?=date("Ymd_Hi")?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    
    // Generate PDF lalu tampilkan tombol kembali
    html2pdf().set(opt).from(element).save().then(() => {
        printBar.style.display = 'flex';
    });
}
</script>
</body>
</html>
