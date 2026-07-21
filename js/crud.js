/**
 * AL-SYUKROSMART OPS — CRUD Frontend JS
 * Handles: fetch CRUD, modal management, table rendering, notifications
 */

// ── FETCH HELPER ──
async function apiFetch(resource, action, data={}, method='POST') {
    try {
        let url = `api/${resource}.php?action=${action}`;
        const opts = { headers: {} };
        if (method === 'POST') {
            opts.method = 'POST';
            const fd = new FormData();
            fd.append('action', action);
            Object.entries(data).forEach(([k,v]) => fd.append(k, v));
            opts.body = fd;
        } else {
            opts.method = 'GET';
            url += '&' + new URLSearchParams(data).toString();
        }
        const res = await fetch(url, opts);
        return await res.json();
    } catch (e) {
        console.error('API Error:', e);
        return { ok: false, msg: e.message };
    }
}

// ── TOAST NOTIFICATION ──
function toast(msg, type='success', duration=3500) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
        document.body.appendChild(container);
    }
    const t = document.createElement('div');
    const colors = { success:'#0f4525,#eafaf1', error:'#c0392b,#fdecea', info:'#1a5276,#eaf4fb', warning:'#856404,#fff3cd' };
    const [fg, bg] = (colors[type]||colors.info).split(',');
    t.style.cssText = `background:${bg};border-left:4px solid ${fg};color:${fg};padding:12px 18px;border-radius:10px;
      font-size:14px;font-family:'DM Sans',sans-serif;max-width:320px;box-shadow:0 4px 20px rgba(0,0,0,.15);
      animation:slideIn .3s ease;display:flex;align-items:center;gap:10px;cursor:pointer`;
    const icons = {success:'✅', error:'❌', info:'ℹ️', warning:'⚠️'};
    t.innerHTML = `<span>${icons[type]||'ℹ️'}</span><span>${msg}</span>`;
    t.onclick = () => t.remove();
    container.appendChild(t);
    setTimeout(() => { t.style.animation='slideOut .3s ease forwards'; setTimeout(()=>t.remove(), 300); }, duration);
}

// Add CSS for toast animations
const toastCSS = document.createElement('style');
toastCSS.textContent = `
@keyframes slideIn{from{opacity:0;transform:translateX(60px)}to{opacity:1;transform:translateX(0)}}
@keyframes slideOut{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(60px)}}
.loading-row td{text-align:center;padding:30px;color:#5a7a64}
.crud-modal{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;display:flex;align-items:center;justify-content:center;padding:20px;animation:fadeInBg .2s ease}
.crud-modal-box{background:#fff;border-radius:14px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:scaleIn .25s ease}
@keyframes fadeInBg{from{opacity:0}to{opacity:1}}
@keyframes scaleIn{from{opacity:0;transform:scale(.92)}to{opacity:1;transform:scale(1)}}
.crud-modal-header{padding:18px 22px;border-bottom:1px solid #cce0d4;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:#fff;z-index:1}
.crud-modal-header h3{font-size:16px;font-weight:700;color:#0f4525;font-family:'DM Sans',sans-serif}
.crud-modal-close{width:30px;height:30px;border:none;background:#f0f4f8;border-radius:7px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;transition:.2s}
.crud-modal-close:hover{background:#fdecea;color:#c0392b}
.crud-modal-body{padding:22px}
.crud-modal-footer{padding:14px 22px;border-top:1px solid #cce0d4;display:flex;justify-content:flex-end;gap:10px}
.cfield{margin-bottom:15px}
.cfield label{display:block;font-size:12px;font-weight:600;color:#1a2e22;margin-bottom:6px;text-transform:uppercase;letter-spacing:.3px}
.cfield input,.cfield select,.cfield textarea{width:100%;padding:10px 13px;border:1.5px solid #cce0d4;border-radius:8px;font-size:14px;font-family:'DM Sans',sans-serif;color:#1a2e22;background:#f7faf8;outline:none;transition:.2s;appearance:none}
.cfield input:focus,.cfield select:focus,.cfield textarea:focus{border-color:#2d9b5a;background:#fff;box-shadow:0 0 0 3px rgba(45,155,90,.1)}
.cfield textarea{min-height:80px;resize:vertical}
.crow{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.cbtn{padding:9px 20px;border-radius:8px;border:none;cursor:pointer;font-size:13.5px;font-family:'DM Sans',sans-serif;font-weight:600;transition:.2s}
.cbtn-primary{background:linear-gradient(135deg,#1a6b3c,#2d9b5a);color:#fff}
.cbtn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(26,107,60,.3)}
.cbtn-outline{background:#fff;border:1.5px solid #cce0d4;color:#1a2e22}
.cbtn-outline:hover{background:#f7faf8}
.cbtn-danger{background:#fdecea;border:1.5px solid #fccac6;color:#c0392b}
.cbtn-danger:hover{background:#e74c3c;color:#fff}
.action-btns{display:flex;gap:6px;flex-wrap:wrap}
.tbl-action{padding:5px 11px;border-radius:7px;border:1.5px solid;font-size:12px;font-family:'DM Sans',sans-serif;cursor:pointer;font-weight:600;transition:.2s;background:transparent}
.ta-edit{border-color:#cce0d4;color:#1a6b3c}.ta-edit:hover{background:#eafaf1}
.ta-delete{border-color:#fccac6;color:#c0392b}.ta-delete:hover{background:#fdecea}
.ta-view{border-color:#bee5eb;color:#0c5460}.ta-view:hover{background:#d1ecf1}
.ta-approve{border-color:#b7e8c9;color:#0f4525;background:#eafaf1}.ta-approve:hover{background:#27ae60;color:#fff;border-color:#27ae60}
.ta-reject{border-color:#fccac6;color:#c0392b;background:#fdecea}.ta-reject:hover{background:#e74c3c;color:#fff}
.ta-return{border-color:#bee5eb;color:#0c5460;background:#d1ecf1}.ta-return:hover{background:#3498db;color:#fff}
.empty-state{text-align:center;padding:48px 20px;color:#5a7a64}
.empty-state .ei{font-size:48px;margin-bottom:12px}
`;
document.head.appendChild(toastCSS);

// ── MODAL HELPERS ──
function showModal(html, id='crudModal') {
    let el = document.getElementById(id);
    if (el) el.remove();
    el = document.createElement('div');
    el.id = id; el.className = 'crud-modal';
    el.innerHTML = `<div class="crud-modal-box">${html}</div>`;
    el.addEventListener('click', e => { if(e.target === el) el.remove(); });
    document.body.appendChild(el);
    el.querySelector('.crud-modal-close')?.addEventListener('click', () => el.remove());
    return el;
}

function closeModal(id='crudModal') {
    document.getElementById(id)?.remove();
}

// ── CONFIRM DIALOG ──
function confirmDel(msg) {
    return new Promise(resolve => {
        const el = showModal(`
            <div class="crud-modal-header"><h3>⚠️ Konfirmasi</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body"><p style="font-size:14.5px;line-height:1.6;">${msg}</p></div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="this.closest('.crud-modal').remove()">Batal</button>
                <button class="cbtn cbtn-danger" id="confirmYes">Ya, Hapus</button>
            </div>`, 'confirmModal');
        el.querySelector('#confirmYes').addEventListener('click', () => { el.remove(); resolve(true); });
    });
}

// ═══════════════════════════════════════════════════
// INVENTORY CRUD
// ═══════════════════════════════════════════════════
window.InvCRUD = {
    async load(tbodyId='invTbody') {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        tbody.innerHTML = `<tr class="loading-row"><td colspan="8">⏳ Memuat data...</td></tr>`;
        const res = await apiFetch('inventory','list',{},'GET');
        if (!res.ok && !res.data) { tbody.innerHTML=`<tr class="loading-row"><td colspan="8">❌ Gagal memuat</td></tr>`; return; }
        const items = res.data || res;
        if (!items.length) { tbody.innerHTML=`<tr><td colspan="8"><div class="empty-state"><div class="ei">📦</div><p>Belum ada data barang</p></div></td></tr>`; return; }
        tbody.innerHTML = items.map(it => {
            const badge = {tersedia:'<span style="background:#d4edda;color:#155724;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">✅ Tersedia</span>',
                dipinjam:'<span style="background:#fff3cd;color:#856404;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">🔄 Dipinjam</span>',
                maintenance:'<span style="background:#fdecea;color:#c0392b;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">🔧 Maintenance</span>'}[it.status]||it.status;
            return `<tr>
                <td><code style="background:#f0f5f2;padding:3px 8px;border-radius:5px;font-size:11.5px">${it.kode||'INV-'+String(it.id).padStart(3,'0')}</code></td>
                <td><strong>${it.nama}</strong></td><td>${it.kategori}</td>
                <td><span style="font-size:12px">📍 ${it.lokasi}</span></td>
                <td><strong>${it.stok}</strong></td><td>${badge}</td>
                <td class="action-btns">
                    <button class="tbl-action ta-edit" onclick="InvCRUD.edit(${it.id})">✏️ Edit</button>
                    <button class="tbl-action ta-delete" onclick="InvCRUD.delete(${it.id},'${it.nama}')">🗑️</button>
                </td></tr>`;
        }).join('');
    },

    showAdd() {
        showModal(`
            <div class="crud-modal-header"><h3>📦 Tambah Barang Baru</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <div class="crow"><div class="cfield"><label>Nama Barang *</label><input id="ci_nama" placeholder="Nama barang"></div>
                <div class="cfield"><label>Kategori</label><select id="ci_kat"><option>Elektronik</option><option>Furnitur</option><option>Audio</option><option>ATK</option><option>Kamera</option><option>Mesin</option><option>Lainnya</option></select></div></div>
                <div class="crow"><div class="cfield"><label>Lokasi</label><input id="ci_lok" placeholder="Ruang / Gedung"></div>
                <div class="cfield"><label>Stok *</label><input id="ci_stok" type="number" min="0" value="1"></div></div>
                <div class="crow"><div class="cfield"><label>Tanggal Beli</label><input id="ci_tgl" type="date" value="${new Date().toISOString().slice(0,10)}"></div></div>
                <div class="cfield"><label>Keterangan</label><textarea id="ci_ket" placeholder="Kondisi dan catatan..."></textarea></div>
            </div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="closeModal()">Batal</button>
                <button class="cbtn cbtn-primary" onclick="InvCRUD.save()">💾 Simpan</button>
            </div>`);
    },

    async save() {
        const nama=document.getElementById('ci_nama')?.value.trim();
        if(!nama){toast('Nama barang wajib diisi','error');return;}
        const res = await apiFetch('inventory','add',{
            nama, kategori:document.getElementById('ci_kat')?.value,
            lokasi:document.getElementById('ci_lok')?.value,
            stok:document.getElementById('ci_stok')?.value,
            tgl_beli:document.getElementById('ci_tgl')?.value,
            keterangan:document.getElementById('ci_ket')?.value,
        });
        if(res.ok){toast('Barang berhasil ditambahkan! 📦','success');closeModal();this.load();}
        else toast(res.msg||'Gagal menyimpan','error');
    },

    async edit(id) {
        const res = await apiFetch('inventory','get',{id},'GET');
        const it = res.data||res;
        showModal(`
            <div class="crud-modal-header"><h3>✏️ Edit Barang</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <input type="hidden" id="ce_id" value="${it.id}">
                <div class="crow"><div class="cfield"><label>Nama Barang *</label><input id="ce_nama" value="${it.nama}"></div>
                <div class="cfield"><label>Kategori</label><select id="ce_kat"><option${it.kategori==='Elektronik'?' selected':''}>Elektronik</option><option${it.kategori==='Furnitur'?' selected':''}>Furnitur</option><option${it.kategori==='Audio'?' selected':''}>Audio</option><option${it.kategori==='ATK'?' selected':''}>ATK</option><option${it.kategori==='Kamera'?' selected':''}>Kamera</option><option${it.kategori==='Mesin'?' selected':''}>Mesin</option><option${it.kategori==='Lainnya'?' selected':''}>Lainnya</option></select></div></div>
                <div class="crow"><div class="cfield"><label>Lokasi</label><input id="ce_lok" value="${it.lokasi}"></div>
                <div class="cfield"><label>Stok</label><input id="ce_stok" type="number" value="${it.stok}"></div></div>
                <div class="cfield"><label>Status</label><select id="ce_st"><option${it.status==='tersedia'?' selected':''}>tersedia</option><option${it.status==='dipinjam'?' selected':''}>dipinjam</option><option${it.status==='maintenance'?' selected':''}>maintenance</option></select></div>
                <div class="cfield"><label>Keterangan</label><textarea id="ce_ket">${it.keterangan||''}</textarea></div>
            </div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="closeModal()">Batal</button>
                <button class="cbtn cbtn-primary" onclick="InvCRUD.update()">💾 Simpan Perubahan</button>
            </div>`);
    },

    async update() {
        const id=document.getElementById('ce_id')?.value;
        const res = await apiFetch('inventory','edit',{
            id, nama:document.getElementById('ce_nama')?.value,
            kategori:document.getElementById('ce_kat')?.value,
            lokasi:document.getElementById('ce_lok')?.value,
            stok:document.getElementById('ce_stok')?.value,
            status:document.getElementById('ce_st')?.value,
            keterangan:document.getElementById('ce_ket')?.value,
        });
        if(res.ok){toast('Data barang berhasil diperbarui! ✅','success');closeModal();this.load();}
        else toast(res.msg||'Gagal','error');
    },

    async delete(id, nama) {
        const ok2 = await confirmDel(`Hapus barang <strong>${nama}</strong>? Data tidak dapat dikembalikan.`);
        if(!ok2) return;
        const res = await apiFetch('inventory','delete',{id});
        if(res.ok){toast('Barang dihapus','info');this.load();}
        else toast(res.msg||'Gagal hapus','error');
    }
};

// ═══════════════════════════════════════════════════
// KARYAWAN CRUD
// ═══════════════════════════════════════════════════
window.KaryawanCRUD = {
    async load(tbodyId='karyTbody') {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        tbody.innerHTML = `<tr class="loading-row"><td colspan="8">⏳ Memuat...</td></tr>`;
        const res = await apiFetch('karyawan','list',{},'GET');
        const items = res.data || res;
        if (!items.length) { tbody.innerHTML=`<tr><td colspan="8"><div class="empty-state"><div class="ei">👥</div><p>Belum ada data karyawan</p></div></td></tr>`; return; }
        tbody.innerHTML = items.map(k => {
            const badge = k.status==='aktif' ? '<span style="background:#d4edda;color:#155724;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">Aktif</span>' : '<span style="background:#fff3cd;color:#856404;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">Cuti</span>';
            return `<tr>
                <td><code style="background:#f0f5f2;padding:3px 8px;border-radius:5px;font-size:11.5px">${k.nip}</code></td>
                <td><div style="display:flex;align-items:center;gap:8px">
                    <div style="width:30px;height:30px;border-radius:50%;background:#1a6b3c;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0">${k.nama[0]}</div>
                    <strong>${k.nama}</strong></div></td>
                <td>${k.jabatan}</td><td>${k.unit}</td>
                <td>${badge}</td><td>${k.kehadiran}%</td><td>${k.kinerja}%</td>
                <td class="action-btns">
                    <button class="tbl-action ta-view" onclick="KaryawanCRUD.view(${k.id})">👁️</button>
                    <button class="tbl-action ta-edit" onclick="KaryawanCRUD.edit(${k.id})">✏️</button>
                    <button class="tbl-action ta-delete" onclick="KaryawanCRUD.delete(${k.id},'${k.nama}')">🗑️</button>
                </td></tr>`;
        }).join('');
    },

    showAdd() {
        showModal(`
            <div class="crud-modal-header"><h3>👥 Tambah Karyawan Baru</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <div class="crow"><div class="cfield"><label>Nama Lengkap *</label><input id="ck_nama" placeholder="Nama lengkap"></div>
                <div class="cfield"><label>Jabatan *</label><input id="ck_jab" placeholder="Jabatan / posisi"></div></div>
                <div class="crow"><div class="cfield"><label>Unit Kerja</label><select id="ck_unit"><option>Tata Usaha</option><option>Keuangan</option><option>Kurikulum</option><option>IT</option><option>Event</option><option>Fasilitas</option><option>HR</option></select></div>
                <div class="cfield"><label>Tanggal Bergabung</label><input id="ck_tgl" type="date" value="${new Date().toISOString().slice(0,10)}"></div></div>
                <div class="crow"><div class="cfield"><label>Email</label><input id="ck_email" type="email" placeholder="email@alsyukro.sch.id"></div>
                <div class="cfield"><label>Telepon</label><input id="ck_telp" placeholder="08xxxxxxxxxx"></div></div>
            </div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="closeModal()">Batal</button>
                <button class="cbtn cbtn-primary" onclick="KaryawanCRUD.save()">💾 Simpan</button>
            </div>`);
    },

    async save() {
        const nama=document.getElementById('ck_nama')?.value.trim();
        if(!nama){toast('Nama wajib diisi','error');return;}
        const res=await apiFetch('karyawan','add',{
            nama, jabatan:document.getElementById('ck_jab')?.value,
            unit:document.getElementById('ck_unit')?.value,
            tgl_bergabung:document.getElementById('ck_tgl')?.value,
            email:document.getElementById('ck_email')?.value,
            telp:document.getElementById('ck_telp')?.value,
        });
        if(res.ok){toast('Karyawan berhasil ditambahkan! 👥','success');closeModal();this.load();}
        else toast(res.msg||'Gagal','error');
    },

    async view(id) {
        const res = await apiFetch('karyawan','get',{id},'GET');
        const k = res.data||res;
        showModal(`
            <div class="crud-modal-header"><h3>👤 Detail Karyawan</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <div style="display:flex;gap:16px;align-items:center;margin-bottom:20px;padding:16px;background:linear-gradient(135deg,#0f4525,#2d9b5a);border-radius:10px;color:#fff;">
                    <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;flex-shrink:0">${k.nama[0]}</div>
                    <div><strong style="font-size:17px;display:block">${k.nama}</strong><span style="opacity:.85;font-size:13px">${k.jabatan} — ${k.unit}</span>
                    <div style="margin-top:6px;font-size:12px;opacity:.75">${k.nip}</div></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13.5px">
                    <div style="padding:12px;background:#f7faf8;border-radius:8px"><div style="font-size:11px;color:#5a7a64;margin-bottom:3px;text-transform:uppercase">Email</div>${k.email||'-'}</div>
                    <div style="padding:12px;background:#f7faf8;border-radius:8px"><div style="font-size:11px;color:#5a7a64;margin-bottom:3px;text-transform:uppercase">Telepon</div>${k.telp||'-'}</div>
                    <div style="padding:12px;background:#f7faf8;border-radius:8px"><div style="font-size:11px;color:#5a7a64;margin-bottom:3px;text-transform:uppercase">Bergabung</div>${k.tgl_bergabung||'-'}</div>
                    <div style="padding:12px;background:#f7faf8;border-radius:8px"><div style="font-size:11px;color:#5a7a64;margin-bottom:3px;text-transform:uppercase">Status</div>${k.status}</div>
                    <div style="padding:12px;background:#eafaf1;border-radius:8px;border-left:3px solid #27ae60"><div style="font-size:11px;color:#5a7a64;margin-bottom:3px">Kehadiran</div><strong style="font-size:20px;color:#1a6b3c">${k.kehadiran}%</strong></div>
                    <div style="padding:12px;background:#eaf4fb;border-radius:8px;border-left:3px solid #2980b9"><div style="font-size:11px;color:#5a7a64;margin-bottom:3px">Kinerja</div><strong style="font-size:20px;color:#1a5276">${k.kinerja}%</strong></div>
                </div>
            </div>
            <div class="crud-modal-footer"><button class="cbtn cbtn-outline" onclick="closeModal()">Tutup</button></div>`);
    },

    async edit(id) {
        const res = await apiFetch('karyawan','get',{id},'GET');
        const k = res.data||res;
        showModal(`
            <div class="crud-modal-header"><h3>✏️ Edit Karyawan</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <input type="hidden" id="ce_kid" value="${k.id}">
                <div class="crow"><div class="cfield"><label>Nama Lengkap *</label><input id="ce_knama" value="${k.nama}"></div>
                <div class="cfield"><label>Jabatan</label><input id="ce_kjab" value="${k.jabatan}"></div></div>
                <div class="crow"><div class="cfield"><label>Unit</label><select id="ce_kunit"><option${k.unit==='Tata Usaha'?' selected':''}>Tata Usaha</option><option${k.unit==='Keuangan'?' selected':''}>Keuangan</option><option${k.unit==='Kurikulum'?' selected':''}>Kurikulum</option><option${k.unit==='IT'?' selected':''}>IT</option><option${k.unit==='Event'?' selected':''}>Event</option></select></div>
                <div class="cfield"><label>Status</label><select id="ce_kst"><option${k.status==='aktif'?' selected':''} value="aktif">Aktif</option><option${k.status==='cuti'?' selected':''} value="cuti">Cuti</option><option value="nonaktif">Non-Aktif</option></select></div></div>
                <div class="crow"><div class="cfield"><label>Email</label><input id="ce_kemail" type="email" value="${k.email||''}"></div>
                <div class="cfield"><label>Telepon</label><input id="ce_ktelp" value="${k.telp||''}"></div></div>
            </div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="closeModal()">Batal</button>
                <button class="cbtn cbtn-primary" onclick="KaryawanCRUD.update()">💾 Simpan</button>
            </div>`);
    },

    async update() {
        const id=document.getElementById('ce_kid')?.value;
        const res=await apiFetch('karyawan','edit',{
            id, nama:document.getElementById('ce_knama')?.value,
            jabatan:document.getElementById('ce_kjab')?.value,
            unit:document.getElementById('ce_kunit')?.value,
            status:document.getElementById('ce_kst')?.value,
            email:document.getElementById('ce_kemail')?.value,
            telp:document.getElementById('ce_ktelp')?.value,
        });
        if(res.ok){toast('Data karyawan diperbarui! ✅','success');closeModal();this.load();}
        else toast(res.msg||'Gagal','error');
    },

    async delete(id, nama) {
        const ok2 = await confirmDel(`Hapus karyawan <strong>${nama}</strong>?`);
        if(!ok2) return;
        const res = await apiFetch('karyawan','delete',{id});
        if(res.ok){toast('Karyawan dihapus','info');this.load();}
        else toast(res.msg||'Gagal','error');
    }
};

// ═══════════════════════════════════════════════════
// PEMINJAMAN CRUD
// ═══════════════════════════════════════════════════
window.PinjamCRUD = {
    async loadApproval(tbodyId='approvalTbody') {
        const tbody = document.getElementById(tbodyId);
        if(!tbody) return;
        tbody.innerHTML = `<tr class="loading-row"><td colspan="7">⏳ Memuat...</td></tr>`;
        const res = await apiFetch('peminjaman','list',{},'GET');
        const items = (res.data||res).filter(x => x.status==='pending');
        if(!items.length){tbody.innerHTML=`<tr><td colspan="7"><div class="empty-state"><div class="ei">✅</div><p>Tidak ada permintaan pending</p></div></td></tr>`;return;}
        tbody.innerHTML=items.map(l=>`<tr>
            <td><code style="background:#f0f5f2;padding:3px 8px;border-radius:5px;font-size:11.5px">${l.kode}</code></td>
            <td><strong>${l.peminjam}</strong><br><span style="font-size:11.5px;color:#5a7a64">${l.unit}</span></td>
            <td>${l.barang}</td><td>${l.jumlah}</td><td>${l.tgl_kembali}</td>
            <td><span style="font-size:12px;color:#5a7a64">${l.keperluan||'-'}</span></td>
            <td class="action-btns">
                <button class="tbl-action ta-approve" onclick="PinjamCRUD.approve(${l.id})">✅ Setuju</button>
                <button class="tbl-action ta-reject" onclick="PinjamCRUD.reject(${l.id})">❌ Tolak</button>
            </td></tr>`).join('');
    },

    async approve(id) {
        let ok = true;
        if (typeof customConfirm === 'function') {
            ok = await customConfirm('Anda yakin ingin menyetujui peminjaman ini?', 'Ya, Setuju', 'btn-success', '🤝');
        } else if (typeof confirmDel === 'function') {
            ok = await confirmDel('Setujui peminjaman ini?');
        } else {
            ok = confirm('Setujui peminjaman ini?');
        }
        if(!ok) return;

        const res=await apiFetch('peminjaman','approve',{id, status: 'dipinjam'});
        if(res.ok){toast('Peminjaman disetujui! ✅','success');this.loadApproval();}
        else toast(res.msg||'Gagal','error');
    },

    async reject(id) {
        let ok = true;
        if (typeof customConfirm === 'function') {
            ok = await customConfirm('Tolak permintaan peminjaman ini?', 'Ya, Tolak', 'btn-danger', '🛑');
        } else if (typeof confirmDel === 'function') {
            ok = await confirmDel('Tolak permintaan peminjaman ini?');
        } else {
            ok = confirm('Tolak permintaan peminjaman ini?');
        }
        if(!ok) return;

        const res=await apiFetch('peminjaman','reject',{id, status: 'ditolak'});
        if(res.ok){toast('Peminjaman ditolak','info');this.loadApproval();}
        else toast(res.msg||'Gagal','error');
    },

    async loadRiwayat(tbodyId='riwayatTbody') {
        const tbody=document.getElementById(tbodyId);
        if(!tbody) return;
        tbody.innerHTML=`<tr class="loading-row"><td colspan="7">⏳ Memuat...</td></tr>`;
        const res=await apiFetch('peminjaman','list',{},'GET');
        const items=res.data||res;
        tbody.innerHTML=items.map(l=>{
            const sb=({pending:'<span style="background:#d1ecf1;color:#0c5460;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">⏳ Pending</span>',
                dipinjam:'<span style="background:#fff3cd;color:#856404;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">🔄 Dipinjam</span>',
                dikembalikan:'<span style="background:#d4edda;color:#155724;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">✅ Dikembalikan</span>',
                ditolak:'<span style="background:#fdecea;color:#c0392b;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">❌ Ditolak</span>'})[l.status]||l.status;
            const rtBtn=l.status==='dipinjam'?`<button class="tbl-action ta-return" onclick="PinjamCRUD.return(${l.id})">📥 Kembalikan</button>`:'';
            return `<tr><td><code style="background:#f0f5f2;padding:3px 8px;border-radius:5px;font-size:11.5px">${l.kode}</code></td>
                <td><strong>${l.peminjam}</strong></td><td>${l.barang}</td><td>${l.jumlah}</td>
                <td>${l.tgl_pinjam}</td><td>${l.tgl_kembali}</td>
                <td>${sb}</td><td class="action-btns">${rtBtn}</td></tr>`;
        }).join('');
    },

    async return(id) {
        const res=await apiFetch('peminjaman','return',{id});
        if(res.ok){toast('Barang berhasil dikembalikan! 📥','success');this.loadRiwayat();this.loadApproval();}
        else toast(res.msg||'Gagal','error');
    },

    showPinjam(barang='') {
        showModal(`
            <div class="crud-modal-header"><h3>📤 Ajukan Peminjaman</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <div class="cfield"><label>Barang yang Dipinjam *</label><input id="cp_barang" placeholder="Nama barang" value="${barang}"></div>
                <div class="crow"><div class="cfield"><label>Jumlah *</label><input id="cp_jml" type="number" min="1" value="1"></div>
                <div class="cfield"><label>Tgl Kembali *</label><input id="cp_tgl" type="date" min="${new Date().toISOString().slice(0,10)}"></div></div>
                <div class="cfield"><label>Keperluan</label><textarea id="cp_ket" placeholder="Jelaskan keperluan peminjaman..."></textarea></div>
            </div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="closeModal()">Batal</button>
                <button class="cbtn cbtn-primary" onclick="PinjamCRUD.submit()">📤 Ajukan</button>
            </div>`);
    },

    async submit() {
        const barang=document.getElementById('cp_barang')?.value.trim();
        const tgl=document.getElementById('cp_tgl')?.value;
        if(!barang||!tgl){toast('Barang dan tanggal kembali wajib diisi','error');return;}
        const res=await apiFetch('peminjaman','add',{
            barang, jumlah:document.getElementById('cp_jml')?.value,
            tgl_kembali:tgl, keperluan:document.getElementById('cp_ket')?.value,
        });
        if(res.ok){toast('Permintaan peminjaman diajukan! Menunggu approval.','success');closeModal();this.loadRiwayat();}
        else toast(res.msg||'Gagal','error');
    }
};

// ═══════════════════════════════════════════════════
// EVENT CRUD
// ═══════════════════════════════════════════════════
window.EventCRUD = {
    async load(tbodyId='eventTbody') {
        const tbody=document.getElementById(tbodyId);
        if(!tbody) return;
        tbody.innerHTML=`<tr class="loading-row"><td colspan="7">⏳ Memuat...</td></tr>`;
        const res=await apiFetch('event','list',{},'GET');
        const items=res.data||res;
        tbody.innerHTML=items.map(e=>{
            const sb=({persiapan:'<span style="background:#fff3cd;color:#856404;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">⏳ Persiapan</span>',
                berlangsung:'<span style="background:#cce5ff;color:#004085;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">🔄 Berlangsung</span>',
                selesai:'<span style="background:#d4edda;color:#155724;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">✅ Selesai</span>'})[e.status]||e.status;
            return `<tr>
                <td><code style="background:#f0f5f2;padding:3px 8px;border-radius:5px;font-size:11.5px">${e.kode}</code></td>
                <td><strong>${e.nama}</strong></td><td>${e.jenis}</td>
                <td>📅 ${e.tgl_mulai}</td><td>📍 ${e.lokasi}</td>
                <td>${sb}</td>
                <td class="action-btns">
                    ${e.approved?'':`<button class="tbl-action ta-approve" onclick="EventCRUD.approve(${e.id})">✅ Approve</button>`}
                    <button class="tbl-action ta-edit" onclick="EventCRUD.edit(${e.id})">✏️</button>
                    <button class="tbl-action ta-delete" onclick="EventCRUD.delete(${e.id},'${e.nama}')">🗑️</button>
                </td></tr>`;
        }).join('');
    },

    showAdd() {
        showModal(`
            <div class="crud-modal-header"><h3>🎪 Buat Event Baru</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <div class="crow"><div class="cfield"><label>Nama Event *</label><input id="ev_nama" placeholder="Nama kegiatan"></div>
                <div class="cfield"><label>Jenis</label><select id="ev_jenis"><option>Akademik</option><option>Non-Akademik</option><option>Internal</option><option>Eksternal</option></select></div></div>
                <div class="crow"><div class="cfield"><label>Tgl Mulai *</label><input id="ev_tgl1" type="date"></div>
                <div class="cfield"><label>Tgl Selesai</label><input id="ev_tgl2" type="date"></div></div>
                <div class="crow"><div class="cfield"><label>Lokasi</label><input id="ev_lok" placeholder="Aula / Lokasi"></div>
                <div class="cfield"><label>Jml Personel</label><input id="ev_per" type="number" value="10"></div></div>
                <div class="cfield"><label>Kebutuhan Fasilitas</label><input id="ev_fas" placeholder="Sound system, Proyektor, dll"></div>
                <div class="cfield"><label>Deskripsi</label><textarea id="ev_dsk" placeholder="Detail kegiatan..."></textarea></div>
            </div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="closeModal()">Batal</button>
                <button class="cbtn cbtn-primary" onclick="EventCRUD.save()">🎪 Buat Event</button>
            </div>`);
    },

    async save() {
        const nama=document.getElementById('ev_nama')?.value.trim();
        const tgl=document.getElementById('ev_tgl1')?.value;
        if(!nama||!tgl){toast('Nama dan tanggal wajib diisi','error');return;}
        const res=await apiFetch('event','add',{
            nama, jenis:document.getElementById('ev_jenis')?.value,
            tgl_mulai:tgl, tgl_selesai:document.getElementById('ev_tgl2')?.value,
            lokasi:document.getElementById('ev_lok')?.value,
            personel:document.getElementById('ev_per')?.value,
            fasilitas:document.getElementById('ev_fas')?.value,
            deskripsi:document.getElementById('ev_dsk')?.value,
        });
        if(res.ok){toast('Event berhasil dibuat! 🎪','success');closeModal();this.load();}
        else toast(res.msg||'Gagal','error');
    },

    async approve(id) {
        const res=await apiFetch('event','approve',{id});
        if(res.ok){toast('Event diapprove! ✅','success');this.load();}
        else toast(res.msg||'Akses ditolak','error');
    },

    async edit(id) {
        const res=await apiFetch('event','get',{id},'GET');
        const e=res.data||res;
        showModal(`
            <div class="crud-modal-header"><h3>✏️ Edit Event</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <input type="hidden" id="cee_id" value="${e.id}">
                <div class="cfield"><label>Nama Event *</label><input id="cee_nama" value="${e.nama}"></div>
                <div class="crow"><div class="cfield"><label>Tgl Mulai</label><input id="cee_tgl1" type="date" value="${e.tgl_mulai}"></div>
                <div class="cfield"><label>Status</label><select id="cee_st"><option${e.status==='persiapan'?' selected':''} value="persiapan">Persiapan</option><option${e.status==='berlangsung'?' selected':''} value="berlangsung">Berlangsung</option><option${e.status==='selesai'?' selected':''} value="selesai">Selesai</option></select></div></div>
                <div class="cfield"><label>Lokasi</label><input id="cee_lok" value="${e.lokasi}"></div>
            </div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="closeModal()">Batal</button>
                <button class="cbtn cbtn-primary" onclick="EventCRUD.update()">💾 Simpan</button>
            </div>`);
    },

    async update() {
        const id=document.getElementById('cee_id')?.value;
        const res=await apiFetch('event','edit',{id,nama:document.getElementById('cee_nama')?.value,tgl_mulai:document.getElementById('cee_tgl1')?.value,status:document.getElementById('cee_st')?.value,lokasi:document.getElementById('cee_lok')?.value});
        if(res.ok){toast('Event diperbarui ✅','success');closeModal();this.load();}
        else toast(res.msg||'Gagal','error');
    },

    async delete(id, nama) {
        const ok2=await confirmDel(`Hapus event <strong>${nama}</strong>?`);
        if(!ok2) return;
        const res=await apiFetch('event','delete',{id});
        if(res.ok){toast('Event dihapus','info');this.load();}
        else toast(res.msg||'Gagal','error');
    }
};

// ── MAINTENANCE CRUD ──
window.MaintCRUD = {
    async load(tbodyId='maintTbody') {
        const tbody=document.getElementById(tbodyId);
        if(!tbody) return;
        tbody.innerHTML=`<tr class="loading-row"><td colspan="7">⏳ Memuat...</td></tr>`;
        const res=await apiFetch('maintenance','list',{},'GET');
        const items=res.data||res;
        tbody.innerHTML=items.map(m=>{
            const sb=({selesai:'<span style="background:#d4edda;color:#155724;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">✅ Selesai</span>',
                'dalam proses':'<span style="background:#fff3cd;color:#856404;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">🔧 Proses</span>',
                terjadwal:'<span style="background:#d1ecf1;color:#0c5460;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">📅 Terjadwal</span>'})[m.status]||m.status;
            const pb=({Darurat:'<span style="background:#fdecea;color:#c0392b;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">🔴 Darurat</span>',Tinggi:'<span style="background:#fff3cd;color:#856404;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">🟡 Tinggi</span>'})[m.prioritas]||'<span style="background:#e2e3e5;color:#383d41;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">Normal</span>';
            return `<tr>
                <td><code style="background:#f0f5f2;padding:3px 8px;border-radius:5px;font-size:11.5px">${m.kode}</code></td>
                <td><strong>${m.fasilitas}</strong></td><td>${m.jenis}</td>
                <td>${pb}</td><td>🔧 ${m.teknisi}</td><td>${sb}</td>
                <td class="action-btns">
                    ${m.status!=='selesai'?`<button class="tbl-action ta-approve" onclick="MaintCRUD.updateStatus(${m.id},'selesai')">✅ Selesai</button>`:''}
                    ${m.status==='terjadwal'?`<button class="tbl-action ta-edit" onclick="MaintCRUD.updateStatus(${m.id},'dalam proses')">▶️ Mulai</button>`:''}
                    <button class="tbl-action ta-delete" onclick="MaintCRUD.delete(${m.id})">🗑️</button>
                </td></tr>`;
        }).join('');
    },

    async updateStatus(id, status) {
        const res=await apiFetch('maintenance','update_status',{id,status});
        if(res.ok){toast(`Status diperbarui: ${status} ✅`,'success');this.load();}
        else toast(res.msg||'Gagal','error');
    },

    showAdd() {
        showModal(`
            <div class="crud-modal-header"><h3>🛠️ Request Maintenance</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <div class="crow"><div class="cfield"><label>Fasilitas *</label><input id="mn_fas" placeholder="Nama fasilitas/aset"></div>
                <div class="cfield"><label>Jenis</label><select id="mn_jenis"><option>Perbaikan</option><option>Rutin</option><option>Darurat</option><option>Penggantian</option></select></div></div>
                <div class="crow"><div class="cfield"><label>Prioritas</label><select id="mn_pri"><option>Normal</option><option>Tinggi</option><option>Darurat</option></select></div>
                <div class="cfield"><label>Jadwal</label><input id="mn_tgl" type="date" value="${new Date().toISOString().slice(0,10)}"></div></div>
                <div class="cfield"><label>Teknisi</label><input id="mn_tek" placeholder="Nama teknisi/tim"></div>
                <div class="cfield"><label>Deskripsi Masalah</label><textarea id="mn_dsk" placeholder="Jelaskan masalah..."></textarea></div>
            </div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="closeModal()">Batal</button>
                <button class="cbtn cbtn-primary" onclick="MaintCRUD.save()">📤 Ajukan</button>
            </div>`);
    },

    async save() {
        const fas=document.getElementById('mn_fas')?.value.trim();
        if(!fas){toast('Fasilitas wajib diisi','error');return;}
        const res=await apiFetch('maintenance','add',{fasilitas:fas,jenis:document.getElementById('mn_jenis')?.value,prioritas:document.getElementById('mn_pri')?.value,tgl_jadwal:document.getElementById('mn_tgl')?.value,teknisi:document.getElementById('mn_tek')?.value,deskripsi:document.getElementById('mn_dsk')?.value});
        if(res.ok){toast('Request maintenance diajukan! 🛠️','success');closeModal();this.load();}
        else toast(res.msg||'Gagal','error');
    },

    async delete(id) {
        const ok2=await confirmDel('Hapus data maintenance ini?');
        if(!ok2) return;
        const res=await apiFetch('maintenance','delete',{id});
        if(res.ok){toast('Data dihapus','info');this.load();}
        else toast(res.msg||'Gagal','error');
    }
};

// ── TABLE SEARCH ──
document.addEventListener('input', e => {
    if (e.target.classList.contains('table-search')) {
        const q = e.target.value.toLowerCase();
        const tbl = document.querySelector(e.target.dataset.table);
        tbl?.querySelectorAll('tbody tr').forEach(r => {
            r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }
});

// ── AUTO-INIT on page load ──
document.addEventListener('DOMContentLoaded', () => {
    // Auto-load any data tables present
    if (document.getElementById('invTbody'))     InvCRUD.load();
    if (document.getElementById('karyTbody'))    KaryawanCRUD.load();
    if (document.getElementById('approvalTbody')) PinjamCRUD.loadApproval();
    if (document.getElementById('riwayatTbody')) PinjamCRUD.loadRiwayat();
    if (document.getElementById('eventTbody'))   EventCRUD.load();
    if (document.getElementById('maintTbody'))   MaintCRUD.load();
});

// ═══════════════════════════════════════════════════
// ARSIP CRUD
// ═══════════════════════════════════════════════════
window.ArsipCRUD = {
    async load(tbodyId='arsipTbody') {
        const tbody=document.getElementById(tbodyId);
        if(!tbody) return;
        tbody.innerHTML=`<tr class="loading-row"><td colspan="7">⏳ Memuat arsip...</td></tr>`;
        const res=await apiFetch('arsip','list',{},'GET');
        const items=res.data||res;
        if(!items.length){tbody.innerHTML=`<tr><td colspan="7"><div class="empty-state"><div class="ei">📁</div><p>Belum ada arsip</p></div></td></tr>`;return;}
        const icons={pdf:'📄',xlsx:'📊',docx:'📝',zip:'🗜️',pptx:'📑',jpg:'🖼️',png:'🖼️'};
        const kColors={SDM:'#cce5ff,#004085',Keuangan:'#d4edda,#155724',Akademik:'#f4ecf7,#6c3483',Administrasi:'#fff3cd,#856404',Dokumentasi:'#fdecea,#c0392b',Sarana:'#e2e3e5,#383d41'};
        tbody.innerHTML=items.map(it=>{
            const [bg,fg]=(kColors[it.kategori]||'#f0f5f2,#1a2e22').split(',');
            return `<tr>
                <td><code style="background:#f0f5f2;padding:3px 8px;border-radius:5px;font-size:11.5px">${it.kode}</code></td>
                <td style="display:flex;align-items:center;gap:8px;padding:11px 16px">
                    <span style="font-size:22px">${icons[it.tipe]||'📁'}</span>
                    <div><strong style="display:block">${it.nama}</strong><span style="font-size:11.5px;color:#5a7a64">${it.keterangan||''}</span></div></td>
                <td><span style="background:${bg};color:${fg};padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">${it.kategori}</span></td>
                <td>${it.tahun}</td>
                <td style="font-size:12px;color:#5a7a64">${it.ukuran}</td>
                <td><span style="background:${it.kerahasiaan==='Publik'?'#d4edda,#155724':it.kerahasiaan==='Rahasia'?'#fdecea,#c0392b':'#fff3cd,#856404'.split(',')[0]};color:${it.kerahasiaan==='Publik'?'#155724':it.kerahasiaan==='Rahasia'?'#c0392b':'#856404'};padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">${it.kerahasiaan}</span></td>
                <td class="action-btns">
                    <button class="tbl-action ta-view" onclick="ArsipCRUD.detail(${it.id})">👁️ Lihat</button>
                    <button class="tbl-action ta-delete" onclick="ArsipCRUD.delete(${it.id},'${it.nama}')">🗑️</button>
                </td></tr>`;
        }).join('');
    },

    async search(q) {
        const tbody=document.getElementById('arsipTbody');
        if(!tbody) return;
        tbody.innerHTML=`<tr class="loading-row"><td colspan="7">🔍 Mencari "${q}"...</td></tr>`;
        const res=await apiFetch('arsip','search',{q},'GET');
        const items=res.data||res;
        if(!items.length){tbody.innerHTML=`<tr><td colspan="7"><div class="empty-state"><div class="ei">🔍</div><p>Tidak ada hasil untuk "<strong>${q}</strong>"</p></div></td></tr>`;return;}
        // Re-render with search results
        ArsipCRUD._render(items, tbody);
    },

    _render(items,tbody) {
        const icons={pdf:'📄',xlsx:'📊',docx:'📝',zip:'🗜️'};
        tbody.innerHTML=items.map(it=>`<tr>
            <td><code style="background:#f0f5f2;padding:3px 8px;border-radius:5px;font-size:11.5px">${it.kode}</code></td>
            <td><div style="display:flex;align-items:center;gap:8px"><span style="font-size:20px">${icons[it.tipe]||'📁'}</span><strong>${it.nama}</strong></div></td>
            <td><span style="background:#f0f5f2;padding:3px 8px;border-radius:10px;font-size:11px">${it.kategori}</span></td>
            <td>${it.tahun}</td><td style="font-size:12px">${it.ukuran}</td><td>${it.kerahasiaan}</td>
            <td class="action-btns">
                <button class="tbl-action ta-view" onclick="ArsipCRUD.detail(${it.id})">👁️</button>
                <button class="tbl-action ta-delete" onclick="ArsipCRUD.delete(${it.id},'${it.nama}')">🗑️</button>
            </td></tr>`).join('');
    },

    async detail(id) {
        const res=await apiFetch('arsip','get',{id},'GET');
        const it=res.data||res;
        const icons={pdf:'📄',xlsx:'📊',docx:'📝',zip:'🗜️',pptx:'📑'};
        showModal(`
            <div class="crud-modal-header"><h3>${icons[it.tipe]||'📁'} Detail Dokumen Arsip</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <div style="background:linear-gradient(135deg,#0f4525,#2d9b5a);border-radius:10px;padding:18px 20px;color:#fff;margin-bottom:18px;display:flex;align-items:center;gap:14px">
                    <span style="font-size:36px">${icons[it.tipe]||'📁'}</span>
                    <div><strong style="font-size:16px;display:block">${it.nama}</strong>
                    <span style="opacity:.8;font-size:13px">${it.kode} · ${it.tipe.toUpperCase()} · ${it.ukuran}</span></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:13.5px">
                    ${[['Kategori',it.kategori],['Tahun',it.tahun],['Kerahasiaan',it.kerahasiaan],['Diupload oleh',it.uploader],['Tanggal Upload',it.tgl_upload],['Ukuran',it.ukuran]].map(([l,v])=>`
                    <div style="padding:11px 14px;background:#f7faf8;border-radius:8px"><div style="font-size:11px;color:#5a7a64;margin-bottom:2px;text-transform:uppercase">${l}</div><strong>${v}</strong></div>`).join('')}
                </div>
                <div style="margin-top:12px;padding:12px 14px;background:#f7faf8;border-radius:8px">
                    <div style="font-size:11px;color:#5a7a64;margin-bottom:4px;text-transform:uppercase">Keterangan</div>
                    <p style="font-size:13.5px">${it.keterangan||'—'}</p>
                </div>
            </div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="closeModal()">Tutup</button>
                <button class="cbtn cbtn-primary" onclick="toast('Simulasi download — file tersimpan di server','info');closeModal()">⬇️ Unduh Dokumen</button>
            </div>`);
    },

    showUpload() {
        showModal(`
            <div class="crud-modal-header"><h3>⬆️ Upload Dokumen Baru</h3><button class="crud-modal-close">✕</button></div>
            <div class="crud-modal-body">
                <div class="cfield"><label>Nama Dokumen *</label><input id="au_nama" placeholder="Judul dokumen"></div>
                <div class="crow">
                    <div class="cfield"><label>Kategori *</label><select id="au_kat"><option>SDM</option><option>Keuangan</option><option>Akademik</option><option>Administrasi</option><option>Dokumentasi</option><option>Sarana</option></select></div>
                    <div class="cfield"><label>Tahun</label><input id="au_thn" type="number" value="${new Date().getFullYear()}"></div>
                </div>
                <div class="crow">
                    <div class="cfield"><label>Tipe File</label><select id="au_tipe"><option>pdf</option><option>docx</option><option>xlsx</option><option>pptx</option><option>zip</option><option>jpg</option></select></div>
                    <div class="cfield"><label>Kerahasiaan</label><select id="au_khs"><option>Publik</option><option>Internal</option><option>Rahasia</option></select></div>
                </div>
                <div class="cfield">
                    <label>File Dokumen</label>
                    <div style="border:2px dashed #cce0d4;border-radius:10px;padding:28px;text-align:center;background:#f7faf8;cursor:pointer" onclick="document.getElementById('au_file').click()">
                        <div style="font-size:32px;margin-bottom:8px">📁</div>
                        <p style="font-size:13.5px;color:#5a7a64">Klik untuk pilih file</p>
                        <p style="font-size:11.5px;color:#aaa;margin-top:4px">PDF, DOCX, XLSX, ZIP — Maks 50MB</p>
                        <input type="file" id="au_file" style="display:none" onchange="document.getElementById('au_fname').textContent=this.files[0]?.name||''">
                    </div>
                    <p id="au_fname" style="font-size:12.5px;color:#1a6b3c;margin-top:6px;font-weight:600"></p>
                </div>
                <div class="cfield"><label>Keterangan</label><textarea id="au_ket" placeholder="Deskripsi singkat dokumen..."></textarea></div>
            </div>
            <div class="crud-modal-footer">
                <button class="cbtn cbtn-outline" onclick="closeModal()">Batal</button>
                <button class="cbtn cbtn-primary" onclick="ArsipCRUD.save()">⬆️ Upload</button>
            </div>`);
    },

    async save() {
        const nama=document.getElementById('au_nama')?.value.trim();
        if(!nama){toast('Nama dokumen wajib','error');return;}
        const file=document.getElementById('au_file')?.files[0];
        const ukuran=file?`${(file.size/1024/1024).toFixed(1)} MB`:'—';
        const res=await apiFetch('arsip','add',{
            nama, kategori:document.getElementById('au_kat')?.value,
            tahun:document.getElementById('au_thn')?.value,
            tipe:document.getElementById('au_tipe')?.value,
            kerahasiaan:document.getElementById('au_khs')?.value,
            ukuran, keterangan:document.getElementById('au_ket')?.value,
        });
        if(res.ok){toast('Dokumen berhasil diupload! 📁','success');closeModal();this.load();}
        else toast(res.msg||'Gagal','error');
    },

    async delete(id,nama) {
        const ok2=await confirmDel(`Hapus arsip <strong>${nama}</strong>?`);
        if(!ok2) return;
        const res=await apiFetch('arsip','delete',{id});
        if(res.ok){toast('Arsip dihapus','info');this.load();}
        else toast(res.msg||'Gagal','error');
    }
};

// ═══════════════════════════════════════════════════
// ABSENSI CRUD
// ═══════════════════════════════════════════════════
window.AbsensiCRUD = {
    async load(tbodyId='absensiTbody') {
        const tbody=document.getElementById(tbodyId);
        if(!tbody) return;
        tbody.innerHTML=`<tr class="loading-row"><td colspan="6">⏳ Memuat...</td></tr>`;
        const res=await apiFetch('absensi','list',{},'GET');
        const items=res.data||res;
        if(!items.length){tbody.innerHTML=`<tr><td colspan="6"><div class="empty-state"><div class="ei">📋</div><p>Belum ada data absensi</p></div></td></tr>`;return;}
        const sb=(s)=>({hadir:'<span style="background:#d4edda;color:#155724;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">✅ Hadir</span>',
            izin:'<span style="background:#fff3cd;color:#856404;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">🤒 Izin/Sakit</span>',
            cuti:'<span style="background:#d1ecf1;color:#0c5460;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">🌴 Cuti</span>',
            absen:'<span style="background:#fdecea;color:#c0392b;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">❌ Absen</span>'})[s]||s;
        tbody.innerHTML=items.map(a=>`<tr>
            <td><strong>${a.nama}</strong></td><td>${a.unit}</td>
            <td>${a.masuk||'<span style="color:#aaa">—</span>'}</td>
            <td>${a.keluar||'<span style="color:#aaa">—</span>'}</td>
            <td>${sb(a.status)}</td>
            <td style="font-size:12px;color:#5a7a64">${a.keterangan||'—'}</td>
        </tr>`).join('');
        // Update summary
        const hadir=items.filter(x=>x.status==='hadir').length;
        const total=items.length;
        document.getElementById('absHadir')&&(document.getElementById('absHadir').textContent=hadir);
        document.getElementById('absTotal')&&(document.getElementById('absTotal').textContent=total);
        document.getElementById('absPct')&&(document.getElementById('absPct').textContent=Math.round(hadir/total*100)+'%');
    },

    async checkIn() {
        const res=await apiFetch('absensi','checkin',{});
        if(res.ok){toast(`Absen masuk berhasil! Jam ${res.data.masuk} ✅`,'success');this.load();}
        else toast(res.msg||'Gagal','error');
    },

    async checkOut() {
        const res=await apiFetch('absensi','checkout',{});
        if(res.ok){toast(`Absen keluar berhasil! Jam ${res.data.keluar} 👋`,'success');this.load();}
        else toast(res.msg||'Gagal','error');
    }
};

// ═══════════════════════════════════════════════════
// AKTIVITAS CRUD
// ═══════════════════════════════════════════════════
window.AktivitasCRUD = {
    async load(tbodyId='aktivTbody', mineOnly=false) {
        const tbody=document.getElementById(tbodyId);
        if(!tbody) return;
        tbody.innerHTML=`<tr class="loading-row"><td colspan="5">⏳ Memuat...</td></tr>`;
        const action=mineOnly?'my':'list';
        const res=await apiFetch('aktivitas',action,{},'GET');
        const items=res.data||res;
        if(!items.length){tbody.innerHTML=`<tr><td colspan="5"><div class="empty-state"><div class="ei">📝</div><p>Belum ada aktivitas</p></div></td></tr>`;return;}
        const jColors={Administrasi:'#d1ecf1,#0c5460',Pengajaran:'#d4edda,#155724',Rapat:'#fff3cd,#856404',Koordinasi:'#f4ecf7,#6c3483',Teknis:'#fdecea,#721c24',Lainnya:'#e2e3e5,#383d41'};
        tbody.innerHTML=items.map(a=>{
            const [bg,fg]=(jColors[a.jenis]||jColors.Lainnya).split(',');
            return `<tr>
                <td>📅 ${a.tanggal}</td>
                <td><strong>${a.nama}</strong><br><span style="font-size:11.5px;color:#5a7a64">${a.unit}</span></td>
                <td><span style="background:${bg};color:${fg};padding:3px 9px;border-radius:12px;font-size:11px;font-weight:600">${a.jenis}</span></td>
                <td style="font-size:13.5px">${a.deskripsi}</td>
                <td style="font-size:12px;color:#5a7a64">${a.jam}</td>
            </tr>`;
        }).join('');
    },

    async save() {
        const deskripsi=document.getElementById('akt_dsk')?.value.trim();
        if(!deskripsi){toast('Deskripsi aktivitas wajib diisi','error');return;}
        const res=await apiFetch('aktivitas','add',{
            jenis:document.getElementById('akt_jenis')?.value,
            deskripsi, tanggal:document.getElementById('akt_tgl')?.value,
        });
        if(res.ok){
            toast('Aktivitas harian berhasil dicatat! ✅','success');
            document.getElementById('akt_dsk').value='';
            this.load('aktivTbody', true);
        } else toast(res.msg||'Gagal','error');
    }
};

// ── AUTO-INIT extended ──
const _origInit = document.addEventListener;
document.addEventListener('DOMContentLoaded', () => {
    if(document.getElementById('arsipTbody'))   ArsipCRUD.load();
    if(document.getElementById('absensiTbody')) AbsensiCRUD.load();
    if(document.getElementById('aktivTbody'))   AktivitasCRUD.load('aktivTbody', true);
    if(document.getElementById('monitorTbody')) AktivitasCRUD.load('monitorTbody', false);
});
