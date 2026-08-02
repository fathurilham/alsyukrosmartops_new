/* AL-SYUKROSMART OPS — main.js */
'use strict';

/* TOAST */
function toast(msg, type = 'info', duration = 4000) {
    const c = document.getElementById('toastContainer');
    if (!c) { console.log(`[${type}] ${msg}`); return; }
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    const ic = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    el.innerHTML = `<span style="font-size:18px;">${ic[type] || 'ℹ️'}</span><span style="flex:1;line-height:1.4;">${msg}</span>`;
    el.onclick = () => el.remove();
    c.appendChild(el);
    setTimeout(() => { el.style.transition = 'opacity .4s,transform .4s'; el.style.opacity = '0'; el.style.transform = 'translateX(120%)'; setTimeout(() => el.remove(), 400); }, duration);
}

/* API FETCH */
async function apiFetch(resource, action, data = {}, method = 'POST') {
    try {
        let url, opts = {};
        if (method === 'GET') {
            url = `api/${resource}.php?${new URLSearchParams({ ...data, action, _t: Date.now() })}`;
            opts = { method: 'GET', cache: 'no-store' };
        } else {
            url = `api/${resource}.php`;
            const b = new FormData();
            b.append('action', action);
            Object.entries(data).forEach(([k, v]) => b.append(k, v));
            opts = { method: 'POST', body: b };
        }
        const res = await fetch(url, opts);
        return await res.json();
    } catch (err) { return { ok: false, msg: 'Koneksi gagal: ' + err.message }; }
}

/* MODAL */
function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

function customConfirm(msg, btnText = 'Ya', btnClass = 'btn-primary', icon = '⚠️') {
    return new Promise(resolve => {
        const id = 'confirm_' + Date.now();
        const html = `
        <div class="modal-overlay open" id="${id}" style="z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);">
            <div class="modal" style="max-width: 400px; text-align: center; background:#fff; border-radius:14px; padding:30px; box-shadow:0 10px 30px rgba(0,0,0,0.2); animation:scaleIn 0.25s ease;">
                <div style="font-size: 48px; margin-bottom: 16px;">${icon}</div>
                <h3 style="font-size: 20px; font-weight: 700; color: #1e2d25; margin-bottom: 10px;">Konfirmasi</h3>
                <p style="color: #6b8070; margin-bottom: 24px; font-size:14px;">${msg}</p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button class="btn btn-outline" style="flex: 1;" id="btnCancel_${id}">Batal</button>
                    <button class="btn ${btnClass}" style="flex: 1;" id="btnOk_${id}">${btnText}</button>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', html);
        const overlay = document.getElementById(id);

        const close = (result) => {
            overlay.classList.remove('open');
            overlay.style.opacity = '0';
            setTimeout(() => overlay.remove(), 250);
            resolve(result);
        };

        document.getElementById(`btnCancel_${id}`).onclick = () => close(false);
        document.getElementById(`btnOk_${id}`).onclick = () => close(true);
    });
}


/* TABLE SEARCH */
function filterTable(q, tableId = null) {
    const rows = document.querySelectorAll(tableId ? `#${tableId} tbody tr` : 'tbody tr');
    const ql = q.toLowerCase();
    rows.forEach(r => r.style.display = r.textContent.toLowerCase().includes(ql) ? '' : 'none');
}

/* PRINT */
function printPage(type) { window.open(`print.php?type=${type}`, '_blank'); }

document.addEventListener('DOMContentLoaded', () => {

    /* MOBILE SIDEBAR */
    const menuBtn = document.getElementById('menuToggle');
    const sb = document.querySelector('.sidebar');
    const sOv = document.getElementById('sidebarOverlay');
    if (menuBtn) { menuBtn.addEventListener('click', () => { sb?.classList.toggle('open'); if (sOv) sOv.style.cssText = sb?.classList.contains('open') ? 'display:block;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;' : 'display:none;'; }); }
    if (sOv) { sOv.addEventListener('click', () => { sb?.classList.remove('open'); sOv.style.display = 'none'; }); }

    /* MODAL */
    document.querySelectorAll('.modal-overlay').forEach(ov => ov.addEventListener('click', e => { if (e.target === ov) ov.classList.remove('open'); }));
    document.querySelectorAll('.modal-close').forEach(btn => btn.addEventListener('click', () => btn.closest('.modal-overlay')?.classList.remove('open')));

    /* ENTER KEY TO LOGOUT */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const modalLogout = document.getElementById('modalLogout');
            if (modalLogout && modalLogout.classList.contains('open')) {
                window.location.href = 'logout.php';
            }
        }
    });

    /* TABLE SEARCH INPUT */
    document.querySelectorAll('#tableSearch').forEach(inp => inp.addEventListener('input', () => filterTable(inp.value)));

    /* SORTABLE HEADERS */
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.style.cursor = 'pointer'; th.title = 'Klik untuk mengurutkan';
        let asc = true;
        th.addEventListener('click', () => {
            const tbody = th.closest('table')?.querySelector('tbody');
            if (!tbody) return;
            const idx = [...th.parentElement.children].indexOf(th);
            const rows = [...tbody.querySelectorAll('tr')];
            rows.sort((a, b) => {
                const A = a.cells[idx]?.textContent.trim() || '', B = b.cells[idx]?.textContent.trim() || '';
                const nA = parseFloat(A), nB = parseFloat(B);
                if (!isNaN(nA) && !isNaN(nB)) return asc ? nA - nB : nB - nA;
                return asc ? A.localeCompare(B, 'id') : B.localeCompare(A, 'id');
            });
            rows.forEach(r => tbody.appendChild(r));
            document.querySelectorAll('th[data-sort]').forEach(t => t.textContent = t.textContent.replace(/ [▲▼]$/, ''));
            th.textContent += (asc ? ' ▲' : ' ▼'); asc = !asc;
        });
    });

    /* PASSWORD TOGGLE */
    document.querySelectorAll('.eye-toggle').forEach(btn => btn.addEventListener('click', () => {
        const inp = btn.previousElementSibling; if (!inp) return;
        inp.type = inp.type === 'password' ? 'text' : 'password';
        btn.textContent = inp.type === 'password' ? '👁️' : '🙈';
    }));

    /* DEMO ACCOUNT BUTTONS */
    document.querySelectorAll('.demo-btn').forEach(btn => btn.addEventListener('click', () => {
        const u = document.getElementById('username'), p = document.getElementById('password');
        if (u) u.value = btn.dataset.user || ''; if (p) p.value = btn.dataset.pass || '';
    }));

    /* NOTIFICATION DROPDOWN */
    const nb = document.getElementById('notifBtn'), nd = document.getElementById('notifDropdown');
    if (nb && nd) { nb.addEventListener('click', e => { e.stopPropagation(); nd.classList.toggle('show'); }); document.addEventListener('click', () => nd.classList.remove('show')); }

    /* DATE DISPLAY */
    const de = document.getElementById('currentDate');
    if (de) { const n = new Date(); const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']; const mo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']; de.textContent = `${days[n.getDay()]}, ${n.getDate()} ${mo[n.getMonth()]} ${n.getFullYear()}`; }

    /* ANIMATE KPI NUMBERS */
    document.querySelectorAll('.kpi-val').forEach(el => {
        const raw = parseInt(el.textContent.replace(/[^0-9]/g, ''));
        if (!raw || raw < 2) return;
        let cur = 0; const step = Math.ceil(raw / 32);
        const t = setInterval(() => { cur = Math.min(cur + step, raw); el.textContent = cur.toLocaleString('id-ID'); if (cur >= raw) clearInterval(t); }, 25);
    });

    /* PROGRESS BAR ANIMATION */
    document.querySelectorAll('.progress-fill').forEach(bar => {
        const w = bar.style.width; bar.style.width = '0';
        setTimeout(() => { bar.style.transition = 'width .8s ease'; bar.style.width = w; }, 150);
    });

    /* AUTO CLOSE FLASH ALERTS */
    setTimeout(() => { document.querySelectorAll('.alert').forEach(a => { a.style.transition = 'opacity .5s,max-height .5s,padding .5s,margin .5s'; a.style.opacity = '0'; a.style.maxHeight = '0'; a.style.padding = '0'; a.style.margin = '0'; }); }, 5000);

    /* UPLOAD DROP ZONE */
    document.querySelectorAll('.upload-drop-zone').forEach(zone => {
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('drag-over'); const f = e.dataTransfer.files; if (f.length) toast(`📁 ${f[0].name} siap diupload`, 'info'); });
        zone.addEventListener('click', () => zone.querySelector('input[type=file]')?.click());
    });

    /* CONFIRM DATA ATTR */
    document.querySelectorAll('[data-confirm]').forEach(btn => btn.addEventListener('click', e => { if (!confirm(btn.dataset.confirm || 'Yakin?')) e.preventDefault(); }));

    /* SAVE/RESTORE SIDEBAR SCROLL POSITION */
    const sidebarScrollContainer = document.querySelector('.sidebar');
    if (sidebarScrollContainer) {
        const storedScroll = sessionStorage.getItem('sidebarScrollPos');
        if (storedScroll !== null) {
            sidebarScrollContainer.scrollTop = parseInt(storedScroll, 10);
        }
        sidebarScrollContainer.addEventListener('scroll', () => {
            sessionStorage.setItem('sidebarScrollPos', sidebarScrollContainer.scrollTop);
        });
    }

    /* REAL-TIME NOTIFICATION POLLING */
    let lastNotifCount = -1;
    setInterval(async () => {
        try {
            const res = await fetch('api/check_notifications.php?_=' + new Date().getTime(), { cache: 'no-store' });
            const data = await res.json();
            if (data.status === 'ok') {
                if (lastNotifCount !== -1 && data.count > lastNotifCount) {
                    toast('🔔 Ada pengajuan / request baru masuk!', 'info');
                }
                
                // Update badge di lonceng atas
                const notifBtn = document.getElementById('notifBtn');
                if (notifBtn) {
                    if (data.count > 0) {
                        notifBtn.innerHTML = `🔔<span class="badge">${data.count}</span>`;
                    } else {
                        notifBtn.innerHTML = `🔔`;
                    }
                }
                
                // Update list dropdown notif
                const notifList = document.querySelector('.notif-list');
                if (notifList && data.notifs) {
                    if (data.count > 0) {
                        let html = '';
                        data.notifs.forEach(n => {
                            const bg = n[3] === 'read' ? 'background:#ddd;' : '';
                            const url = n[4] ? n[4] : '#';
                            html += `<div class="notif-item ${n[3]}" onclick="location.href='${url}'" style="cursor:pointer;"><div class="notif-dot" style="${bg}"></div><div class="notif-content"><p><i data-lucide="${n[0]}" style="width:14px;height:14px;margin-right:6px;vertical-align:-2px;"></i> ${n[1]}</p><span>${n[2]}</span></div></div>`;
                        });
                        notifList.innerHTML = html;
                        if (window.lucide) { lucide.createIcons(); }
                    } else {
                        notifList.innerHTML = `<div style="padding:15px;text-align:center;color:#6b8070;font-size:13px;">Tidak ada notifikasi baru</div>`;
                    }
                }

                // Update sidebar badges
                if (data.sidebarCounts) {
                    const mappings = {
                        'peminjaman': 'badge-peminjaman',
                        'event': 'badge-event',
                        'maint': 'badge-maint'
                    };
                    for (const [key, id] of Object.entries(mappings)) {
                        const badgeEl = document.getElementById(id);
                        if (badgeEl) {
                            const count = data.sidebarCounts[key] || 0;
                            if (count > 0) {
                                badgeEl.textContent = count;
                                badgeEl.style.display = '';
                            } else {
                                badgeEl.style.display = 'none';
                            }
                        }
                    }
                }

                lastNotifCount = data.count;
            }
        } catch (e) {
            // Ignore fetch errors
        }
    }, 3000);

    /* GLOBAL REAL-TIME DATA AUTO-REFRESH */
    setInterval(async () => {
        // Jangan refresh tabel jika sedang ada modal terbuka (misal sedang isi form)
        if (document.querySelector('.modal-overlay.open')) return;

        try {
            const sep = location.href.includes('?') ? '&' : '?';
            const htmlRes = await fetch(location.href + sep + '_=' + new Date().getTime(), { cache: 'no-store' });
            const htmlText = await htmlRes.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');
            
            // Auto-refresh Table Wrap (Berlaku untuk semua tabel di semua halaman)
            const currentTableWrap = document.querySelector('.table-wrap');
            const newTableWrap = doc.querySelector('.table-wrap');
            if (currentTableWrap && newTableWrap && currentTableWrap.innerHTML !== newTableWrap.innerHTML) {
                currentTableWrap.innerHTML = newTableWrap.innerHTML;
                
                // Aplikasikan kembali filter pencarian jika user sedang mengetik
                const searchInp = document.getElementById('tableSearch');
                if (searchInp && typeof filterTable === 'function') {
                    filterTable(searchInp.value);
                }
            }
            
            // Auto-refresh Stat Cards (Dashboard / Ringkasan)
            const currentStats = document.querySelector('.stats-grid');
            const newStats = doc.querySelector('.stats-grid');
            if (currentStats && newStats && currentStats.innerHTML !== newStats.innerHTML) {
                currentStats.innerHTML = newStats.innerHTML;
            }

            // Auto-refresh Warning Alerts (seperti pemberitahuan pending approval)
            const alertDiv = document.querySelector('div[style*="#f39c12"]');
            const newAlertDiv = doc.querySelector('div[style*="#f39c12"]');
            if (alertDiv && newAlertDiv) {
                alertDiv.outerHTML = newAlertDiv.outerHTML;
            } else if (!alertDiv && newAlertDiv) {
                const header = document.querySelector('.card-header');
                if (header) header.insertAdjacentHTML('afterend', newAlertDiv.outerHTML);
            } else if (alertDiv && !newAlertDiv) {
                alertDiv.remove();
            }

            // Update tab badges
            const currentTabs = document.querySelector('.tab-nav-bar');
            const newTabs = doc.querySelector('.tab-nav-bar');
            if (currentTabs && newTabs && currentTabs.innerHTML !== newTabs.innerHTML) {
                currentTabs.innerHTML = newTabs.innerHTML;
            }
            
        } catch (e) {}
    }, 3000);


});
