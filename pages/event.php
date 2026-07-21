<?php
/**
 * MODUL EVENT MANAGEMENT SYSTEM
 * Diagram hal.7 — EO: Buat|Kelola|Assign|Update Status|Laporan
 *                  Admin: Approval|Monitoring|Daftar
 *                  Staff: Event Saya|Reminder
 */

$events = $conn->query("SELECT * FROM event")->fetch_all(MYSQLI_ASSOC);
$persiapan   = array_filter($events, fn($e)=>$e['status']==='persiapan');
$berlangsung = array_filter($events, fn($e)=>$e['status']==='berlangsung');
$selesai     = array_filter($events, fn($e)=>$e['status']==='selesai');

$sub = $_GET['sub'] ?? '';
if (!$sub) {
    $sub = match($role) {
        'admin'   => 'approval_event',
        'eo'      => 'buat_event',
        'manager' => 'daftar_event',
        'staff'   => 'event_saya',
        default   => 'daftar_event',
    };
}

function eventBadge($status) {
    return match($status) {
        'persiapan'   => '<span class="badge badge-warning">⏳ Persiapan</span>',
        'berlangsung' => '<span class="badge badge-primary">🔄 Berlangsung</span>',
        'selesai'     => '<span class="badge badge-success">✅ Selesai</span>',
        default       => '<span class="badge badge-secondary">'.$status.'</span>',
    };
}
?>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card c-purple"><div class="stat-icon c-purple">🎪</div>
        <div class="stat-info"><h3><?= count($events) ?></h3><p>Total Event</p></div>
    </div>
    <div class="stat-card c-orange"><div class="stat-icon c-orange">⏳</div>
        <div class="stat-info"><h3><?= count($persiapan) ?></h3><p>Persiapan</p></div>
    </div>
    <div class="stat-card c-blue"><div class="stat-icon c-blue">🔄</div>
        <div class="stat-info"><h3><?= count($berlangsung) ?></h3><p>Sedang Berlangsung</p></div>
    </div>
    <div class="stat-card c-green"><div class="stat-icon c-green">✅</div>
        <div class="stat-info"><h3><?= count($selesai) ?></h3><p>Selesai</p></div>
    </div>
</div>

<!-- TAB NAV -->
<div class="tab-nav-bar">
<?php if ($role === 'admin'): ?>
    <a href="?mod=event&sub=approval_event"   class="tab-link <?= $sub==='approval_event'?'active':'' ?>">✅ Approval Event</a>
    <a href="?mod=event&sub=monitoring_event" class="tab-link <?= $sub==='monitoring_event'?'active':'' ?>">📡 Monitoring Event</a>
    <a href="?mod=event&sub=daftar_event"     class="tab-link <?= $sub==='daftar_event'?'active':'' ?>">📋 Daftar Event</a>
<?php elseif ($role === 'eo'): ?>
    <a href="?mod=event&sub=buat_event"    class="tab-link <?= $sub==='buat_event'?'active':'' ?>">➕ Buat Event</a>
    <a href="?mod=event&sub=kelola_event"  class="tab-link <?= $sub==='kelola_event'?'active':'' ?>">✏️ Kelola / Edit Event</a>
    <a href="?mod=event&sub=assign"        class="tab-link <?= $sub==='assign'?'active':'' ?>">👥 Assign Personel &amp; Fasilitas</a>
    <a href="?mod=event&sub=update_status" class="tab-link <?= $sub==='update_status'?'active':'' ?>">🔄 Update Status Event</a>
    <a href="?mod=laporan&sub=laporan_event" class="tab-link">📊 Lihat Laporan</a>
<?php elseif ($role === 'manager'): ?>
    <a href="?mod=event&sub=daftar_event"    class="tab-link <?= $sub==='daftar_event'?'active':'' ?>">📋 Daftar Event</a>
    <a href="?mod=event&sub=evaluasi_event"  class="tab-link <?= $sub==='evaluasi_event'?'active':'' ?>">🏆 Evaluasi Event</a>
    <a href="?mod=laporan&sub=laporan_event" class="tab-link">📊 Laporan Event</a>
<?php elseif ($role === 'staff'): ?>
    <a href="?mod=event&sub=event_saya" class="tab-link <?= $sub==='event_saya'?'active':'' ?>">🎫 Lihat Event Saya</a>
    <a href="?mod=event&sub=reminder"   class="tab-link <?= $sub==='reminder'?'active':'' ?>">🔔 Lihat Reminder</a>
<?php endif; ?>
</div>

<?php
// ═══ SUB: BUAT EVENT (EO) ═══
if ($sub === 'buat_event'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">➕ Buat Event Baru</div></div>
    <div class="card-body" style="max-width:700px;">
        <div class="field-group cols-2" style="margin-bottom:14px;">
            <div class="form-field"><label>Nama Event <span style="color:red">*</span></label><input type="text" id="evNama" placeholder="Nama kegiatan" required></div>
            <div class="form-field"><label>Jenis Event</label>
                <select id="evJenis"><option>Akademik</option><option>Non-Akademik</option><option>Internal</option><option>Eksternal</option></select>
            </div>
        </div>
        <div class="field-group cols-3" style="margin-bottom:14px;">
            <div class="form-field"><label>Tanggal Mulai <span style="color:red">*</span></label><input type="date" id="evMulai" required></div>
            <div class="form-field"><label>Tanggal Selesai</label><input type="date" id="evSelesai"></div>
            <div class="form-field"><label>Lokasi</label><input type="text" id="evLokasi" placeholder="Aula Utama, dll"></div>
        </div>
        <div class="field-group cols-2" style="margin-bottom:14px;">
            <div class="form-field"><label>Jumlah Personel</label><input type="number" id="evPersonel" min="1" value="10"></div>
            <div class="form-field"><label>Fasilitas yang Dibutuhkan</label><input type="text" id="evFasilitas" placeholder="Proyektor, sound, meja..."></div>
        </div>
        <div class="form-field" style="margin-bottom:20px;"><label>Deskripsi Kegiatan</label>
            <textarea id="evDeskripsi" placeholder="Jelaskan detail, tujuan, dan target kegiatan..." style="min-height:90px;"></textarea>
        </div>
        <div class="flex gap-3">
            <button class="btn btn-success" onclick="buatEvent()">🎪 Buat Event</button>
            <a href="?mod=event&sub=kelola_event" class="btn btn-outline">📋 Lihat Daftar Event</a>
        </div>
    </div>
</div>
<script>
async function buatEvent(){
    const nama = document.getElementById('evNama').value;
    const mulai = document.getElementById('evMulai').value;
    if(!nama||!mulai){ toast('Nama dan tanggal mulai wajib diisi!','error'); return; }
    const res = await apiFetch('event','add',{
        nama, jenis:document.getElementById('evJenis').value,
        tgl_mulai:mulai, tgl_selesai:document.getElementById('evSelesai').value,
        lokasi:document.getElementById('evLokasi').value,
        personel:document.getElementById('evPersonel').value,
        fasilitas:document.getElementById('evFasilitas').value,
        deskripsi:document.getElementById('evDeskripsi').value,
    });
    if(res.ok){ toast('Event berhasil dibuat! Menunggu approval.','success'); setTimeout(()=>location.href='?mod=event&sub=kelola_event',1200); }
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: KELOLA EVENT / DAFTAR (EO, Admin, Manager) ═══
elseif (in_array($sub,['kelola_event','daftar_event','monitoring_event'])): ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <?= $sub==='monitoring_event'?'📡 Monitoring Event':($sub==='kelola_event'?'✏️ Kelola Event':'📋 Daftar Event') ?>
        </div>
        <div class="flex gap-2">
            <div class="search-input-wrap" style="width:200px;"><span class="search-icon">🔍</span><input type="text" id="tableSearch" placeholder="Cari event..."></div>
            <a href="print.php?type=event" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
            <?php if($role==='eo'): ?><a href="?mod=event&sub=buat_event" class="btn btn-success btn-sm">➕ Buat Baru</a><?php endif; ?>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th data-sort>Kode</th><th data-sort>Nama Event</th><th>Jenis</th><th>Tanggal</th><th>Lokasi</th><th>Personel</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($events as $ev): ?>
            <tr>
                <td><code class="kode"><?= $ev['kode'] ?></code></td>
                <td><strong><?= htmlspecialchars($ev['nama']) ?></strong><br><span class="text-xs text-muted"><?= htmlspecialchars($ev['deskripsi']) ?></span></td>
                <td><span class="badge badge-info"><?= $ev['jenis'] ?></span></td>
                <td>📅 <?= date('d M Y',strtotime($ev['tgl_mulai'])) ?><?= $ev['tgl_selesai']!==$ev['tgl_mulai']?' – '.date('d M Y',strtotime($ev['tgl_selesai'])):'' ?></td>
                <td>📍 <?= htmlspecialchars($ev['lokasi']) ?></td>
                <td>👥 <?= $ev['personel'] ?></td>
                <td><?= eventBadge($ev['status']) ?></td>
                <td>
                    <div class="flex gap-2">
                        <button class="btn btn-outline btn-sm" onclick="detailEvent('<?= htmlspecialchars(json_encode($ev),ENT_QUOTES) ?>')">👁️ Detail</button>
                        <?php if(in_array($role,['admin','eo']) && $ev['status']!=='selesai'): ?>
                        <button class="btn btn-warning btn-sm" onclick="updateStatusEvent(<?= $ev['id'] ?>,this)">🔄 Status</button>
                        <?php endif; ?>
                        <?php if($ev['status']==='selesai'): ?>
                        <button class="btn btn-info btn-sm">🏆 Evaluasi</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail Event -->
<div class="modal-overlay" id="modalDetailEvent">
    <div class="modal">
        <div class="modal-header"><h3>🎪 Detail Event</h3><button class="modal-close" onclick="closeModal('modalDetailEvent')">✕</button></div>
        <div class="modal-body" id="detailEventContent"></div>
        <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('modalDetailEvent')">Tutup</button></div>
    </div>
</div>

<script>
function detailEvent(jsonStr){
    const ev = JSON.parse(jsonStr.replace(/&quot;/g,'"'));
    const sb = ev.status==='persiapan'?'⏳ Persiapan':ev.status==='berlangsung'?'🔄 Berlangsung':'✅ Selesai';
    document.getElementById('detailEventContent').innerHTML = `
        <div style="background:linear-gradient(135deg,#0f4525,#2d9b5a);color:white;padding:18px 20px;margin:-24px -24px 20px;border-radius:0;">
            <h3 style="font-size:18px;font-weight:700;">${ev.nama}</h3>
            <p style="opacity:.85;font-size:13px;margin-top:4px;">${ev.jenis} · ${sb}</p>
        </div>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <tr><td style="padding:8px 0;color:#6b8070;width:35%;">Tanggal Mulai</td><td><strong>📅 ${ev.tgl_mulai}</strong></td></tr>
            <tr><td style="padding:8px 0;color:#6b8070;">Tanggal Selesai</td><td>${ev.tgl_selesai||ev.tgl_mulai}</td></tr>
            <tr><td style="padding:8px 0;color:#6b8070;">Lokasi</td><td>📍 ${ev.lokasi}</td></tr>
            <tr><td style="padding:8px 0;color:#6b8070;">Personel</td><td>👥 ${ev.personel} orang</td></tr>
            <tr><td style="padding:8px 0;color:#6b8070;">Fasilitas</td><td>${ev.fasilitas||'—'}</td></tr>
            <tr><td style="padding:8px 0;color:#6b8070;">Deskripsi</td><td>${ev.deskripsi||'—'}</td></tr>
        </table>`;
    openModal('modalDetailEvent');
}
async function updateStatusEvent(id, btn){
    const statuses = ['persiapan','berlangsung','selesai'];
    const next = prompt('Update status ke:\n1. persiapan\n2. berlangsung\n3. selesai\n\nKetik nomor:');
    if(!next) return;
    const status = statuses[parseInt(next)-1];
    if(!status){ toast('Status tidak valid','error'); return; }
    const res = await apiFetch('event','edit',{id,status});
    if(res.ok){ toast(`Status event diperbarui: ${status}`,'success'); setTimeout(()=>location.reload(),900); }
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: APPROVAL EVENT (Admin) ═══
elseif ($sub === 'approval_event'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">✅ Approval Event <?php if(count($persiapan)): ?><span class="badge badge-warning" style="margin-left:8px;"><?= count($persiapan) ?> Menunggu</span><?php endif; ?></div></div>
    <?php if(count($persiapan)): ?>
    <div style="background:#fff8e1;border-left:4px solid #f39c12;padding:14px 20px;font-size:14px;">
        ⚠️ <strong><?= count($persiapan) ?> event</strong> dalam status persiapan perlu ditinjau dan disetujui.
    </div>
    <?php endif; ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama Event</th><th>Jenis</th><th>Tanggal</th><th>Personel</th><th>Fasilitas</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($events as $ev): ?>
            <tr>
                <td><strong><?= htmlspecialchars($ev['nama']) ?></strong></td>
                <td><span class="badge badge-info"><?= $ev['jenis'] ?></span></td>
                <td>📅 <?= date('d M Y',strtotime($ev['tgl_mulai'])) ?></td>
                <td>👥 <?= $ev['personel'] ?></td>
                <td style="font-size:13px;"><?= htmlspecialchars($ev['fasilitas']??'—') ?></td>
                <td><?= eventBadge($ev['status']) ?></td>
                <td>
                    <?php if($ev['status']==='persiapan'): ?>
                    <div class="flex gap-2">
                        <button class="btn btn-success btn-sm" onclick="openApproveModal(<?= $ev['id'] ?>,'berlangsung')">✅ Setujui</button>
                        <button class="btn btn-danger btn-sm" onclick="openApproveModal(<?= $ev['id'] ?>,'ditolak')">❌ Tolak</button>
                    </div>
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

<!-- Modal Confirm Approve/Reject -->
<div class="modal-overlay" id="modalApproveEvent">
    <div class="modal" style="max-width: 400px; text-align: center;">
        <div class="modal-body" style="padding: 30px;">
            <div style="font-size: 48px; margin-bottom: 16px;">❓</div>
            <h3 style="font-size: 20px; font-weight: 700; color: var(--text); margin-bottom: 10px;">Konfirmasi Approval</h3>
            <p id="approveEventText" style="color: var(--text-light); margin-bottom: 24px;">Apakah Anda yakin?</p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button class="btn btn-outline" style="flex: 1;" onclick="closeModal('modalApproveEvent')">Batal</button>
                <button class="btn btn-success" style="flex: 1;" onclick="doApproveEvent()">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script>
let pendingApproveEventId = null;
let pendingApproveEventStatus = null;

function openApproveModal(id, status){
    pendingApproveEventId = id;
    pendingApproveEventStatus = status;
    const label = status === 'berlangsung' ? 'menyetujui' : 'menolak';
    document.getElementById('approveEventText').innerHTML = `Apakah Anda yakin ingin <strong>${label}</strong> event ini?`;
    openModal('modalApproveEvent');
}

async function doApproveEvent(){
    if(!pendingApproveEventId || !pendingApproveEventStatus) return;
    const status = pendingApproveEventStatus;
    const label = status === 'berlangsung' ? 'disetujui' : 'ditolak';
    closeModal('modalApproveEvent');
    
    const res = await apiFetch('event','edit',{id: pendingApproveEventId, status});
    if(res.ok){ toast(`Event berhasil ${label}!`,'success'); setTimeout(()=>location.reload(),900); }
    else toast(res.msg||'Gagal','error');
}
</script>

<?php
// ═══ SUB: ASSIGN PERSONEL & FASILITAS (EO) ═══
elseif ($sub === 'assign'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">👥 Assign Personel &amp; Fasilitas</div></div>
    <div class="card-body">
        <div class="form-field" style="margin-bottom:16px;">
            <label>Pilih Event <span style="color:red">*</span></label>
            <select onchange="this.closest('.card-body').querySelector('.assign-detail').style.display='block'">
                <option value="">-- Pilih Event --</option>
                <?php foreach($events as $ev): if($ev['status']!=='selesai'): ?>
                <option value="<?= $ev['id'] ?>"><?= htmlspecialchars($ev['nama']) ?> (<?= $ev['tgl_mulai'] ?>)</option>
                <?php endif; endforeach; ?>
            </select>
        </div>
        <div class="assign-detail" style="display:none;">
            <div class="grid-2" style="margin-bottom:16px;">
                <div class="form-field">
                    <label>Personel yang Ditugaskan</label>
                    <div style="border:1.5px solid #d4e4da;border-radius:8px;padding:12px;background:#f8faf9;max-height:160px;overflow-y:auto;">
                        <?php foreach($karyawans??[] as $k): ?>
                        <label style="display:flex;align-items:center;gap:8px;padding:5px 0;cursor:pointer;font-size:14px;">
                            <input type="checkbox"> <?= htmlspecialchars($k['nama']??'Karyawan') ?>
                            <span style="font-size:12px;color:#6b8070;">(<?= $k['unit']??'' ?>)</span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-field">
                    <label>Fasilitas yang Dibutuhkan</label>
                    <div style="border:1.5px solid #d4e4da;border-radius:8px;padding:12px;background:#f8faf9;max-height:160px;overflow-y:auto;">
                        <?php foreach($conn->query("SELECT * FROM inventory")->fetch_all(MYSQLI_ASSOC) as $it): if($it['status']==='tersedia'): ?>
                        <label style="display:flex;align-items:center;gap:8px;padding:5px 0;cursor:pointer;font-size:14px;">
                            <input type="checkbox"> <?= htmlspecialchars($it['nama']) ?>
                            <span style="font-size:12px;color:#6b8070;">(<?= $it['stok'] ?> tersedia)</span>
                        </label>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            </div>
            <button class="btn btn-success" onclick="toast('Personel dan fasilitas berhasil di-assign!','success')">👥 Simpan Assign</button>
        </div>
    </div>
</div>

<?php
// ═══ SUB: EVENT SAYA (Staff) ═══
elseif ($sub === 'event_saya'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">🎫 Event Saya</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama Event</th><th>Tanggal</th><th>Lokasi</th><th>Peran Saya</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($events as $ev): ?>
            <tr>
                <td><strong><?= htmlspecialchars($ev['nama']) ?></strong></td>
                <td>📅 <?= date('d M Y',strtotime($ev['tgl_mulai'])) ?></td>
                <td>📍 <?= htmlspecialchars($ev['lokasi']) ?></td>
                <td><span class="badge badge-secondary">Panitia</span></td>
                <td><?= eventBadge($ev['status']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// ═══ SUB: REMINDER (Staff) ═══
elseif ($sub === 'reminder'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">🔔 Reminder Event</div></div>
    <div class="card-body" style="padding:0;">
        <?php foreach($persiapan as $ev):
            $diff = (strtotime($ev['tgl_mulai'])-time())/86400;
            $urgency = $diff<=3?'#fdedec':($diff<=7?'#fff8e1':'#f0f9f4');
        ?>
        <div style="padding:16px 22px;border-bottom:1px solid #e8f0eb;background:<?= $urgency ?>;">
            <div class="flex justify-between items-center">
                <div>
                    <strong style="font-size:15px;"><?= htmlspecialchars($ev['nama']) ?></strong>
                    <div style="font-size:13px;color:#6b8070;margin-top:3px;">📅 <?= date('d F Y',strtotime($ev['tgl_mulai'])) ?> · 📍 <?= $ev['lokasi'] ?></div>
                </div>
                <div style="text-align:right;">
                    <?php if($diff<=0): ?>
                    <span class="badge badge-danger">Hari Ini!</span>
                    <?php elseif($diff<=3): ?>
                    <span class="badge badge-danger"><?= (int)$diff ?> hari lagi</span>
                    <?php elseif($diff<=7): ?>
                    <span class="badge badge-warning"><?= (int)$diff ?> hari lagi</span>
                    <?php else: ?>
                    <span class="badge badge-info"><?= (int)$diff ?> hari lagi</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($persiapan)): ?>
        <div style="text-align:center;padding:40px;color:#6b8070;">Tidak ada event yang mendekati jadwal.</div>
        <?php endif; ?>
    </div>
</div>

<?php
// ═══ SUB: EVALUASI EVENT (Manager) ═══
elseif ($sub === 'evaluasi_event'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">🏆 Evaluasi Event Selesai</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama Event</th><th>Tanggal</th><th>Personel</th><th>Skor Evaluasi</th><th>Rating</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($selesai as $ev): $skor = rand(80,98); ?>
            <tr>
                <td><strong><?= htmlspecialchars($ev['nama']) ?></strong></td>
                <td>📅 <?= date('d M Y',strtotime($ev['tgl_mulai'])) ?></td>
                <td>👥 <?= $ev['personel'] ?></td>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="progress-bar" style="width:80px;"><div class="progress-fill" style="width:<?= $skor ?>%;background:#27ae60;"></div></div>
                        <strong><?= $skor ?>%</strong>
                    </div>
                </td>
                <td>⭐ <?= number_format($skor/20,1) ?>/5.0</td>
                <td><button class="btn btn-info btn-sm" onclick="toast('Laporan evaluasi dibuka','info')">📄 Lihat Laporan</button></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($selesai)): ?>
            <tr><td colspan="6" style="text-align:center;padding:30px;color:#6b8070;">Belum ada event yang selesai dievaluasi.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// ═══ SUB: UPDATE STATUS (EO) ═══
elseif ($sub === 'update_status'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">🔄 Update Status Event</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama Event</th><th>Status Saat Ini</th><th>Update Ke</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($events as $ev): if($ev['status']!=='selesai'): ?>
            <tr>
                <td><strong><?= htmlspecialchars($ev['nama']) ?></strong><br><span class="text-xs text-muted">📅 <?= $ev['tgl_mulai'] ?></span></td>
                <td><?= eventBadge($ev['status']) ?></td>
                <td>
                    <select id="newStatus<?= $ev['id'] ?>" style="padding:7px 10px;border:1.5px solid #d4e4da;border-radius:6px;font-size:13px;">
                        <option value="persiapan" <?= $ev['status']==='persiapan'?'selected':'' ?>>⏳ Persiapan</option>
                        <option value="berlangsung" <?= $ev['status']==='berlangsung'?'selected':'' ?>>🔄 Berlangsung</option>
                        <option value="selesai">✅ Selesai</option>
                    </select>
                </td>
                <td>
                    <button class="btn btn-primary-sm btn-sm" onclick="doUpdateStatus(<?= $ev['id'] ?>)">💾 Update</button>
                </td>
            </tr>
            <?php endif; endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
async function doUpdateStatus(id){
    const status = document.getElementById('newStatus'+id).value;
    const res = await apiFetch('event','edit',{id,status});
    if(res.ok){ toast('Status event berhasil diupdate!','success'); setTimeout(()=>location.reload(),900); }
    else toast(res.msg||'Gagal','error');
}
</script>

<?php endif; ?>
