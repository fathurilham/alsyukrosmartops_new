<?php
/**
 * MODUL MAINTENANCE FASILITAS
 * Diagram hal.3 — AdminFas: Kelola Fasilitas|Jadwal|Laporan
 *                  Teknisi: Lihat Tugas|Jadwal|Proses/Update Status
 *                  Staff/Karyawan: Ajukan Request|Lacak Request
 */

$fasilitas = $conn->query("SELECT * FROM maintenance")->fetch_all(MYSQLI_ASSOC);
$requests  = $conn->query("SELECT * FROM request_maint")->fetch_all(MYSQLI_ASSOC);

$baik      = count(array_filter($fasilitas, fn($f)=>$f['kondisi']==='baik'));
$perbaikan = count(array_filter($fasilitas, fn($f)=>$f['kondisi']==='perbaikan'));
$maint     = count(array_filter($fasilitas, fn($f)=>$f['kondisi']==='maintenance'));
$pending_req = count(array_filter($requests, fn($r)=>$r['status']==='menunggu'));

$sub = $_GET['sub'] ?? '';
if (!$sub) {
    $sub = match($role) {
        'admin','admin_fasilitas' => 'kelola_fasilitas',
        'teknisi'                 => 'tugas_maint',
        'manager'                 => 'laporan_maint',
        default                   => 'request_maint',
    };
}

function kondisiBadge($k){
    return match($k){
        'baik'        => '<span class="badge badge-success">✅ Baik</span>',
        'perbaikan'   => '<span class="badge badge-danger">⚠️ Perlu Perbaikan</span>',
        'maintenance' => '<span class="badge badge-warning">🔧 Maintenance</span>',
        default       => '<span class="badge badge-secondary">'.$k.'</span>',
    };
}

function statusMaintBadge($s){
    return match($s){
        'terjadwal'   => '<span class="badge badge-info">📅 Terjadwal</span>',
        'dalam_proses'=> '<span class="badge badge-warning">🔧 Proses</span>',
        'selesai'     => '<span class="badge badge-success">✅ Selesai</span>',
        default       => '<span class="badge badge-secondary">'.$s.'</span>',
    };
}
?>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card c-blue"><div class="stat-icon c-blue">🏗️</div>
        <div class="stat-info"><h3><?= count($fasilitas) ?></h3><p>Total Fasilitas</p></div>
    </div>
    <div class="stat-card c-green"><div class="stat-icon c-green">✅</div>
        <div class="stat-info"><h3><?= $baik ?></h3><p>Kondisi Baik</p></div>
    </div>
    <div class="stat-card c-red"><div class="stat-icon c-red">⚠️</div>
        <div class="stat-info"><h3><?= $perbaikan ?></h3><p>Perlu Perbaikan</p></div>
    </div>
    <div class="stat-card c-orange"><div class="stat-icon c-orange">📝</div>
        <div class="stat-info"><h3><?= $pending_req ?></h3><p>Request Masuk</p>
            <?php if($pending_req>0): ?><div class="stat-change down">▲ Segera ditangani</div><?php endif; ?>
        </div>
    </div>
</div>

<!-- TAB NAV -->
<div class="tab-nav-bar">
<?php if (in_array($role,['admin','admin_fasilitas'])): ?>
    <a href="?mod=maintenance&sub=kelola_fasilitas" class="tab-link <?= $sub==='kelola_fasilitas'?'active':'' ?>">🏗️ Kelola Data Fasilitas</a>
    <a href="?mod=maintenance&sub=jadwal_maint"     class="tab-link <?= $sub==='jadwal_maint'?'active':'' ?>">📅 Kelola Jadwal Maintenance</a>
    <a href="?mod=maintenance&sub=laporan_maint"    class="tab-link <?= $sub==='laporan_maint'?'active':'' ?>">📊 Laporan Maintenance</a>
<?php elseif ($role==='teknisi'): ?>
    <a href="?mod=maintenance&sub=tugas_maint"  class="tab-link <?= $sub==='tugas_maint'?'active':'' ?>">🗒️ Lihat Tugas <?php if($maint): ?><span class="tab-badge"><?= $maint ?></span><?php endif; ?></a>
    <a href="?mod=maintenance&sub=jadwal_maint" class="tab-link <?= $sub==='jadwal_maint'?'active':'' ?>">📅 Jadwal Maintenance</a>
    <a href="?mod=maintenance&sub=proses_maint" class="tab-link <?= $sub==='proses_maint'?'active':'' ?>">🔧 Proses / Update Status</a>
<?php elseif ($role==='manager'): ?>
    <a href="?mod=maintenance&sub=laporan_maint" class="tab-link <?= $sub==='laporan_maint'?'active':'' ?>">📊 Laporan Maintenance</a>
    <a href="?mod=maintenance&sub=jadwal_maint"  class="tab-link <?= $sub==='jadwal_maint'?'active':'' ?>">📅 Jadwal Maintenance</a>
<?php else: // staff / karyawan ?>
    <a href="?mod=maintenance&sub=request_maint" class="tab-link <?= $sub==='request_maint'?'active':'' ?>">🛠️ Ajukan Request Maintenance</a>
    <a href="?mod=maintenance&sub=lacak_request" class="tab-link <?= $sub==='lacak_request'?'active':'' ?>">🔍 Lacak Request</a>
<?php endif; ?>
</div>

<?php
// ═══ SUB: KELOLA DATA FASILITAS ═══
if ($sub === 'kelola_fasilitas'): ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">🏗️ Data Fasilitas Sekolah</div>
        <div class="flex gap-2">
            <div class="search-input-wrap" style="width:200px;"><span class="search-icon">🔍</span><input type="text" id="tableSearch" placeholder="Cari fasilitas..."></div>
            <?php if(in_array($role,['admin','admin_fasilitas'])): ?>
            <button class="btn btn-success" onclick="openModal('modalTambahFasilitas')">➕ Tambah Fasilitas</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Nama Fasilitas</th><th>Lokasi</th><th>Kondisi</th><th>Maint. Terakhir</th><th>Maint. Berikutnya</th><th>Teknisi</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($fasilitas as $f):
                $overdue = strtotime($f['maint_berikut'])<time() && $f['kondisi']==='baik';
            ?>
            <tr>
                <td><code class="kode"><?= $f['kode'] ?></code></td>
                <td><strong><?= htmlspecialchars($f['nama']) ?></strong></td>
                <td>📍 <?= $f['lokasi'] ?></td>
                <td><?= kondisiBadge($f['kondisi']) ?></td>
                <td><?= $f['maint_terakhir'] ?></td>
                <td style="color:<?= $overdue?'#e74c3c':'inherit' ?>;">
                    <?= $f['maint_berikut'] ?><?= $overdue?' ⚠️':'' ?>
                </td>
                <td>🔧 <?= htmlspecialchars($f['teknisi']) ?></td>
                <td>
                    <button class="btn btn-outline btn-sm" onclick="editFasilitas(<?= $f['id'] ?>,'<?= htmlspecialchars($f['nama'],ENT_QUOTES) ?>','<?= $f['kondisi'] ?>','<?= $f['teknisi'] ?>')">✏️ Edit</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Fasilitas -->
<div class="modal-overlay" id="modalTambahFasilitas">
    <div class="modal">
        <div class="modal-header"><h3>➕ Tambah Data Fasilitas</h3><button class="modal-close" onclick="closeModal('modalTambahFasilitas')">✕</button></div>
        <div class="modal-body">
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Nama Fasilitas <span style="color:red">*</span></label><input type="text" id="addFNama" required></div>
                <div class="form-field"><label>Lokasi</label><input type="text" id="addFLokasi"></div>
            </div>
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Jadwal Maint. Berikutnya</label><input type="date" id="addFJadwal"></div>
                <div class="form-field"><label>Teknisi PIC</label><input type="text" id="addFTeknisi" placeholder="Nama teknisi"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalTambahFasilitas')">Batal</button>
            <button class="btn btn-success" onclick="simpanFasilitas()">💾 Simpan</button>
        </div>
    </div>
</div>

<!-- Modal Edit Kondisi -->
<div class="modal-overlay" id="modalEditFasilitas">
    <div class="modal">
        <div class="modal-header"><h3>✏️ Update Fasilitas</h3><button class="modal-close" onclick="closeModal('modalEditFasilitas')">✕</button></div>
        <div class="modal-body">
            <input type="hidden" id="editFId">
            <div class="form-field" style="margin-bottom:14px;"><label>Nama Fasilitas</label><input type="text" id="editFNama" readonly style="background:#f0f5f2;"></div>
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Kondisi</label>
                    <select id="editFKondisi"><option value="baik">Baik</option><option value="perbaikan">Perlu Perbaikan</option><option value="maintenance">Maintenance</option></select>
                </div>
                <div class="form-field"><label>Teknisi</label><input type="text" id="editFTeknisi"></div>
            </div>
            <div class="field-group cols-2">
                <div class="form-field"><label>Maint. Terakhir</label><input type="date" id="editFMaintTerakhir" value="<?= date('Y-m-d') ?>"></div>
                <div class="form-field"><label>Maint. Berikutnya</label><input type="date" id="editFMaintBerikut"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalEditFasilitas')">Batal</button>
            <button class="btn btn-primary-sm" onclick="updateFasilitas()">💾 Simpan</button>
        </div>
    </div>
</div>
<script>
function editFasilitas(id,nama,kondisi,teknisi){
    document.getElementById('editFId').value=id;
    document.getElementById('editFNama').value=nama;
    document.getElementById('editFKondisi').value=kondisi;
    document.getElementById('editFTeknisi').value=teknisi;
    openModal('modalEditFasilitas');
}
async function simpanFasilitas(){
    const res=await apiFetch('maintenance','add',{nama:document.getElementById('addFNama').value,lokasi:document.getElementById('addFLokasi').value,maint_berikut:document.getElementById('addFJadwal').value,teknisi:document.getElementById('addFTeknisi').value});
    if(res.ok){toast('Fasilitas berhasil ditambahkan!','success');closeModal('modalTambahFasilitas');setTimeout(()=>location.reload(),900);}
    else toast(res.msg||'Gagal','error');
}
async function updateFasilitas(){
    const res=await apiFetch('maintenance','edit',{id:document.getElementById('editFId').value,kondisi:document.getElementById('editFKondisi').value,teknisi:document.getElementById('editFTeknisi').value,maint_terakhir:document.getElementById('editFMaintTerakhir').value,maint_berikut:document.getElementById('editFMaintBerikut').value});
    if(res.ok){toast('Fasilitas berhasil diperbarui!','success');closeModal('modalEditFasilitas');setTimeout(()=>location.reload(),900);}
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: JADWAL MAINTENANCE ═══
elseif ($sub === 'jadwal_maint'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">📅 Jadwal Maintenance Fasilitas</div>
        <?php if(in_array($role,['admin','admin_fasilitas'])): ?>
        <button class="btn btn-success btn-sm" onclick="toast('Form jadwal maintenance baru ditambahkan','info')">➕ Jadwal Baru</button>
        <?php endif; ?>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Fasilitas</th><th>Kondisi</th><th>Jadwal Maintenance</th><th>Teknisi PIC</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($fasilitas as $f):
                $overdue = strtotime($f['maint_berikut'])<time();
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($f['nama']) ?></strong><br><span class="text-xs text-muted">📍 <?= $f['lokasi'] ?></span></td>
                <td><?= kondisiBadge($f['kondisi']) ?></td>
                <td style="color:<?= $overdue?'#e74c3c':'inherit' ?>;">📅 <?= $f['maint_berikut'] ?><?= $overdue?' <span class="badge badge-danger" style="margin-left:4px;">Overdue</span>':'' ?></td>
                <td>🔧 <?= htmlspecialchars($f['teknisi']) ?></td>
                <td><?= statusMaintBadge($f['status_maint']) ?></td>
                <td>
                    <?php if($role==='teknisi'): ?>
                    <button class="btn btn-success btn-sm" onclick="tandaiSelesai(<?= $f['id'] ?>)">✅ Tandai Selesai</button>
                    <?php else: ?>
                    <button class="btn btn-outline btn-sm" onclick="toast('Detail jadwal maintenance','info')">👁️ Detail</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
async function tandaiSelesai(id){
    if(!confirm('Tandai maintenance sebagai selesai?')) return;
    const res = await apiFetch('maintenance','edit',{id,kondisi:'baik',status_maint:'selesai',maint_terakhir:'<?= date('Y-m-d') ?>'});
    if(res.ok){ toast('Maintenance berhasil diselesaikan!','success'); setTimeout(()=>location.reload(),900); }
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: TUGAS TEKNISI ═══
elseif ($sub === 'tugas_maint'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">🗒️ Tugas Maintenance Saya <?php if($maint+$perbaikan): ?><span class="badge badge-danger" style="margin-left:8px;"><?= $maint+$perbaikan ?> Aktif</span><?php endif; ?></div></div>
    <?php if($maint+$perbaikan>0): ?>
    <div style="background:#fff8e1;border-left:4px solid #f39c12;padding:14px 20px;font-size:14px;">
        🔧 Ada <strong><?= $maint+$perbaikan ?> fasilitas</strong> yang perlu ditangani segera.
    </div>
    <?php endif; ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Fasilitas</th><th>Lokasi</th><th>Kondisi</th><th>Prioritas</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($fasilitas as $f): $isUrgent = $f['kondisi']!=='baik'; ?>
            <tr style="<?= $isUrgent?'background:#fffbf0;':'' ?>">
                <td><strong><?= htmlspecialchars($f['nama']) ?></strong></td>
                <td>📍 <?= $f['lokasi'] ?></td>
                <td><?= kondisiBadge($f['kondisi']) ?></td>
                <td><?= $isUrgent?'<span class="badge badge-danger">🔴 Urgent</span>':'<span class="badge badge-info">🔵 Normal</span>' ?></td>
                <td><?= statusMaintBadge($f['status_maint']) ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="openModal('modalProsesMaint');document.getElementById('prosesFasId').value=<?= $f['id'] ?>;document.getElementById('prosesFasNama').value='<?= htmlspecialchars($f['nama'],ENT_QUOTES) ?>'">🔧 Proses</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Proses Maintenance -->
<div class="modal-overlay" id="modalProsesMaint">
    <div class="modal">
        <div class="modal-header"><h3>🔧 Proses Maintenance</h3><button class="modal-close" onclick="closeModal('modalProsesMaint')">✕</button></div>
        <div class="modal-body">
            <input type="hidden" id="prosesFasId">
            <div class="form-field" style="margin-bottom:14px;"><label>Fasilitas</label><input type="text" id="prosesFasNama" readonly style="background:#f0f5f2;"></div>
            <div class="form-field" style="margin-bottom:14px;"><label>Hasil Pengecekan</label><select id="prosesKondisi"><option value="baik">Sudah diperbaiki / Baik</option><option value="perbaikan">Masih perlu perbaikan lanjut</option><option value="maintenance">Maintenance berkala</option></select></div>
            <div class="form-field"><label>Catatan Pekerjaan</label><textarea id="prosesCatatan" placeholder="Jelaskan apa yang sudah dikerjakan..." style="min-height:80px;"></textarea></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalProsesMaint')">Batal</button>
            <button class="btn btn-success" onclick="prosesMaint()">💾 Simpan Laporan</button>
        </div>
    </div>
</div>
<script>
async function prosesMaint(){
    const id=document.getElementById('prosesFasId').value;
    const kondisi=document.getElementById('prosesKondisi').value;
    const res=await apiFetch('maintenance','edit',{id,kondisi,status_maint:kondisi==='baik'?'selesai':'dalam_proses',maint_terakhir:'<?= date('Y-m-d') ?>'});
    if(res.ok){toast('Laporan maintenance tersimpan!','success');closeModal('modalProsesMaint');setTimeout(()=>location.reload(),900);}
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: PROSES/UPDATE STATUS (Teknisi) ═══
elseif ($sub === 'proses_maint'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">🔧 Proses &amp; Update Status Maintenance</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Fasilitas</th><th>Kondisi</th><th>Status Sekarang</th><th>Update Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($fasilitas as $f): ?>
            <tr>
                <td><strong><?= htmlspecialchars($f['nama']) ?></strong><br><span class="text-xs text-muted">📍 <?= $f['lokasi'] ?></span></td>
                <td><?= kondisiBadge($f['kondisi']) ?></td>
                <td><?= statusMaintBadge($f['status_maint']) ?></td>
                <td>
                    <select id="newMaintStatus<?= $f['id'] ?>" style="padding:7px;border:1.5px solid #d4e4da;border-radius:6px;font-size:13px;">
                        <option value="terjadwal" <?= $f['status_maint']==='terjadwal'?'selected':'' ?>>📅 Terjadwal</option>
                        <option value="dalam_proses" <?= $f['status_maint']==='dalam_proses'?'selected':'' ?>>🔧 Dalam Proses</option>
                        <option value="selesai" <?= $f['status_maint']==='selesai'?'selected':'' ?>>✅ Selesai</option>
                    </select>
                </td>
                <td><button class="btn btn-primary-sm btn-sm" onclick="updateMaintStatus(<?= $f['id'] ?>)">💾 Update</button></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
async function updateMaintStatus(id){
    const status=document.getElementById('newMaintStatus'+id).value;
    const kondisi=status==='selesai'?'baik':undefined;
    const data={id,status_maint:status};
    if(kondisi)data.kondisi=kondisi;
    const res=await apiFetch('maintenance','edit',data);
    if(res.ok){toast('Status maintenance diperbarui!','success');setTimeout(()=>location.reload(),900);}
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: REQUEST MAINTENANCE (Staff/Karyawan) ═══
elseif ($sub === 'request_maint'): ?>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">🛠️ Ajukan Request Maintenance</div></div>
        <div class="card-body">
            <div class="form-field" style="margin-bottom:14px;"><label>Fasilitas Bermasalah <span style="color:red">*</span></label>
                <select id="reqFasilitas" required>
                    <option value="">-- Pilih Fasilitas --</option>
                    <?php foreach($fasilitas as $f): ?>
                    <option value="<?= htmlspecialchars($f['nama'],ENT_QUOTES) ?>"><?= htmlspecialchars($f['nama']) ?></option>
                    <?php endforeach; ?>
                    <option value="Lainnya">Lainnya (sebutkan di deskripsi)</option>
                </select>
            </div>
            <div class="form-field" style="margin-bottom:14px;"><label>Tingkat Prioritas</label>
                <select id="reqPrioritas">
                    <option value="rendah">🟢 Rendah</option>
                    <option value="normal" selected>🟡 Normal</option>
                    <option value="tinggi">🟠 Tinggi</option>
                    <option value="darurat">🔴 Darurat</option>
                </select>
            </div>
            <div class="form-field" style="margin-bottom:16px;"><label>Deskripsi Masalah <span style="color:red">*</span></label>
                <textarea id="reqMasalah" placeholder="Jelaskan detail masalah yang ditemukan..." required style="min-height:90px;"></textarea>
            </div>
            <button class="btn btn-warning w-full" onclick="ajukanRequest()">📤 Ajukan Request</button>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📋 Request Sebelumnya</div></div>
        <div style="padding:0;">
            <?php foreach($requests as $r):
                $sc=['menunggu'=>['#fff8e1','⏳ Menunggu'],'diproses'=>['#eaf4fb','🔧 Diproses'],'selesai'=>['#eafaf1','✅ Selesai']];
                [$bg,$sl]=$sc[$r['status']]??['#fff','—'];
            ?>
            <div style="padding:14px 20px;border-bottom:1px solid #e8f0eb;background:<?= $bg ?>;">
                <div class="flex justify-between items-center">
                    <strong style="font-size:14px;"><?= htmlspecialchars($r['fasilitas']) ?></strong>
                    <span class="badge badge-secondary" style="font-size:11px;"><?= $sl ?></span>
                </div>
                <p style="font-size:13px;color:#6b8070;margin-top:4px;"><?= htmlspecialchars($r['masalah']) ?></p>
                <span style="font-size:11px;color:#aaa;">📅 <?= $r['tgl_request'] ?> · Prioritas: <?= $r['prioritas'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script>
async function ajukanRequest(){
    const fas=document.getElementById('reqFasilitas').value;
    const masalah=document.getElementById('reqMasalah').value;
    if(!fas||!masalah){toast('Lengkapi semua field yang wajib diisi!','error');return;}
    const res=await apiFetch('request_maint','add',{fasilitas:fas,prioritas:document.getElementById('reqPrioritas').value,masalah});
    if(res.ok){toast('Request maintenance berhasil diajukan!','success');setTimeout(()=>location.href='?mod=maintenance&sub=lacak_request',1200);}
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: LACAK REQUEST (Staff/Karyawan) ═══
elseif ($sub === 'lacak_request'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">🔍 Lacak Status Request Maintenance</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Fasilitas</th><th>Masalah</th><th>Prioritas</th><th>Tgl Request</th><th>Status</th></tr></thead>
            <tbody>
            <?php
            $myReqs = in_array($role,['admin','admin_fasilitas','teknisi','manager']) ? $requests : array_filter($requests, fn($r)=>$r['pemohon']===$user['name']);
            foreach($myReqs as $r):
                $pb=['rendah'=>'badge-success','normal'=>'badge-info','tinggi'=>'badge-warning','darurat'=>'badge-danger'];
                $sb=['menunggu'=>'<span class="badge badge-warning">⏳ Menunggu</span>','diproses'=>'<span class="badge badge-info">🔧 Diproses</span>','selesai'=>'<span class="badge badge-success">✅ Selesai</span>'];
            ?>
            <tr>
                <td><code class="kode"><?= $r['kode'] ?></code></td>
                <td><strong><?= htmlspecialchars($r['fasilitas']) ?></strong></td>
                <td style="font-size:13px;"><?= htmlspecialchars($r['masalah']) ?></td>
                <td><span class="badge <?= $pb[$r['prioritas']]??'badge-secondary' ?>"><?= ucfirst($r['prioritas']) ?></span></td>
                <td>📅 <?= $r['tgl_request'] ?></td>
                <td><?= $sb[$r['status']] ?? '<span class="badge badge-secondary">'.$r['status'].'</span>' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($requests)): ?>
            <tr><td colspan="6" style="text-align:center;padding:30px;color:#6b8070;">Belum ada request yang diajukan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// ═══ SUB: LAPORAN MAINTENANCE ═══
elseif ($sub === 'laporan_maint'): ?>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">📊 Ringkasan Kondisi Fasilitas</div>
            <a href="print.php?type=maintenance" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
        </div>
        <div class="card-body">
            <?php
            $stats=[['Kondisi Baik',$baik,count($fasilitas),'#27ae60'],['Perlu Perbaikan',$perbaikan,count($fasilitas),'#e74c3c'],['Dalam Maintenance',$maint,count($fasilitas),'#f39c12']];
            foreach($stats as $s): $pct=count($fasilitas)?round($s[1]/count($fasilitas)*100):0; ?>
            <div style="margin-bottom:18px;">
                <div class="flex justify-between" style="margin-bottom:6px;">
                    <span style="font-size:14px;font-weight:600;"><?= $s[0] ?></span>
                    <span style="font-size:12px;color:#6b8070;"><?= $s[1] ?>/<?= $s[2] ?> (<?= $pct ?>%)</span>
                </div>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%;background:<?= $s[3] ?>;"></div></div>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:20px;padding:14px;background:#eafaf1;border-radius:8px;text-align:center;">
                <div style="font-size:32px;font-weight:800;color:#27ae60;"><?= $baik ?>/<?= count($fasilitas) ?></div>
                <div style="font-size:13px;color:#6b8070;">Fasilitas dalam kondisi baik</div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📋 Ringkasan Request</div></div>
        <div class="card-body">
            <?php
            $rStats=[['Menunggu',$pending_req,'#f39c12'],['Diproses',count(array_filter($requests,fn($r)=>$r['status']==='diproses')),'#2980b9'],['Selesai',count(array_filter($requests,fn($r)=>$r['status']==='selesai')),'#27ae60']];
            foreach($rStats as $rs): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e8f0eb;">
                <span style="font-size:14px;"><?= $rs[0] ?></span>
                <span style="font-size:24px;font-weight:800;color:<?= $rs[2] ?>;"><?= $rs[1] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php endif; ?>
