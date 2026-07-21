<?php
/**
 * MODUL MANAJEMEN KARYAWAN & HR
 * Diagram hal.4 — AdminHR: Kelola Karyawan | Rekap Absensi | Monitor Aktivitas | Monitor Kinerja
 *                  Karyawan: Lihat/Edit Profil
 */

$karyawans  = $conn->query("SELECT * FROM karyawan")->fetch_all(MYSQLI_ASSOC);
$absenToday = $conn->query("SELECT * FROM absensi")->fetch_all(MYSQLI_ASSOC);
$aktivitas  = $conn->query("SELECT * FROM aktivitas")->fetch_all(MYSQLI_ASSOC);

$hadir  = count(array_filter($absenToday, fn($a)=>$a['status']==='hadir'));
$izin   = count(array_filter($absenToday, fn($a)=>in_array($a['status'],['izin','sakit'])));
$cuti   = count(array_filter($absenToday, fn($a)=>$a['status']==='cuti'));
$aktif  = count(array_filter($karyawans,  fn($k)=>$k['status']==='aktif'));

$sub = $_GET['sub'] ?? '';
if (!$sub) {
    $sub = match($role) {
        'admin','admin_hr' => 'data_karyawan',
        'karyawan'         => 'profil',
        default            => 'data_karyawan',
    };
}
?>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card c-blue"><div class="stat-icon c-blue">👥</div>
        <div class="stat-info"><h3><?= count($karyawans) ?></h3><p>Total Karyawan</p><div class="stat-change up">▲ <?= $aktif ?> aktif</div></div>
    </div>
    <div class="stat-card c-green"><div class="stat-icon c-green">✅</div>
        <div class="stat-info"><h3><?= $hadir ?></h3><p>Hadir Hari Ini</p></div>
    </div>
    <div class="stat-card c-orange"><div class="stat-icon c-orange">🤒</div>
        <div class="stat-info"><h3><?= $izin ?></h3><p>Izin / Sakit</p></div>
    </div>
    <div class="stat-card c-teal"><div class="stat-icon c-teal">🌴</div>
        <div class="stat-info"><h3><?= $cuti ?></h3><p>Cuti</p></div>
    </div>
</div>

<!-- TAB NAV -->
<div class="tab-nav-bar">
<?php if (in_array($role,['admin','admin_hr'])): ?>
    <a href="?mod=karyawan&sub=data_karyawan"        class="tab-link <?= $sub==='data_karyawan'?'active':'' ?>">👥 Kelola Data Karyawan</a>
    <a href="?mod=karyawan&sub=rekap_absensi"        class="tab-link <?= $sub==='rekap_absensi'?'active':'' ?>">📋 Rekap Absensi</a>
    <a href="?mod=karyawan&sub=monitoring_aktivitas" class="tab-link <?= $sub==='monitoring_aktivitas'?'active':'' ?>">📝 Monitoring Aktivitas</a>
    <a href="?mod=karyawan&sub=monitoring_kinerja"   class="tab-link <?= $sub==='monitoring_kinerja'?'active':'' ?>">📊 Monitoring Kinerja</a>
<?php elseif($role==='manager'): ?>
    <a href="?mod=karyawan&sub=data_karyawan"      class="tab-link <?= $sub==='data_karyawan'?'active':'' ?>">👥 Data Karyawan</a>
    <a href="?mod=karyawan&sub=monitoring_kinerja" class="tab-link <?= $sub==='monitoring_kinerja'?'active':'' ?>">📊 Monitoring Kinerja</a>
<?php elseif($role==='karyawan'): ?>
    <a href="?mod=karyawan&sub=profil" class="tab-link <?= $sub==='profil'?'active':'' ?>">👤 Lihat / Edit Profil</a>
<?php endif; ?>
</div>

<?php
// ═══ SUB: KELOLA DATA KARYAWAN ═══
if ($sub === 'data_karyawan'): ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">👥 Data Karyawan</div>
        <div class="flex gap-2">
            <div class="search-input-wrap" style="width:220px;"><span class="search-icon">🔍</span><input type="text" id="tableSearch" placeholder="Cari karyawan..."></div>
            <?php if(in_array($role,['admin','admin_hr'])): ?>
            <button class="btn btn-success" onclick="openModal('modalTambahKaryawan')">➕ Tambah Karyawan</button>
            <a href="print.php?type=karyawan" target="_blank" class="btn btn-outline">🖨️ Cetak</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th data-sort>NIP</th><th data-sort>Nama</th><th>Jabatan</th><th>Unit</th><th>Email</th><th>Tgl Bergabung</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($karyawans as $k):
                $sb = $k['status']==='aktif'?'<span class="badge badge-success">✅ Aktif</span>':'<span class="badge badge-warning">🌴 '.$k['status'].'</span>';
                $init = strtoupper($k['nama'][0]);
                $colors=['#1a6b3c','#2980b9','#9b59b6','#e67e22','#e74c3c','#1abc9c'];
                $color = $colors[$k['id']%count($colors)];
            ?>
            <tr>
                <td><code class="kode"><?= $k['nip'] ?></code></td>
                <td>
                    <div class="flex items-center gap-2">
                        <div style="width:34px;height:34px;border-radius:50%;background:<?= $color ?>;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;flex-shrink:0;"><?= $init ?></div>
                        <strong><?= htmlspecialchars($k['nama']) ?></strong>
                    </div>
                </td>
                <td><?= $k['jabatan'] ?></td>
                <td><span class="badge badge-info"><?= $k['unit'] ?></span></td>
                <td style="font-size:13px;">✉️ <?= $k['email'] ?></td>
                <td><?= $k['tgl_bergabung'] ?></td>
                <td><?= $sb ?></td>
                <td>
                    <div class="flex gap-2">
                        <button class="btn btn-outline btn-sm" onclick="lihatKaryawan('<?= htmlspecialchars(json_encode($k), ENT_QUOTES) ?>')">👁️ Detail</button>
                        <?php if(in_array($role,['admin','admin_hr'])): ?>
                        <button class="btn btn-warning btn-sm" onclick="editKaryawan('<?= htmlspecialchars(json_encode($k), ENT_QUOTES) ?>')">✏️</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div class="modal-overlay" id="modalTambahKaryawan">
    <div class="modal">
        <div class="modal-header"><h3>➕ Tambah Karyawan Baru</h3><button class="modal-close" onclick="closeModal('modalTambahKaryawan')">✕</button></div>
        <div class="modal-body">
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Nama Lengkap <span style="color:red">*</span></label><input type="text" id="addKNama" required></div>
                <div class="form-field"><label>Jabatan</label><input type="text" id="addKJabatan"></div>
            </div>
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Unit Kerja</label>
                    <select id="addKUnit"><option>Tata Usaha</option><option>Keuangan</option><option>Kurikulum</option><option>IT</option><option>Event</option><option>Teknik</option><option>Arsip</option></select>
                </div>
                <div class="form-field"><label>Tanggal Bergabung</label><input type="date" id="addKTgl" value="<?= date('Y-m-d') ?>"></div>
            </div>
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Email</label><input type="email" id="addKEmail"></div>
                <div class="form-field"><label>No. Telepon</label><input type="tel" id="addKTelp"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalTambahKaryawan')">Batal</button>
            <button class="btn btn-success" onclick="simpanKaryawan()">💾 Simpan</button>
        </div>
    </div>
</div>

<!-- Modal Detail Karyawan -->
<div class="modal-overlay" id="modalDetailKaryawan">
    <div class="modal">
        <div class="modal-header"><h3>👤 Detail Karyawan</h3><button class="modal-close" onclick="closeModal('modalDetailKaryawan')">✕</button></div>
        <div class="modal-body" id="detailKaryawanContent"></div>
        <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('modalDetailKaryawan')">Tutup</button></div>
    </div>
</div>

<!-- Modal Edit Karyawan -->
<div class="modal-overlay" id="modalEditKaryawan">
    <div class="modal">
        <div class="modal-header"><h3>✏️ Edit Karyawan</h3><button class="modal-close" onclick="closeModal('modalEditKaryawan')">✕</button></div>
        <div class="modal-body">
            <input type="hidden" id="editKId">
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Nama Lengkap <span style="color:red">*</span></label><input type="text" id="editKNama" required></div>
                <div class="form-field"><label>Jabatan</label><input type="text" id="editKJabatan"></div>
            </div>
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Unit Kerja</label>
                    <select id="editKUnit">
                        <option value="Tata Usaha">Tata Usaha</option>
                        <option value="Keuangan">Keuangan</option>
                        <option value="Kurikulum">Kurikulum</option>
                        <option value="IT">IT</option>
                        <option value="Event">Event</option>
                        <option value="Teknik">Teknik</option>
                        <option value="Arsip">Arsip</option>
                    </select>
                </div>
                <div class="form-field"><label>Status</label>
                    <select id="editKStatus">
                        <option value="aktif">Aktif</option>
                        <option value="cuti">Cuti</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Email</label><input type="email" id="editKEmail"></div>
                <div class="form-field"><label>No. Telepon</label><input type="tel" id="editKTelp"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalEditKaryawan')">Batal</button>
            <button class="btn btn-warning" onclick="updateKaryawan()">💾 Update</button>
        </div>
    </div>
</div>

<script>
function lihatKaryawan(jsonStr){
    const k = JSON.parse(jsonStr.replace(/&quot;/g,'"'));
    const sb = k.status==='aktif'?'<span class="badge badge-success">✅ Aktif</span>':'<span class="badge badge-warning">🌴 '+k.status+'</span>';
    document.getElementById('detailKaryawanContent').innerHTML = `
        <div style="text-align:center;margin-bottom:20px;">
            <div style="width:64px;height:64px;border-radius:50%;background:#1a6b3c;display:flex;align-items:center;justify-content:center;color:white;font-size:26px;font-weight:700;margin:0 auto 10px;">${k.nama[0].toUpperCase()}</div>
            <h3 style="font-size:18px;font-weight:700;">${k.nama}</h3>
            <p style="color:#6b8070;">${k.jabatan} · ${k.unit}</p>
        </div>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <tr><td style="padding:8px 0;color:#6b8070;width:40%;">NIP</td><td><strong>${k.nip}</strong></td></tr>
            <tr><td style="padding:8px 0;color:#6b8070;">Email</td><td>${k.email}</td></tr>
            <tr><td style="padding:8px 0;color:#6b8070;">No. Telp</td><td>${k.telp}</td></tr>
            <tr><td style="padding:8px 0;color:#6b8070;">Tgl Bergabung</td><td>${k.tgl_bergabung}</td></tr>
            <tr><td style="padding:8px 0;color:#6b8070;">Status</td><td>${sb}</td></tr>
        </table>`;
    openModal('modalDetailKaryawan');
}
function editKaryawan(jsonStr){
    const k = JSON.parse(jsonStr.replace(/&quot;/g,'"'));
    document.getElementById('editKId').value = k.id;
    document.getElementById('editKNama').value = k.nama;
    document.getElementById('editKJabatan').value = k.jabatan;
    document.getElementById('editKUnit').value = k.unit;
    document.getElementById('editKStatus').value = k.status;
    document.getElementById('editKEmail').value = k.email;
    document.getElementById('editKTelp').value = k.telp;
    openModal('modalEditKaryawan');
}
async function updateKaryawan(){
    const res = await apiFetch('karyawan','edit',{
        id:document.getElementById('editKId').value,
        nama:document.getElementById('editKNama').value,
        jabatan:document.getElementById('editKJabatan').value,
        unit:document.getElementById('editKUnit').value,
        status:document.getElementById('editKStatus').value,
        email:document.getElementById('editKEmail').value,
        telp:document.getElementById('editKTelp').value,
    });
    if(res.ok){ toast('Karyawan berhasil diupdate!','success'); closeModal('modalEditKaryawan'); setTimeout(()=>location.reload(),900); }
    else toast(res.msg||'Gagal','error');
}
async function simpanKaryawan(){
    const res = await apiFetch('karyawan','add',{
        nama:document.getElementById('addKNama').value,
        jabatan:document.getElementById('addKJabatan').value,
        unit:document.getElementById('addKUnit').value,
        tgl_bergabung:document.getElementById('addKTgl').value,
        email:document.getElementById('addKEmail').value,
        telp:document.getElementById('addKTelp').value,
    });
    if(res.ok){ toast('Karyawan berhasil ditambahkan!','success'); closeModal('modalTambahKaryawan'); setTimeout(()=>location.reload(),900); }
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: REKAP ABSENSI ═══
elseif ($sub === 'rekap_absensi'): ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">📋 Rekap Absensi — <?= date('d F Y') ?></div>
        <a href="print.php?type=absensi" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
    </div>

    <!-- Ring chart mini -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-bottom:1px solid #e8f0eb;">
        <?php
        $stats=[['Hadir',$hadir,'#27ae60'],['Izin/Sakit',$izin,'#f39c12'],['Cuti',$cuti,'#3498db'],['Absen',count($absenToday)-$hadir-$izin-$cuti,'#e74c3c']];
        foreach($stats as $s): ?>
        <div style="text-align:center;padding:20px;border-right:1px solid #e8f0eb;">
            <div style="font-size:32px;font-weight:800;color:<?= $s[2] ?>;"><?= $s[1] ?></div>
            <div style="font-size:13px;color:#6b8070;margin-top:4px;"><?= $s[0] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama Karyawan</th><th>Unit</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Status</th><th>Keterangan</th></tr></thead>
            <tbody>
            <?php foreach($absenToday as $a):
                $sb = match($a['status']) {
                    'hadir' => '<span class="badge badge-success">✅ Hadir</span>',
                    'izin'  => '<span class="badge badge-warning">🤒 Izin/Sakit</span>',
                    'cuti'  => '<span class="badge badge-info">🌴 Cuti</span>',
                    default => '<span class="badge badge-danger">❌ Absen</span>',
                };
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($a['nama']) ?></strong></td>
                <td><span class="badge badge-secondary"><?= $a['unit'] ?></span></td>
                <td><?= $a['masuk'] ?: '<span class="text-muted">—</span>' ?></td>
                <td><?= $a['keluar'] ?: '<span class="text-muted">—</span>' ?></td>
                <td><?= $sb ?></td>
                <td><span class="text-sm text-muted"><?= $a['keterangan'] ?: '—' ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// ═══ SUB: MONITORING AKTIVITAS ═══
elseif ($sub === 'monitoring_aktivitas'): ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">📝 Monitoring Aktivitas Karyawan</div>
        <div class="search-input-wrap" style="width:220px;"><span class="search-icon">🔍</span><input type="text" id="tableSearch" placeholder="Cari aktivitas..."></div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Tanggal</th><th>Jenis</th><th>Deskripsi Aktivitas</th></tr></thead>
            <tbody>
            <?php foreach($aktivitas as $a): ?>
            <tr>
                <td><strong><?= htmlspecialchars($a['nama']) ?></strong></td>
                <td>📅 <?= $a['tanggal'] ?></td>
                <td><span class="badge badge-info"><?= $a['jenis'] ?></span></td>
                <td><?= htmlspecialchars($a['deskripsi']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($aktivitas)): ?>
            <tr><td colspan="4" style="text-align:center;padding:30px;color:#6b8070;">Belum ada aktivitas tercatat.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// ═══ SUB: MONITORING KINERJA ═══
elseif ($sub === 'monitoring_kinerja'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">📊 Monitoring Kinerja Karyawan</div>
        <a href="print.php?type=karyawan" target="_blank" class="btn btn-outline btn-sm">🖨️ Laporan</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Karyawan</th><th>Unit</th><th>Kehadiran</th><th>Aktivitas</th><th>Ketepatan</th><th>Skor</th><th>Grade</th></tr></thead>
            <tbody>
            <?php
            $aktivitas_all = $conn->query("SELECT nama, COUNT(*) as jml FROM aktivitas WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE()) GROUP BY nama")->fetch_all(MYSQLI_ASSOC);
            $akt_map = [];
            foreach($aktivitas_all as $a) $akt_map[$a['nama']] = $a['jml'];

            foreach($karyawans as $k):
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
                <td>
                    <div class="flex items-center gap-2"><?= $kehadiran ?>%
                        <div class="progress-bar" style="width:60px;margin:0;"><div class="progress-fill" style="width:<?= $kehadiran ?>%;background:#27ae60;"></div></div>
                    </div>
                </td>
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
// ═══ SUB: PROFIL KARYAWAN ═══
elseif ($sub === 'profil'): ?>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">👤 Data Profil Saya</div></div>
        <div class="card-body">
            <div style="text-align:center;margin-bottom:24px;">
                <div style="width:72px;height:72px;border-radius:50%;background:<?= $roleColor ?>;display:flex;align-items:center;justify-content:center;color:white;font-size:30px;font-weight:700;margin:0 auto 12px;"><?= $userInitial ?></div>
                <h3 style="font-size:18px;font-weight:700;"><?= htmlspecialchars($user['name']) ?></h3>
                <p style="color:#6b8070;"><?= $roleName ?></p>
            </div>
            <form>
                <div class="form-field" style="margin-bottom:14px;"><label>Nama Lengkap</label><input type="text" value="<?= htmlspecialchars($user['name']) ?>"></div>
                <div class="form-field" style="margin-bottom:14px;"><label>Email</label><input type="email" value="<?= htmlspecialchars($user['email']) ?>"></div>
                <div class="form-field" style="margin-bottom:16px;"><label>Unit Kerja</label><input type="text" value="<?= htmlspecialchars($user['unit']??'') ?>"></div>
                <button type="button" class="btn btn-success w-full" onclick="toast('Profil berhasil diperbarui!','success')">💾 Simpan Perubahan</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">🔐 Ubah Password</div></div>
        <div class="card-body">
            <div class="form-field" style="margin-bottom:14px;"><label>Password Lama</label><input type="password" placeholder="••••••••"></div>
            <div class="form-field" style="margin-bottom:14px;"><label>Password Baru</label><input type="password" placeholder="Min. 6 karakter"></div>
            <div class="form-field" style="margin-bottom:16px;"><label>Konfirmasi Password</label><input type="password" placeholder="Ulangi password baru"></div>
            <button type="button" class="btn btn-warning w-full" onclick="toast('Password berhasil diubah!','success')">🔑 Ganti Password</button>
        </div>
    </div>
</div>

<?php endif; ?>
