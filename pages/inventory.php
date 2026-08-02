<?php
/**
 * MODUL INVENTARIS & PEMINJAMAN BARANG
 * Diagram hal.8 — Admin: Kelola Data Barang | Persetujuan Peminjaman | Laporan
 *                  Staff: Pinjam Barang | Kembalikan Barang | Lihat Riwayat
 */

// Load data dari JSON
$items    = $conn->query("SELECT * FROM inventory")->fetch_all(MYSQLI_ASSOC);
$loans    = $conn->query("SELECT * FROM peminjaman ORDER BY CAST(SUBSTRING(kode, 5) AS UNSIGNED) ASC")->fetch_all(MYSQLI_ASSOC);
usort($loans, function($a, $b) { return (int)substr($a["kode"], 4) - (int)substr($b["kode"], 4); });
$pending  = array_filter($loans, fn($l) => $l['status'] === 'pending');
$dipinjam = array_filter($loans, fn($l) => $l['status'] === 'dipinjam');
$tersedia = array_filter($items, fn($i) => $i['status'] === 'tersedia');

$sub = $_GET['sub'] ?? '';
// Default sub per role
if (!$sub) {
    $sub = match($role) {
        'admin','admin_fasilitas' => 'barang',
        'manager'                 => 'approval',
        default                   => 'pinjam',
    };
}
?>

<!-- STAT CARDS -->
<div class="stats-grid">
    <div class="stat-card c-blue">
        <div class="stat-icon c-blue">📦</div>
        <div class="stat-info">
            <h3><?= count($items) ?></h3><p>Total Barang</p>
            <div class="stat-change up">▲ <?= count($tersedia) ?> tersedia</div>
        </div>
    </div>
    <div class="stat-card c-orange">
        <div class="stat-icon c-orange">🔄</div>
        <div class="stat-info">
            <h3><?= count($dipinjam) ?></h3><p>Sedang Dipinjam</p>
        </div>
    </div>
    <div class="stat-card c-red">
        <div class="stat-icon c-red">⏳</div>
        <div class="stat-info">
            <h3><?= count($pending) ?></h3><p>Menunggu Approval</p>
            <?php if(count($pending)>0): ?>
            <div class="stat-change down">▲ Segera diproses</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="stat-card c-green">
        <div class="stat-icon c-green">✅</div>
        <div class="stat-info">
            <h3><?= count(array_filter($loans, fn($l)=>$l['status']==='dikembalikan')) ?></h3><p>Sudah Dikembalikan</p>
        </div>
    </div>
</div>

<!-- TAB NAV -->
<div class="tab-nav-bar">
<?php if (in_array($role,['admin','admin_fasilitas'])): ?>
    <a href="?mod=inventory&sub=barang"     class="tab-link <?= $sub==='barang'?'active':'' ?>">📦 Kelola Data Barang</a>
    <a href="?mod=inventory&sub=approval"   class="tab-link <?= $sub==='approval'?'active':'' ?>">
        ✅ Persetujuan Peminjaman <?php if(count($pending)): ?><span class="tab-badge"><?= count($pending) ?></span><?php endif; ?>
    </a>
<?php if ($role === 'admin'): ?>
    <a href="?mod=inventory&sub=kembali"  class="tab-link <?= $sub==='kembali'?'active':'' ?>">📥 Kembalikan Barang</a>
<?php endif; ?>
    <a href="?mod=inventory&sub=riwayat"    class="tab-link <?= $sub==='riwayat'?'active':'' ?>">📋 Data Pengembalian</a>
    <a href="?mod=inventory&sub=laporan_inv" class="tab-link <?= $sub==='laporan_inv'?'active':'' ?>">📄 Laporan Inventaris</a>
<?php elseif ($role==='manager'): ?>
    <a href="?mod=inventory&sub=approval"    class="tab-link <?= $sub==='approval'?'active':'' ?>">
        ✅ Approval Peminjaman <?php if(count($pending)): ?><span class="tab-badge"><?= count($pending) ?></span><?php endif; ?>
    </a>
    <a href="?mod=inventory&sub=laporan_inv" class="tab-link <?= $sub==='laporan_inv'?'active':'' ?>">📄 Laporan Inventaris</a>
<?php else: // staff ?>
    <a href="?mod=inventory&sub=pinjam"   class="tab-link <?= $sub==='pinjam'?'active':'' ?>">📤 Pinjam Barang</a>
    <a href="?mod=inventory&sub=riwayat"  class="tab-link <?= $sub==='riwayat'?'active':'' ?>">📋 Lihat Riwayat Peminjaman</a>
<?php endif; ?>
</div>

<?php

// ═══ SUB: KELOLA DATA BARANG (Admin) ═══
if ($sub === 'barang'): ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">📦 Data Inventaris Barang</div>
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <div class="search-input-wrap" style="flex: 1; min-width: 130px; max-width: 220px;">
                <span class="search-icon">🔍</span>
                <input type="text" id="tableSearch" placeholder="Cari barang..." style="width: 100%;">
            </div>
            <button class="btn btn-success" onclick="openModal('modalTambahBarang')">➕ Tambah Barang</button>
            <a href="print.php?type=inventory" target="_blank" class="btn btn-outline">🖨️ Cetak</a>
        </div>
    </div>
    <div class="table-wrap">
        <table id="tblBarang">
            <thead>
                <tr><th data-sort>Kode</th><th data-sort>Nama Barang</th><th>Kategori</th><th>Lokasi</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach($items as $item):
                $stBadge = match($item['status']) {
                    'tersedia'    => '<span class="badge badge-success">✅ Tersedia</span>',
                    'dipinjam'    => '<span class="badge badge-warning">🔄 Dipinjam</span>',
                    'maintenance' => '<span class="badge badge-danger">🔧 Maintenance</span>',
                    default       => '<span class="badge badge-secondary">—</span>',
                };
            ?>
            <tr>
                <td><code class="kode"><?= $item['kode'] ?></code></td>
                <td><strong><?= htmlspecialchars($item['nama']) ?></strong><br><span class="text-xs text-muted"><?= htmlspecialchars($item['keterangan']) ?></span></td>
                <td><span class="badge badge-info"><?= $item['kategori'] ?></span></td>
                <td>📍 <?= $item['lokasi'] ?></td>
                <td><strong style="font-size:16px;"><?= $item['stok'] ?></strong></td>
                <td><?= $stBadge ?></td>
                <td>
                    <button class="btn btn-outline btn-sm" onclick="editBarang(<?= $item['id'] ?>,'<?= htmlspecialchars($item['nama'],ENT_QUOTES) ?>','<?= $item['kategori'] ?>','<?= $item['lokasi'] ?>',<?= $item['stok'] ?>,'<?= $item['status'] ?>','<?= htmlspecialchars($item['keterangan'],ENT_QUOTES) ?>')">✏️ Edit</button>
                    <?php if($role==='admin'): ?>
                    <button class="btn btn-danger btn-sm" onclick="hapusBarang(<?= $item['id'] ?>)">🗑️</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Barang -->
<div class="modal-overlay" id="modalTambahBarang">
    <div class="modal">
        <div class="modal-header"><h3>➕ Tambah Barang Baru</h3><button class="modal-close" onclick="closeModal('modalTambahBarang')">✕</button></div>
        <div class="modal-body">
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Nama Barang <span style="color:red">*</span></label><input type="text" id="addNama" placeholder="Nama lengkap barang" required></div>
                <div class="form-field"><label>Kategori</label>
                    <select id="addKat"><option>Elektronik</option><option>Furnitur</option><option>ATK</option><option>Audio</option><option>Kamera</option><option>Mesin</option><option>Lainnya</option></select>
                </div>
            </div>
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Lokasi</label><input type="text" id="addLokasi" placeholder="Misal: Lab Komputer"></div>
                <div class="form-field"><label>Jumlah Stok <span style="color:red">*</span></label><input type="number" id="addStok" min="1" value="1"></div>
            </div>
            <div class="form-field"><label>Keterangan</label><textarea id="addKet" placeholder="Kondisi, catatan, dll..." style="min-height:70px;"></textarea></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalTambahBarang')">Batal</button>
            <button class="btn btn-success" onclick="simpanBarang()">💾 Simpan</button>
        </div>
    </div>
</div>

<!-- Modal Edit Barang -->
<div class="modal-overlay" id="modalEditBarang">
    <div class="modal">
        <div class="modal-header"><h3>✏️ Edit Data Barang</h3><button class="modal-close" onclick="closeModal('modalEditBarang')">✕</button></div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Nama Barang</label><input type="text" id="editNama"></div>
                <div class="form-field"><label>Kategori</label>
                    <select id="editKat"><option>Elektronik</option><option>Furnitur</option><option>ATK</option><option>Audio</option><option>Kamera</option><option>Mesin</option><option>Lainnya</option></select>
                </div>
            </div>
            <div class="field-group cols-3" style="margin-bottom:14px;">
                <div class="form-field"><label>Lokasi</label><input type="text" id="editLokasi"></div>
                <div class="form-field"><label>Stok</label><input type="number" id="editStok" min="0"></div>
                <div class="form-field"><label>Status</label>
                    <select id="editStatus"><option value="tersedia">Tersedia</option><option value="dipinjam">Dipinjam</option><option value="maintenance">Maintenance</option></select>
                </div>
            </div>
            <div class="form-field"><label>Keterangan</label><textarea id="editKet" style="min-height:70px;"></textarea></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalEditBarang')">Batal</button>
            <button class="btn btn-primary-sm" onclick="updateBarang()">💾 Simpan Perubahan</button>
        </div>
    </div>
</div>

<script>
function editBarang(id,nama,kat,lokasi,stok,status,ket){
    document.getElementById('editId').value=id;
    document.getElementById('editNama').value=nama;
    document.getElementById('editKat').value=kat;
    document.getElementById('editLokasi').value=lokasi;
    document.getElementById('editStok').value=stok;
    document.getElementById('editStatus').value=status;
    document.getElementById('editKet').value=ket;
    openModal('modalEditBarang');
}
async function simpanBarang(){
    const res = await apiFetch('inventory','add',{
        nama:document.getElementById('addNama').value,
        kategori:document.getElementById('addKat').value,
        lokasi:document.getElementById('addLokasi').value,
        stok:document.getElementById('addStok').value,
        keterangan:document.getElementById('addKet').value,
    });
    if(res.ok){ toast('Barang berhasil ditambahkan!','success'); closeModal('modalTambahBarang'); setTimeout(()=>location.reload(),1000); }
    else toast(res.msg||'Gagal menyimpan','error');
}
async function updateBarang(){
    const res = await apiFetch('inventory','edit',{
        id:document.getElementById('editId').value,
        nama:document.getElementById('editNama').value,
        kategori:document.getElementById('editKat').value,
        lokasi:document.getElementById('editLokasi').value,
        stok:document.getElementById('editStok').value,
        status:document.getElementById('editStatus').value,
        keterangan:document.getElementById('editKet').value,
    });
    if(res.ok){ toast('Data berhasil diperbarui!','success'); closeModal('modalEditBarang'); setTimeout(()=>location.reload(),1000); }
    else toast(res.msg||'Gagal','error');
}
async function hapusBarang(id){
    let ok = false;
    if (typeof customConfirm === 'function') {
        ok = await customConfirm('Hapus barang ini? Data tidak bisa dikembalikan.', 'Ya, Hapus', 'btn-danger', '🗑️');
    } else {
        ok = confirm('Hapus barang ini? Data tidak bisa dikembalikan.');
    }
    if(!ok) return;
    const res = await apiFetch('inventory','delete',{id});
    if(res.ok){ toast('Barang dihapus','success'); setTimeout(()=>location.reload(),800); }
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: PERSETUJUAN PEMINJAMAN ═══
elseif ($sub === 'approval'): ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">⏳ Persetujuan Peminjaman
            <?php if(count($pending)>0): ?><span class="badge badge-danger" style="margin-left:8px;"><?= count($pending) ?> Pending</span><?php endif; ?>
        </div>
        <a href="print.php?type=peminjaman" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak Laporan</a>
    </div>

    <?php if(count($pending) > 0): ?>
    <div style="background:#fff8e1;border-left:4px solid #f39c12;padding:14px 20px;font-size:14px;display:flex;align-items:center;gap:10px;">
        ⚠️ <strong><?= count($pending) ?> permintaan peminjaman</strong> menunggu persetujuan Anda.
    </div>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Peminjam</th><th>Barang</th><th>Jml</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Keperluan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($loans as $l):
                $sb = match($l['status']) {
                    'pending'       => '<span class="badge badge-warning">⏳ Pending</span>',
                    'dipinjam'      => '<span class="badge badge-info">🔄 Dipinjam</span>',
                    'dikembalikan'  => '<span class="badge badge-success">✅ Kembali</span>',
                    default         => '<span class="badge badge-secondary">'.$l['status'].'</span>',
                };
            ?>
            <tr>
                <td><code class="kode"><?= $l['kode'] ?></code></td>
                <td><strong><?= htmlspecialchars($l['peminjam']) ?></strong><br><span class="text-xs text-muted"><?= $l['unit'] ?></span></td>
                <td><?= htmlspecialchars($l['barang']) ?></td>
                <td><?= $l['jumlah'] ?></td>
                <td><?= $l['tgl_pinjam'] ?></td>
                <td><?= $l['tgl_kembali'] ?></td>
                <td><span class="text-sm"><?= htmlspecialchars($l['keperluan']) ?></span></td>
                <td><?= $sb ?></td>
                <td>
                    <?php if($l['status']==='pending'): ?>
                    <div class="flex gap-2">
                        <button class="btn btn-success btn-sm" onclick="approvePJM(<?= $l['id'] ?>,'dipinjam')">✅ Setuju</button>
                        <button class="btn btn-danger btn-sm" onclick="approvePJM(<?= $l['id'] ?>,'ditolak')">❌ Tolak</button>
                    </div>
                    <?php elseif($l['status']==='dipinjam'): ?>
                    <button class="btn btn-outline btn-sm" onclick="approvePJM(<?= $l['id'] ?>,'dikembalikan')">📥 Tandai Kembali</button>
                    <?php else: ?>
                    <span class="text-muted text-sm">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
async function approvePJM(id, status) {
    const labels = {dipinjam:'disetujui & dipinjam', ditolak:'ditolak', dikembalikan:'ditandai dikembalikan'};
    const btnText = status === 'dipinjam' ? 'Ya, Setuju' : (status === 'ditolak' ? 'Ya, Tolak' : 'Ya, Kembalikan');
    const btnClass = status === 'dipinjam' ? 'btn-success' : (status === 'ditolak' ? 'btn-danger' : 'btn-primary');
    const icon = status === 'dipinjam' ? '🤝' : (status === 'ditolak' ? '⚠️' : '📥');
    
    let ok = false;
    if (typeof customConfirm === 'function') {
        ok = await customConfirm(`Permintaan akan ${labels[status]}?`, btnText, btnClass, icon);
    } else {
        ok = confirm(`Permintaan akan ${labels[status]}?`);
    }
    
    if(!ok) return;
    
    const res = await apiFetch('peminjaman','approve',{id,status});
    if(res.ok){ toast(`Peminjaman berhasil ${labels[status]}!`,'success'); setTimeout(()=>location.reload(),900); }
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: PINJAM BARANG (Staff) ═══
elseif ($sub === 'pinjam'): ?>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">📤 Form Peminjaman Barang</div></div>
        <div class="card-body">
            <div class="form-field" style="margin-bottom:14px;">
                <label>Peminjam</label>
                <input type="text" value="<?= htmlspecialchars($user['name']) ?>" readonly style="background:#f0f5f2;">
            </div>
            <div class="form-field" style="margin-bottom:14px;">
                <label>Pilih Barang <span style="color:red">*</span></label>
                <select id="pjmBarang" required>
                    <option value="">-- Pilih Barang --</option>
                    <?php foreach($items as $it): if($it['status']==='tersedia'): ?>
                    <option value="<?= $it['id'] ?>" data-nama="<?= htmlspecialchars($it['nama'],ENT_QUOTES) ?>"><?= htmlspecialchars($it['nama']) ?> (Stok: <?= $it['stok'] ?>)</option>
                    <?php endif; endforeach; ?>
                </select>
            </div>
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field">
                    <label>Jumlah</label>
                    <input type="number" id="pjmJumlah" min="1" value="1">
                </div>
                <div class="form-field">
                    <label>Tanggal Kembali <span style="color:red">*</span></label>
                    <input type="date" id="pjmKembali" min="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="form-field" style="margin-bottom:16px;">
                <label>Keperluan <span style="color:red">*</span></label>
                <textarea id="pjmKeperluan" placeholder="Jelaskan keperluan peminjaman..." required style="min-height:80px;"></textarea>
            </div>
            <button class="btn btn-success w-full" onclick="ajukanPinjam()">📤 Ajukan Peminjaman</button>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📋 Barang Tersedia</div></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama Barang</th><th>Kategori</th><th>Stok</th></tr></thead>
                <tbody>
                <?php foreach($items as $it): if($it['status']==='tersedia'): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($it['nama']) ?></strong><br><span class="text-xs text-muted">📍 <?= $it['lokasi'] ?></span></td>
                    <td><span class="badge badge-info"><?= $it['kategori'] ?></span></td>
                    <td><strong style="color:#27ae60;"><?= $it['stok'] ?></strong></td>
                </tr>
                <?php endif; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
async function ajukanPinjam(){
    const el = document.getElementById('pjmBarang');
    const inv_id = el.value;
    const barang = el.options[el.selectedIndex]?.dataset?.nama || '';
    const jumlah = document.getElementById('pjmJumlah').value;
    const kembali = document.getElementById('pjmKembali').value;
    const keperluan = document.getElementById('pjmKeperluan').value;
    if(!inv_id||!kembali||!keperluan){ toast('Lengkapi semua field yang wajib diisi!','error'); return; }
    const res = await apiFetch('peminjaman','add',{inv_id,barang,jumlah,tgl_kembali:kembali,keperluan});
    if(res.ok){ toast('Permintaan peminjaman berhasil diajukan! Menunggu persetujuan.','success'); setTimeout(()=>location.href='?mod=inventory&sub=riwayat',1200); }
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: KEMBALIKAN BARANG (Admin Only) ═══
elseif ($sub === 'kembali' && $role === 'admin'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">📥 Kembalikan Barang</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Barang</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php
            $myLoans = array_filter($loans, fn($l)=>in_array($l['status'],['dipinjam','pending']));
            foreach($myLoans as $l):
                $sb = $l['status']==='dipinjam'?'<span class="badge badge-warning">🔄 Dipinjam</span>':'<span class="badge badge-info">⏳ Pending</span>';
            ?>
            <tr>
                <td><code class="kode"><?= $l['kode'] ?></code></td>
                <td><strong><?= htmlspecialchars($l['barang']) ?></strong></td>
                <td><?= $l['tgl_pinjam'] ?></td>
                <td><?= $l['tgl_kembali'] ?><?php if(strtotime($l['tgl_kembali'])<time()&&$l['status']==='dipinjam'): ?> <span class="badge badge-danger" style="margin-left:4px;">Terlambat</span><?php endif; ?></td>
                <td><?= $sb ?></td>
                <td>
                    <?php if($l['status']==='dipinjam'): ?>
                    <button class="btn btn-success btn-sm" onclick="kembalikanBarang(<?= $l['id'] ?>)">📥 Kembalikan</button>
                    <?php else: ?>
                    <span class="text-muted text-sm">Menunggu persetujuan</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($myLoans)): ?>
            <tr><td colspan="6" style="text-align:center;padding:30px;color:#6b8070;">Tidak ada barang yang sedang dipinjam.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
async function kembalikanBarang(id){
    let ok = false;
    if (typeof customConfirm === 'function') {
        ok = await customConfirm('Konfirmasi pengembalian barang ini?', 'Ya, Kembalikan', 'btn-success', '📥');
    } else {
        ok = confirm('Konfirmasi pengembalian barang ini?');
    }
    if(!ok) return;
    const res = await apiFetch('peminjaman','approve',{id,status:'dikembalikan'});
    if(res.ok){ toast('Barang berhasil dikembalikan!','success'); setTimeout(()=>location.reload(),900); }
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: RIWAYAT (Staff) ═══
elseif ($sub === 'riwayat'): ?>
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 10px;">
        <div class="card-title">📋 Riwayat Peminjaman</div>
        <div class="search-input-wrap" style="flex: 1; min-width: 130px; max-width: 220px;">
            <span class="search-icon">🔍</span>
            <input type="text" id="tableSearch" placeholder="Cari..." style="width: 100%;">
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Peminjam</th><th>Barang</th><th>Jml</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th></tr></thead>
            <tbody id="riwayatTbody">
            <?php foreach($loans as $l):
                if (in_array($role, ['admin', 'admin_fasilitas']) && $l['status'] === 'ditolak') continue;
                $sb = match($l['status']) {
                    'pending'       => '<span class="badge badge-warning">⏳ Pending</span>',
                    'dipinjam'      => '<span class="badge badge-info">🔄 Dipinjam</span>',
                    'dikembalikan'  => '<span class="badge badge-success">✅ Dikembalikan</span>',
                    'ditolak'       => '<span class="badge badge-danger">❌ Ditolak</span>',
                    default         => '<span class="badge badge-secondary">'.$l['status'].'</span>',
                };
            ?>
            <tr>
                <td><code class="kode"><?= $l['kode'] ?></code></td>
                <td><strong><?= htmlspecialchars($l['peminjam']) ?></strong><br><span class="text-xs text-muted"><?= $l['unit'] ?></span></td>
                <td><?= htmlspecialchars($l['barang']) ?></td>
                <td><?= $l['jumlah'] ?></td>
                <td><?= $l['tgl_pinjam'] ?></td>
                <td><?= $l['tgl_kembali'] ?></td>
                <td><?= $sb ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>



<?php
// ═══ SUB: LAPORAN INVENTARIS ═══
elseif ($sub === 'laporan_inv'): ?>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">📊 Ringkasan Inventaris</div></div>
        <div class="card-body">
            <?php
            $byKat = [];
            foreach($items as $it) $byKat[$it['kategori']] = ($byKat[$it['kategori']] ?? 0) + 1;
            arsort($byKat);
            foreach($byKat as $kat=>$jml):
                $pct = round($jml/count($items)*100);
            ?>
            <div style="margin-bottom:16px;">
                <div class="flex justify-between" style="margin-bottom:5px;">
                    <span style="font-size:14px;font-weight:600;"><?= $kat ?></span>
                    <span style="font-size:12px;color:#6b8070;"><?= $jml ?> barang (<?= $pct ?>%)</span>
                </div>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%;background:#1a6b3c;"></div></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📤 Generate Laporan</div></div>
        <div class="card-body">
            <?php
            $lapTypes=[['inventory','📦','Laporan Data Barang'],['peminjaman','🔄','Laporan Peminjaman'],['karyawan','👥','Laporan Karyawan']];
            foreach($lapTypes as $lt): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-bottom:1px solid #e8f0eb;">
                <span style="font-size:18px;margin-right:10px;"><?= $lt[1] ?></span>
                <span style="flex:1;font-size:14px;font-weight:600;"><?= $lt[2] ?></span>
                <a href="print.php?type=<?= $lt[0] ?>" target="_blank" class="btn btn-primary-sm btn-sm">🖨️ Cetak PDF</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php endif; ?>
