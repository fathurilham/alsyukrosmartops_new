<?php
/**
 * MODUL ARSIP DIGITAL
 * Diagram hal.2 — AdminArsip: Kelola|Klasifikasi|Pencarian|Backup
 *                  Manager: Lihat Arsip|Laporan
 *                  Staff: Cari Arsip|Upload|Riwayat
 */

$arsips = $conn->query("SELECT * FROM arsip")->fetch_all(MYSQLI_ASSOC);
$sub = $_GET['sub'] ?? '';
if (!$sub) {
    $sub = match($role) {
        'admin','admin_arsip' => 'kelola_arsip',
        'manager'             => 'lihat_arsip',
        default               => 'cari_arsip',
    };
}

function tipeBadge($t){
    $icons=['pdf'=>'📄','xlsx'=>'📊','docx'=>'📝','zip'=>'🗜️','jpg'=>'🖼️','png'=>'🖼️'];
    $icon = $icons[$t] ?? '📁';
    return "<span class='badge badge-secondary'>$icon ".strtoupper($t)."</span>";
}

function rahasia_badge($r){
    return match($r){
        'publik'   => '<span class="badge badge-success">🌐 Publik</span>',
        'internal' => '<span class="badge badge-info">🏢 Internal</span>',
        'rahasia'  => '<span class="badge badge-danger">🔒 Rahasia</span>',
        default    => '<span class="badge badge-secondary">'.$r.'</span>',
    };
}

$byKat = [];
foreach($arsips as $a) $byKat[$a['kategori']] = ($byKat[$a['kategori']] ?? 0) + 1;
?>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card c-blue"><div class="stat-icon c-blue">📁</div>
        <div class="stat-info"><h3><?= count($arsips) ?></h3><p>Total Dokumen</p></div>
    </div>
    <div class="stat-card c-teal"><div class="stat-icon c-teal">🏷️</div>
        <div class="stat-info"><h3><?= count($byKat) ?></h3><p>Kategori Arsip</p></div>
    </div>
    <div class="stat-card c-green"><div class="stat-icon c-green">🌐</div>
        <div class="stat-info"><h3><?= count(array_filter($arsips,fn($a)=>$a['kerahasiaan']==='publik')) ?></h3><p>Dokumen Publik</p></div>
    </div>
    <div class="stat-card c-orange"><div class="stat-icon c-orange">💾</div>
        <div class="stat-info"><h3>3 hari</h3><p>Backup Terakhir</p></div>
    </div>
</div>

<!-- TAB NAV -->
<div class="tab-nav-bar">
<?php if (in_array($role,['admin','admin_arsip'])): ?>
    <a href="?mod=arsip&sub=kelola_arsip"    class="tab-link <?= $sub==='kelola_arsip'?'active':'' ?>">📁 Kelola Arsip Digital</a>
    <a href="?mod=arsip&sub=klasifikasi"     class="tab-link <?= $sub==='klasifikasi'?'active':'' ?>">🏷️ Kelola Klasifikasi</a>
    <a href="?mod=arsip&sub=pencarian_arsip" class="tab-link <?= $sub==='pencarian_arsip'?'active':'' ?>">🔍 Pencarian Arsip</a>
    <a href="?mod=arsip&sub=backup_restore"  class="tab-link <?= $sub==='backup_restore'?'active':'' ?>">💾 Backup &amp; Restore</a>
<?php elseif ($role==='manager'): ?>
    <a href="?mod=arsip&sub=lihat_arsip"   class="tab-link <?= $sub==='lihat_arsip'?'active':'' ?>">📂 Lihat Arsip</a>
    <a href="?mod=arsip&sub=laporan_arsip" class="tab-link <?= $sub==='laporan_arsip'?'active':'' ?>">📊 Laporan Arsip</a>
<?php else: // staff / karyawan ?>
    <a href="?mod=arsip&sub=cari_arsip"    class="tab-link <?= $sub==='cari_arsip'?'active':'' ?>">🔍 Cari Arsip</a>
    <a href="?mod=arsip&sub=upload_dok"    class="tab-link <?= $sub==='upload_dok'?'active':'' ?>">⬆️ Upload Dokumen</a>
    <a href="?mod=arsip&sub=riwayat_arsip" class="tab-link <?= $sub==='riwayat_arsip'?'active':'' ?>">📋 Riwayat Upload</a>
<?php endif; ?>
</div>

<?php
// ═══ SUB: KELOLA / LIHAT ARSIP ═══
if (in_array($sub,['kelola_arsip','lihat_arsip','cari_arsip','riwayat_arsip'])): ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <?= $sub==='kelola_arsip'?'📁 Kelola Arsip Digital':($sub==='lihat_arsip'?'📂 Lihat Arsip':'📋 Daftar Arsip') ?>
        </div>
        <div class="flex gap-2">
            <div class="search-input-wrap" style="width:220px;"><span class="search-icon">🔍</span><input type="text" id="tableSearch" placeholder="Cari dokumen..."></div>
            <?php if(in_array($role,['admin','admin_arsip'])): ?>
            <button class="btn btn-success btn-sm" onclick="openModal('modalUploadArsip')">⬆️ Upload Baru</button>
            <?php endif; ?>
            <a href="print.php?type=arsip" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
        </div>
    </div>
    <!-- Filter kategori -->
    <div style="padding:12px 20px;border-bottom:1px solid #e8f0eb;display:flex;gap:8px;flex-wrap:wrap;">
        <a href="?mod=arsip&sub=<?= $sub ?>" class="btn btn-sm <?= !isset($_GET['kat'])?'btn-primary-sm':'btn-outline' ?>">Semua</a>
        <?php foreach($byKat as $kat=>$jml): ?>
        <a href="?mod=arsip&sub=<?= $sub ?>&kat=<?= urlencode($kat) ?>" class="btn btn-sm <?= ($_GET['kat']??'')===$kat?'btn-primary-sm':'btn-outline' ?>"><?= $kat ?> (<?= $jml ?>)</a>
        <?php endforeach; ?>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Nama Dokumen</th><th>Kategori</th><th>Tahun</th><th>Tipe</th><th>Ukuran</th><th>Kerahasiaan</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php
            $filtered = isset($_GET['kat']) ? array_filter($arsips, fn($a)=>$a['kategori']===$_GET['kat']) : $arsips;
            foreach($filtered as $a): ?>
            <tr>
                <td><code class="kode"><?= $a['kode'] ?></code></td>
                <td><strong><?= htmlspecialchars($a['nama']) ?></strong><br><span class="text-xs text-muted">📤 <?= $a['upload_by'] ?> · <?= $a['tgl_upload'] ?></span></td>
                <td><span class="badge badge-info"><?= $a['kategori'] ?></span></td>
                <td><?= $a['tahun'] ?></td>
                <td><?= tipeBadge($a['tipe']) ?></td>
                <td><?= $a['ukuran'] ?></td>
                <td><?= rahasia_badge($a['kerahasiaan']) ?></td>
                <td>
                    <div class="flex gap-2">
                        <button class="btn btn-info btn-sm" onclick="toast('Membuka dokumen...','info')">👁️ Lihat</button>
                        <button class="btn btn-success btn-sm" onclick="toast('Mengunduh dokumen...','success')">⬇️ Unduh</button>
                        <?php if(in_array($role,['admin','admin_arsip'])): ?>
                        <button class="btn btn-danger btn-sm" onclick="hapusArsip(<?= $a['id'] ?>)">🗑️</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Upload Arsip -->
<div class="modal-overlay" id="modalUploadArsip">
    <div class="modal">
        <div class="modal-header"><h3>⬆️ Upload Dokumen Baru</h3><button class="modal-close" onclick="closeModal('modalUploadArsip')">✕</button></div>
        <div class="modal-body">
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Nama Dokumen <span style="color:red">*</span></label><input type="text" id="upNama" required></div>
                <div class="form-field"><label>Kategori <span style="color:red">*</span></label>
                    <select id="upKat"><option>SDM</option><option>Keuangan</option><option>Akademik</option><option>Administrasi</option><option>Dokumentasi</option><option>Sarana</option></select>
                </div>
            </div>
            <div class="field-group cols-3" style="margin-bottom:14px;">
                <div class="form-field"><label>Tahun</label><input type="number" id="upTahun" value="<?= date('Y') ?>"></div>
                <div class="form-field"><label>Kerahasiaan</label>
                    <select id="upRahasia"><option value="publik">🌐 Publik</option><option value="internal">🏢 Internal</option><option value="rahasia">🔒 Rahasia</option></select>
                </div>
                <div class="form-field"><label>Tipe File</label>
                    <select id="upTipe"><option value="pdf">PDF</option><option value="docx">DOCX</option><option value="xlsx">XLSX</option><option value="zip">ZIP</option><option value="jpg">JPG</option></select>
                </div>
            </div>
            <input type="file" id="upFile" style="display:none;" accept=".pdf,.docx,.xlsx,.zip,.jpg,.png">
            <div id="dropZone" style="border:2px dashed #d4e4da;border-radius:10px;padding:28px;text-align:center;background:#f8faf9;cursor:pointer;transition:all 0.2s;">
                <div style="font-size:36px;margin-bottom:8px;">📁</div>
                <p id="dropText" style="font-size:14px;color:#6b8070;">Klik untuk memilih file atau drag &amp; drop</p>
                <p style="font-size:12px;color:#bbb;margin-top:4px;">PDF, DOCX, XLSX, ZIP — Maks. 5MB</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalUploadArsip')">Batal</button>
            <button class="btn btn-success" onclick="uploadArsip()">⬆️ Upload Dokumen</button>
        </div>
    </div>
</div>
<script>
async function uploadArsip(){
    const fileInput = document.getElementById('upFile');
    let ukuran = '—';
    let fileObj = '';
    if(fileInput && fileInput.files.length) {
        ukuran = (fileInput.files[0].size/1024).toFixed(0) + ' KB';
        fileObj = fileInput.files[0];
    } else {
        toast('Harap pilih file terlebih dahulu!','warning');
        return;
    }
    const res=await apiFetch('arsip','add',{nama:document.getElementById('upNama').value,kategori:document.getElementById('upKat').value,tahun:document.getElementById('upTahun').value,kerahasiaan:document.getElementById('upRahasia').value,tipe:document.getElementById('upTipe').value,ukuran:ukuran,file:fileObj});
    if(res.ok){toast('Dokumen berhasil diupload!','success');closeModal('modalUploadArsip');setTimeout(()=>location.reload(),900);}
    else toast(res.msg||'Gagal','error');
}
async function hapusArsip(id){
    let ok = false;
    if (typeof customConfirm === 'function') {
        ok = await customConfirm('Hapus arsip ini?', 'Ya, Hapus', 'btn-danger', '🗑️');
    } else {
        ok = confirm('Hapus arsip ini?');
    }
    if(!ok) return;
    const res = await apiFetch('arsip','delete',{id});
    if(res.ok){toast('Arsip dihapus','success');setTimeout(()=>location.reload(),800);}
}
</script>

<?php
// ═══ SUB: UPLOAD DOKUMEN (Staff/Karyawan) ═══
elseif ($sub === 'upload_dok'): ?>
<div class="card" style="max-width:640px;">
    <div class="card-header"><div class="card-title">⬆️ Upload Dokumen Baru</div></div>
    <div class="card-body">
        <div class="field-group cols-2" style="margin-bottom:14px;">
            <div class="form-field"><label>Nama Dokumen <span style="color:red">*</span></label><input type="text" id="upNama2" required></div>
            <div class="form-field"><label>Kategori</label>
                <select id="upKat2"><option>SDM</option><option>Keuangan</option><option>Akademik</option><option>Administrasi</option><option>Dokumentasi</option><option>Sarana</option></select>
            </div>
        </div>
        <div class="field-group cols-2" style="margin-bottom:14px;">
            <div class="form-field"><label>Tahun Dokumen</label><input type="number" id="upTahun2" value="<?= date('Y') ?>"></div>
            <div class="form-field"><label>Tipe File</label>
                <select id="upTipe2"><option value="pdf">PDF</option><option value="docx">DOCX</option><option value="xlsx">XLSX</option><option value="zip">ZIP</option></select>
            </div>
        </div>
        <input type="file" id="upFile2" style="display:none;" accept=".pdf,.docx,.xlsx,.zip,.jpg,.png">
        <div id="dropZone2" style="border:2px dashed #d4e4da;border-radius:10px;padding:28px;text-align:center;background:#f8faf9;cursor:pointer;margin-bottom:16px;transition:all 0.2s;">
            <div style="font-size:40px;margin-bottom:8px;">📁</div>
            <p id="dropText2" style="font-size:14px;color:#6b8070;">Klik untuk memilih file atau drag &amp; drop</p>
            <p style="font-size:12px;color:#bbb;margin-top:4px;">PDF, DOCX, XLSX, ZIP — Maks. 5MB</p>
        </div>
        <button class="btn btn-success w-full" onclick="uploadArsip2()">⬆️ Upload Sekarang</button>
    </div>
</div>
<script>
async function uploadArsip2(){
    const fileInput = document.getElementById('upFile2');
    let ukuran = '—';
    let fileObj = '';
    if(fileInput && fileInput.files.length) {
        ukuran = (fileInput.files[0].size/1024).toFixed(0) + ' KB';
        fileObj = fileInput.files[0];
    } else {
        toast('Harap pilih file terlebih dahulu!','warning');
        return;
    }
    const nama=document.getElementById('upNama2').value;
    if(!nama){toast('Nama dokumen wajib diisi!','error');return;}
    const res=await apiFetch('arsip','add',{nama,kategori:document.getElementById('upKat2').value,tahun:document.getElementById('upTahun2').value,kerahasiaan:'internal',tipe:document.getElementById('upTipe2').value,ukuran:ukuran,file:fileObj});
    if(res.ok){toast('Dokumen berhasil diupload!','success');setTimeout(()=>location.href='?mod=arsip&sub=riwayat_arsip',1200);}
    else toast(res.msg||'Gagal','error');
}

</script>

<?php
// ═══ SUB: PENCARIAN ARSIP ═══
elseif ($sub === 'pencarian_arsip'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">🔍 Pencarian Arsip</div></div>
    <div class="card-body">
        <div class="flex gap-3" style="margin-bottom:20px;">
            <div class="search-input-wrap" style="flex:1;"><span class="search-icon">🔍</span><input type="text" id="cariQuery" placeholder="Ketik nama dokumen, kategori, atau tahun..."></div>
            <select id="cariKat" style="padding:10px 14px;border:1.5px solid #d4e4da;border-radius:8px;font-size:14px;">
                <option value="">Semua Kategori</option>
                <?php foreach($byKat as $k=>$j): ?><option value="<?= $k ?>"><?= $k ?></option><?php endforeach; ?>
            </select>
            <button class="btn btn-primary-sm" onclick="cariArsip()">🔍 Cari</button>
        </div>
        <div id="hasilCari">
            <p style="color:#6b8070;font-size:14px;text-align:center;padding:20px;">Ketik kata kunci untuk mencari arsip...</p>
        </div>
    </div>
</div>
<script>
function cariArsip(){
    const q = document.getElementById('cariQuery').value.toLowerCase();
    const kat = document.getElementById('cariKat').value;
    const arsips = <?= json_encode(array_values($arsips)) ?>;
    const hasil = arsips.filter(a =>
        (a.nama.toLowerCase().includes(q) || a.kategori.toLowerCase().includes(q) || String(a.tahun).includes(q)) &&
        (!kat || a.kategori===kat)
    );
    const div = document.getElementById('hasilCari');
    if(!hasil.length){ div.innerHTML='<p style="color:#6b8070;font-size:14px;text-align:center;padding:20px;">Tidak ditemukan.</p>'; return; }
    div.innerHTML = `<p style="font-size:13px;color:#6b8070;margin-bottom:12px;">Ditemukan ${hasil.length} dokumen</p>` +
        hasil.map(a=>`<div style="padding:14px;border:1px solid #e8f0eb;border-radius:8px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
            <div><strong>${a.nama}</strong><br><span style="font-size:12px;color:#6b8070;">${a.kategori} · ${a.tahun} · ${a.ukuran}</span></div>
            <div style="display:flex;gap:8px;">
                <button class="btn btn-info btn-sm" onclick="toast('Membuka...','info')">👁️ Lihat</button>
                <button class="btn btn-success btn-sm" onclick="toast('Mengunduh...','success')">⬇️ Unduh</button>
            </div>
        </div>`).join('');
}
document.getElementById('cariQuery').addEventListener('keyup', e=>{ if(e.key==='Enter') cariArsip(); });
</script>

<?php
// ═══ SUB: KLASIFIKASI ═══
elseif ($sub === 'klasifikasi'): ?>
<div class="module-grid">
<?php
$katColors=['SDM'=>'#eaf4fb','Keuangan'=>'#eafaf1','Akademik'=>'#f4ecf7','Administrasi'=>'#fef9e7','Dokumentasi'=>'#fdedec','Sarana'=>'#f0f5f2'];
$katIcons=['SDM'=>'👥','Keuangan'=>'💰','Akademik'=>'🎓','Administrasi'=>'📋','Dokumentasi'=>'📸','Sarana'=>'🏗️'];
foreach($byKat as $kat=>$jml): ?>
<div style="background:<?= $katColors[$kat]??'#f8f8f8' ?>;border-radius:12px;padding:22px;cursor:pointer;transition:.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
    <div style="font-size:34px;margin-bottom:10px;"><?= $katIcons[$kat]??'📁' ?></div>
    <h4 style="font-size:15px;font-weight:700;margin-bottom:4px;"><?= $kat ?></h4>
    <p style="font-size:13px;color:#6b8070;margin-bottom:14px;"><?= $jml ?> dokumen</p>
    <div class="flex gap-2">
        <a href="?mod=arsip&sub=kelola_arsip&kat=<?= urlencode($kat) ?>" class="btn btn-outline btn-sm">👁️ Lihat</a>
        <?php if(in_array($role,['admin','admin_arsip'])): ?>
        <button class="btn btn-warning btn-sm" onclick="toast('Edit klasifikasi: <?= $kat ?>','info')">✏️ Edit</button>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php
// ═══ SUB: BACKUP & RESTORE ═══
elseif ($sub === 'backup_restore'): ?>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">💾 Backup &amp; Restore Sistem</div></div>
        <div class="card-body">
            <div style="background:#eafaf1;border-radius:10px;padding:18px;margin-bottom:18px;">
                <div style="font-size:13px;color:#6b8070;">Backup Terakhir</div>
                <div style="font-size:17px;font-weight:700;color:#27ae60;margin-top:4px;">✅ 3 hari lalu — 03 Juni 2025</div>
                <div style="font-size:13px;color:#6b8070;margin-top:4px;">Ukuran: 2.4 GB · Status: Berhasil</div>
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:13px;font-weight:600;margin-bottom:8px;display:block;">Jadwal Backup Otomatis</label>
                <select style="width:100%;padding:10px;border:1.5px solid #d4e4da;border-radius:8px;font-size:14px;">
                    <option>Setiap hari (00:00)</option>
                    <option selected>Setiap minggu (Minggu 00:00)</option>
                    <option>Setiap bulan (Tanggal 1)</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button class="btn btn-success w-full" onclick="toast('Backup dimulai, proses mungkin memakan beberapa menit...','info')">💾 Backup Sekarang</button>
                <button class="btn btn-warning w-full" onclick="toast('Pilih file backup untuk restore...','info')">🔄 Restore</button>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📋 Riwayat Backup</div></div>
        <div style="padding:0;">
            <?php
            $bkps=[['03 Jun 2025','2.4 GB','Berhasil','#d4edda'],['27 Mei 2025','2.3 GB','Berhasil','#d4edda'],['20 Mei 2025','2.1 GB','Berhasil','#d4edda'],['13 Mei 2025','2.0 GB','Gagal','#fdecea'],['06 Mei 2025','1.9 GB','Berhasil','#d4edda']];
            foreach($bkps as $b): ?>
            <div style="padding:13px 20px;border-bottom:1px solid #e8f0eb;display:flex;justify-content:space-between;align-items:center;">
                <div><strong style="font-size:14px;"><?= $b[0] ?></strong><div style="font-size:12px;color:#6b8070;"><?= $b[1] ?></div></div>
                <div class="flex items-center gap-2">
                    <span class="badge" style="background:<?= $b[3] ?>;color:#333;"><?= $b[2] ?></span>
                    <?php if($b[2]==='Berhasil'): ?><button class="btn btn-outline btn-sm" onclick="toast('Restore dari backup <?= $b[0] ?>...','info')">🔄</button><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// ═══ SUB: LAPORAN ARSIP (Manager) ═══
elseif ($sub === 'laporan_arsip'): ?>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">📊 Statistik Arsip per Kategori</div></div>
        <div class="card-body">
            <?php foreach($byKat as $kat=>$jml): $pct=round($jml/count($arsips)*100); ?>
            <div style="margin-bottom:16px;">
                <div class="flex justify-between" style="margin-bottom:6px;">
                    <span style="font-size:14px;font-weight:600;"><?= $kat ?></span>
                    <span style="font-size:12px;color:#6b8070;"><?= $jml ?> dokumen (<?= $pct ?>%)</span>
                </div>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%;background:#1a6b3c;"></div></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📤 Generate Laporan Arsip</div>
            <a href="print.php?type=arsip" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
        </div>
        <div class="card-body">
            <div style="background:#eaf4fb;border-radius:8px;padding:16px;margin-bottom:14px;">
                <div style="font-size:28px;font-weight:800;color:#2980b9;"><?= count($arsips) ?></div>
                <div style="font-size:13px;color:#6b8070;">Total dokumen terarsip</div>
            </div>
            <div style="background:#eafaf1;border-radius:8px;padding:16px;">
                <div style="font-size:28px;font-weight:800;color:#27ae60;"><?= count($byKat) ?></div>
                <div style="font-size:13px;color:#6b8070;">Kategori arsip aktif</div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<script>
function initFileUploader(zoneId, fileInputId, textId) {
    const zone = document.getElementById(zoneId);
    const fileInput = document.getElementById(fileInputId);
    const textEl = document.getElementById(textId);
    if(!zone || !fileInput) return;

    zone.onclick = () => fileInput.click();

    zone.ondragover = (e) => {
        e.preventDefault();
        zone.style.background = '#eaf4fb';
        zone.style.borderColor = '#2980b9';
    };
    zone.ondragleave = (e) => {
        e.preventDefault();
        zone.style.background = '#f8faf9';
        zone.style.borderColor = '#d4e4da';
    };
    zone.ondrop = (e) => {
        e.preventDefault();
        zone.style.background = '#f8faf9';
        zone.style.borderColor = '#d4e4da';
        if(e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(fileInput, textEl);
        }
    };
    fileInput.onchange = () => handleFileSelect(fileInput, textEl);
}

function handleFileSelect(input, textEl) {
    if(!input.files.length) {
        textEl.innerText = "Klik untuk memilih file atau drag & drop";
        return;
    }
    const file = input.files[0];
    if(file.size > 5 * 1024 * 1024) {
        toast('Ukuran file maksimal 5MB!', 'error');
        input.value = '';
        textEl.innerText = "Klik untuk memilih file atau drag & drop";
        return;
    }
    textEl.innerHTML = `<strong style="color:#27ae60;">${file.name}</strong><br><span style="font-size:12px;color:#6b8070;">${(file.size/1024/1024).toFixed(2)} MB</span>`;
}

// Call on load
initFileUploader('dropZone', 'upFile', 'dropText');
initFileUploader('dropZone2', 'upFile2', 'dropText2');
</script>
