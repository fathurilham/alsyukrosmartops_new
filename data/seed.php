<?php
require_once __DIR__ . '/../config/database.php';

// Run once to seed database
$seed_data = [];


// INVENTORY
$seed_data['inventory'] = [
    ['id'=>1,'kode'=>'INV-001','nama'=>'Proyektor Epson EB-X51','kategori'=>'Elektronik','lokasi'=>'Lab Komputer','stok'=>5,'status'=>'tersedia','keterangan'=>'Kondisi baik','tgl_beli'=>'2023-01-15'],
    ['id'=>2,'kode'=>'INV-002','nama'=>'Laptop Lenovo IdeaPad','kategori'=>'Elektronik','lokasi'=>'Gudang IT','stok'=>12,'status'=>'tersedia','keterangan'=>'Siap pakai','tgl_beli'=>'2023-03-20'],
    ['id'=>3,'kode'=>'INV-003','nama'=>'Meja Rapat Panjang','kategori'=>'Furnitur','lokasi'=>'Aula Utama','stok'=>3,'status'=>'dipinjam','keterangan'=>'','tgl_beli'=>'2022-06-01'],
    ['id'=>4,'kode'=>'INV-004','nama'=>'Kursi Plastik','kategori'=>'Furnitur','lokasi'=>'Gudang','stok'=>100,'status'=>'tersedia','keterangan'=>'','tgl_beli'=>'2022-01-10'],
    ['id'=>5,'kode'=>'INV-005','nama'=>'Sound System JBL','kategori'=>'Audio','lokasi'=>'Aula Utama','stok'=>2,'status'=>'dipinjam','keterangan'=>'','tgl_beli'=>'2023-05-12'],
    ['id'=>6,'kode'=>'INV-006','nama'=>'Whiteboard Besar','kategori'=>'ATK','lokasi'=>'Ruang Guru','stok'=>8,'status'=>'tersedia','keterangan'=>'','tgl_beli'=>'2022-08-20'],
    ['id'=>7,'kode'=>'INV-007','nama'=>'Kamera DSLR Canon','kategori'=>'Kamera','lokasi'=>'Studio','stok'=>3,'status'=>'tersedia','keterangan'=>'Dengan tripod','tgl_beli'=>'2023-02-14'],
    ['id'=>8,'kode'=>'INV-008','nama'=>'Genset 5KVA','kategori'=>'Mesin','lokasi'=>'Gudang','stok'=>1,'status'=>'maintenance','keterangan'=>'Dalam perawatan','tgl_beli'=>'2021-11-05'],
];

// PEMINJAMAN
$seed_data['peminjaman'] = [
    ['id'=>1,'kode'=>'PJM-001','peminjam'=>'Budi Santoso','unit'=>'TU','barang'=>'Proyektor Epson EB-X51','inv_id'=>1, 'jumlah'=>1,'tgl_pinjam'=>'2025-06-01','tgl_kembali'=>'2025-06-05','status'=>'dipinjam','keperluan'=>'Presentasi rapat','approved_by'=>'Admin'],
    ['id'=>2,'kode'=>'PJM-002','peminjam'=>'Siti Rahma','unit'=>'Keuangan','barang'=>'Laptop Lenovo IdeaPad','inv_id'=>2, 'jumlah'=>2,'tgl_pinjam'=>'2025-06-02','tgl_kembali'=>'2025-06-07','status'=>'pending','keperluan'=>'Kerja lapangan','approved_by'=>''],
    ['id'=>3,'kode'=>'PJM-003','peminjam'=>'Rudi Hermawan','unit'=>'IT','barang'=>'Sound System JBL','inv_id'=>5, 'jumlah'=>1,'tgl_pinjam'=>'2025-06-03','tgl_kembali'=>'2025-06-04','status'=>'pending','keperluan'=>'Acara pensi','approved_by'=>''],
    ['id'=>4,'kode'=>'PJM-004','peminjam'=>'Ahmad Fauzi','unit'=>'Kurikulum','barang'=>'Kamera DSLR Canon','inv_id'=>7, 'jumlah'=>1,'tgl_pinjam'=>'2025-05-28','tgl_kembali'=>'2025-05-30','status'=>'dikembalikan','keperluan'=>'Dokumentasi','approved_by'=>'Admin'],
];

// KARYAWAN
$seed_data['karyawan'] = [
    ['id'=>1,'nip'=>'KRY-001','nama'=>'Budi Santoso','jabatan'=>'Staff TU','unit'=>'Tata Usaha','email'=>'budi@alsyukro.sch.id','telp'=>'08111111111','tgl_bergabung'=>'2020-01-15','status'=>'aktif'],
    ['id'=>2,'nip'=>'KRY-002','nama'=>'Siti Rahma','jabatan'=>'Bendahara','unit'=>'Keuangan','email'=>'siti@alsyukro.sch.id','telp'=>'08222222222','tgl_bergabung'=>'2019-08-01','status'=>'aktif'],
    ['id'=>3,'nip'=>'KRY-003','nama'=>'Ahmad Fauzi','jabatan'=>'Guru Matematika','unit'=>'Kurikulum','email'=>'ahmad@alsyukro.sch.id','telp'=>'08333333333','tgl_bergabung'=>'2018-07-01','status'=>'aktif'],
    ['id'=>4,'nip'=>'KRY-004','nama'=>'Dewi Lestari','jabatan'=>'Guru Bahasa','unit'=>'Kurikulum','email'=>'dewi@alsyukro.sch.id','telp'=>'08444444444','tgl_bergabung'=>'2021-01-03','status'=>'aktif'],
    ['id'=>5,'nip'=>'KRY-005','nama'=>'Rudi Hermawan','jabatan'=>'Teknisi IT','unit'=>'IT','email'=>'rudi@alsyukro.sch.id','telp'=>'08555555555','tgl_bergabung'=>'2022-03-15','status'=>'aktif'],
    ['id'=>6,'nip'=>'KRY-006','nama'=>'Nia Sari','jabatan'=>'Koordinator Event','unit'=>'Event','email'=>'nia@alsyukro.sch.id','telp'=>'08666666666','tgl_bergabung'=>'2020-05-20','status'=>'cuti'],
];

// ABSENSI
$today = date('Y-m-d');
$seed_data['absensi'] = [
    ['id'=>1,'karyawan_id'=>1, 'nama'=>'Budi Santoso','unit'=>'Tata Usaha','tanggal'=>$today,'masuk'=>'07:45','keluar'=>'16:15','status'=>'hadir','keterangan'=>''],
    ['id'=>2,'karyawan_id'=>2, 'nama'=>'Siti Rahma','unit'=>'Keuangan','tanggal'=>$today,'masuk'=>'08:02','keluar'=>'16:00','status'=>'hadir','keterangan'=>''],
    ['id'=>3,'karyawan_id'=>3, 'nama'=>'Ahmad Fauzi','unit'=>'Kurikulum','tanggal'=>$today,'masuk'=>'07:30','keluar'=>'15:45','status'=>'hadir','keterangan'=>''],
    ['id'=>4,'karyawan_id'=>4, 'nama'=>'Dewi Lestari','unit'=>'Kurikulum','tanggal'=>$today,'masuk'=>'','keluar'=>'','status'=>'izin','keterangan'=>'Sakit - Surat dokter'],
    ['id'=>5,'karyawan_id'=>5, 'nama'=>'Rudi Hermawan','unit'=>'IT','tanggal'=>$today,'masuk'=>'08:15','keluar'=>'16:30','status'=>'hadir','keterangan'=>''],
    ['id'=>6,'karyawan_id'=>6, 'nama'=>'Nia Sari','unit'=>'Event','tanggal'=>$today,'masuk'=>'','keluar'=>'','status'=>'cuti','keterangan'=>'Cuti tahunan'],
];

// EVENT
$seed_data['event'] = [
    ['id'=>1,'kode'=>'EVT-001','nama'=>'Wisuda Angkatan 2025','jenis'=>'Akademik','tanggal_mulai'=>'2025-06-15','tanggal_selesai'=>'2025-06-15','lokasi'=>'Aula Utama','personel'=>25,'status'=>'persiapan','deskripsi'=>'Wisuda kelulusan siswa angkatan 2025','created_by'=>'Event Organizer','fasilitas'=>'Proyektor, Sound System, Meja'],
    ['id'=>2,'kode'=>'EVT-002','nama'=>'Pesantren Kilat Ramadan','jenis'=>'Non-Akademik','tanggal_mulai'=>'2025-07-01','tanggal_selesai'=>'2025-07-05','lokasi'=>'Masjid Al-Syukro','personel'=>15,'status'=>'persiapan','deskripsi'=>'Pesantren kilat selama 5 hari','created_by'=>'Event Organizer','fasilitas'=>'Sound System, Proyektor'],
    ['id'=>3,'kode'=>'EVT-003','nama'=>'Rapat Kerja Tahunan','jenis'=>'Internal','tanggal_mulai'=>'2025-06-10','tanggal_selesai'=>'2025-06-10','lokasi'=>'Ruang Rapat','personel'=>30,'status'=>'berlangsung','deskripsi'=>'Rapat kerja evaluasi tahunan','created_by'=>'Event Organizer','fasilitas'=>'Proyektor, Laptop'],
    ['id'=>4,'kode'=>'EVT-004','nama'=>'Seminar Pendidikan 2025','jenis'=>'Eksternal','tanggal_mulai'=>'2025-05-20','tanggal_selesai'=>'2025-05-20','lokasi'=>'Aula Utama','personel'=>18,'status'=>'selesai','deskripsi'=>'Seminar nasional pendidikan','created_by'=>'Event Organizer','fasilitas'=>'Full AV'],
    ['id'=>5,'kode'=>'EVT-005','nama'=>'Peringatan HUT Sekolah','jenis'=>'Non-Akademik','tanggal_mulai'=>'2025-04-15','tanggal_selesai'=>'2025-04-15','lokasi'=>'Seluruh Sekolah','personel'=>50,'status'=>'selesai','deskripsi'=>'HUT ke-30 Al-Syukro Universal','created_by'=>'Event Organizer','fasilitas'=>'Full'],
];

// MAINTENANCE
$seed_data['maintenance'] = [
    ['id'=>1,'kode'=>'FAS-001','nama'=>'AC Ruang Guru','lokasi'=>'Ruang Guru','kondisi'=>'baik','maint_terakhir'=>'2025-05-01','maint_berikut'=>'2025-08-01','teknisi'=>'Rudi Hermawan','status_maint'=>'terjadwal'],
    ['id'=>2,'kode'=>'FAS-002','nama'=>'AC Lab Komputer','lokasi'=>'Lab IPA','kondisi'=>'perbaikan','maint_terakhir'=>'2025-04-15','maint_berikut'=>'2025-06-15','teknisi'=>'Tim Teknik','status_maint'=>'dalam_proses'],
    ['id'=>3,'kode'=>'FAS-003','nama'=>'Genset Cadangan','lokasi'=>'Gudang','kondisi'=>'baik','maint_terakhir'=>'2025-05-15','maint_berikut'=>'2025-07-15','teknisi'=>'Rudi Hermawan','status_maint'=>'terjadwal'],
    ['id'=>4,'kode'=>'FAS-004','nama'=>'CCTV Koridor','lokasi'=>'Seluruh Gedung','kondisi'=>'maintenance','maint_terakhir'=>'2025-03-01','maint_berikut'=>'2025-06-01','teknisi'=>'Tim Teknik','status_maint'=>'dalam_proses'],
    ['id'=>5,'kode'=>'FAS-005','nama'=>'Pompa Air','lokasi'=>'Ruang Pompa','kondisi'=>'perbaikan','maint_terakhir'=>'2025-02-01','maint_berikut'=>'2025-05-01','teknisi'=>'Teknisi Luar','status_maint'=>'dalam_proses'],
];

// REQUEST MAINTENANCE
$seed_data['request_maint'] = [
    ['id'=>1,'kode'=>'REQ-001','pengaju'=>'Budi Santoso','unit'=>'TU','fasilitas'=>'AC Ruang Kepala','masalah'=>'AC bocor dan tidak dingin','prioritas'=>'tinggi','status'=>'menunggu','tgl_request'=>'2025-06-07'],
    ['id'=>2,'kode'=>'REQ-002','pengaju'=>'Dewi Lestari','unit'=>'Kurikulum','fasilitas'=>'Lampu Aula','masalah'=>'Lampu aula mati separuh','prioritas'=>'normal','status'=>'diproses','tgl_request'=>'2025-06-06'],
    ['id'=>3,'kode'=>'REQ-003','pengaju'=>'Ahmad Fauzi','unit'=>'Kurikulum','fasilitas'=>'Kran Air','masalah'=>'Kran air rusak bocor','prioritas'=>'rendah','status'=>'selesai','tgl_request'=>'2025-06-05'],
];

// ARSIP
$seed_data['arsip'] = [
    ['id'=>1,'kode'=>'ARS-001','nama'=>'SK Pengangkatan Guru 2024','kategori'=>'SDM','tahun'=>2024,'ukuran'=>'2.4 MB','tipe'=>'pdf','kerahasiaan'=>'internal','upload_by'=>'Admin Arsip','tgl_upload'=>'2024-12-01','keterangan'=>'Surat keputusan pengangkatan'],
    ['id'=>2,'kode'=>'ARS-002','nama'=>'Laporan Keuangan Q1 2025','kategori'=>'Keuangan','tahun'=>2025,'ukuran'=>'1.8 MB','tipe'=>'xlsx','kerahasiaan'=>'rahasia','upload_by'=>'Admin Arsip','tgl_upload'=>'2025-04-10','keterangan'=>'Laporan Q1'],
    ['id'=>3,'kode'=>'ARS-003','nama'=>'Foto Wisuda Angkatan 2024','kategori'=>'Dokumentasi','tahun'=>2024,'ukuran'=>'450 MB','tipe'=>'zip','kerahasiaan'=>'publik','upload_by'=>'EO','tgl_upload'=>'2024-11-20','keterangan'=>'Dokumentasi wisuda'],
    ['id'=>4,'kode'=>'ARS-004','nama'=>'Kurikulum Tahun Ajaran 2024','kategori'=>'Akademik','tahun'=>2024,'ukuran'=>'3.2 MB','tipe'=>'pdf','kerahasiaan'=>'internal','upload_by'=>'Admin Arsip','tgl_upload'=>'2024-07-01','keterangan'=>'Kurikulum resmi'],
    ['id'=>5,'kode'=>'ARS-005','nama'=>'Notulen Rapat Kerja 2025','kategori'=>'Administrasi','tahun'=>2025,'ukuran'=>'0.8 MB','tipe'=>'docx','kerahasiaan'=>'internal','upload_by'=>'Staff','tgl_upload'=>'2025-01-20','keterangan'=>'Notulen rapat kerja awal tahun'],
];

// AKTIVITAS
$seed_data['aktivitas'] = [
    ['id'=>1,'karyawan_id'=>1,'nama'=>'Budi Santoso','tanggal'=>'2025-06-07','jenis'=>'Koordinasi','deskripsi'=>'Rapat koordinasi kurikulum semester baru'],
    ['id'=>2,'karyawan_id'=>2,'nama'=>'Siti Rahma','tanggal'=>'2025-06-06','jenis'=>'Administrasi','deskripsi'=>'Upload dokumen RAB kegiatan'],
    ['id'=>3,'karyawan_id'=>3,'nama'=>'Ahmad Fauzi','tanggal'=>'2025-06-05','jenis'=>'Pengajaran','deskripsi'=>'Monitoring absensi siswa kelas XII'],
];


foreach ($seed_data as $table => $rows) {
    if (empty($rows)) continue;
    $conn->query("TRUNCATE TABLE $table");
    
    $result = $conn->query("SHOW COLUMNS FROM $table");
    $valid_columns = [];
    while ($row = $result->fetch_assoc()) {
        $valid_columns[] = $row['Field'];
    }
    
    $columns = array_intersect(array_keys($rows[0]), $valid_columns);
    $columns = array_values($columns);
    
    $colList = implode(', ', $columns);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    
    $stmt = $conn->prepare("INSERT INTO $table ($colList) VALUES ($placeholders)");
    
    foreach ($rows as $row) {
        $types = '';
        $values = [];
        foreach ($columns as $col) {
            $val = $row[$col];
            if (is_int($val)) { $types .= 'i'; }
            elseif (is_float($val)) { $types .= 'd'; }
            else { $types .= 's'; }
            $values[] = $val;
        }
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
    }
    echo "Seeded table $table with " . count($rows) . " rows.\n";
}
echo "Semua data berhasil di-seed ke database!\n";
?>
