<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'alsyukrosmart';

$conn = new mysqli($db_host, $db_user, $db_pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

$conn->select_db($db_name);

// Tables based on crud.php JSON data
$tables = [
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL,
        unit VARCHAR(50),
        created_at DATE
    )",
    "inventory" => "CREATE TABLE IF NOT EXISTS inventory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode VARCHAR(50) NOT NULL,
        nama VARCHAR(100) NOT NULL,
        kategori VARCHAR(50) NOT NULL,
        lokasi VARCHAR(100),
        stok INT DEFAULT 0,
        status VARCHAR(50),
        keterangan TEXT,
        tgl_beli DATE
    )",
    "peminjaman" => "CREATE TABLE IF NOT EXISTS peminjaman (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode VARCHAR(50) NOT NULL,
        peminjam VARCHAR(100) NOT NULL,
        unit VARCHAR(50),
        barang VARCHAR(100) NOT NULL,
        jumlah INT DEFAULT 1,
        tgl_pinjam DATE,
        tgl_kembali DATE,
        status VARCHAR(50),
        keperluan TEXT,
        approved_by VARCHAR(100),
        tgl_dikembalikan DATE
    )",
    "karyawan" => "CREATE TABLE IF NOT EXISTS karyawan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nip VARCHAR(50) NOT NULL,
        nama VARCHAR(100) NOT NULL,
        jabatan VARCHAR(100),
        unit VARCHAR(50),
        email VARCHAR(100),
        telp VARCHAR(50),
        status VARCHAR(50),
        tgl_bergabung DATE,
        kehadiran INT DEFAULT 100,
        kinerja INT DEFAULT 90
    )",
    "maintenance" => "CREATE TABLE IF NOT EXISTS maintenance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode VARCHAR(50) NOT NULL,
        fasilitas VARCHAR(100) NOT NULL,
        jenis VARCHAR(50),
        status VARCHAR(50),
        teknisi VARCHAR(100),
        tgl_jadwal DATE,
        tgl_selesai DATE,
        prioritas VARCHAR(50),
        deskripsi TEXT,
        reporter VARCHAR(100),
        kondisi VARCHAR(50),
        maint_terakhir DATE,
        maint_berikut DATE,
        status_maint VARCHAR(50)
    )",
    "event" => "CREATE TABLE IF NOT EXISTS event (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode VARCHAR(50) NOT NULL,
        nama VARCHAR(100) NOT NULL,
        jenis VARCHAR(50),
        tgl_mulai DATE,
        tgl_selesai DATE,
        lokasi VARCHAR(100),
        status VARCHAR(50),
        personel INT DEFAULT 0,
        fasilitas TEXT,
        deskripsi TEXT,
        approved BOOLEAN DEFAULT FALSE,
        evaluasi TEXT
    )",
    "arsip" => "CREATE TABLE IF NOT EXISTS arsip (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode VARCHAR(50) NOT NULL,
        nama VARCHAR(100) NOT NULL,
        kategori VARCHAR(50),
        tahun INT,
        ukuran VARCHAR(50),
        tipe VARCHAR(20),
        kerahasiaan VARCHAR(50),
        uploader VARCHAR(100),
        tgl_upload DATE,
        keterangan TEXT
    )",
    "absensi" => "CREATE TABLE IF NOT EXISTS absensi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        unit VARCHAR(50),
        tanggal DATE,
        masuk VARCHAR(10),
        keluar VARCHAR(10),
        status VARCHAR(50),
        keterangan TEXT
    )",
    "aktivitas" => "CREATE TABLE IF NOT EXISTS aktivitas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        unit VARCHAR(50),
        jenis VARCHAR(50),
        deskripsi TEXT,
        tanggal DATE,
        jam VARCHAR(10)
    )",
    "request_maint" => "CREATE TABLE IF NOT EXISTS request_maint (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode VARCHAR(50) NOT NULL,
        pemohon VARCHAR(100) NOT NULL,
        unit VARCHAR(50),
        fasilitas VARCHAR(100),
        masalah TEXT,
        prioritas VARCHAR(50),
        status VARCHAR(50),
        tgl_request DATE,
        catatan_teknisi TEXT
    )"
];

foreach ($tables as $name => $query) {
    if ($conn->query($query) === TRUE) {
        echo "Table $name created successfully\n";
    } else {
        echo "Error creating table $name: " . $conn->error . "\n";
    }
}

// Seed default users if table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    echo "Seeding default users...\n";
    $default_users = [
        ['Administrator', 'admin', 'admin@alsyukro.sch.id', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 'IT', date('Y-m-d')],
        ['Budi Santoso', 'staff1', 'budi@alsyukro.sch.id', password_hash('staff123', PASSWORD_DEFAULT), 'staff', 'Umum', date('Y-m-d')],
        ['Siti Rahma', 'karyawan1', 'siti@alsyukro.sch.id', password_hash('karya123', PASSWORD_DEFAULT), 'karyawan', 'Keuangan', date('Y-m-d')],
        ['HR Manager', 'adminhr', 'hr@alsyukro.sch.id', password_hash('hr123456', PASSWORD_DEFAULT), 'admin_hr', 'HR', date('Y-m-d')],
        ['Event Organizer', 'eo', 'eo@alsyukro.sch.id', password_hash('eo123456', PASSWORD_DEFAULT), 'eo', 'Event', date('Y-m-d')],
        ['Admin Fasilitas', 'adminfas', 'fasilitas@alsyukro.sch.id', password_hash('fas12345', PASSWORD_DEFAULT), 'admin_fasilitas', 'Fasilitas', date('Y-m-d')],
        ['Teknisi Utama', 'teknisi', 'teknisi@alsyukro.sch.id', password_hash('teks1234', PASSWORD_DEFAULT), 'teknisi', 'Teknik', date('Y-m-d')],
        ['Kepala Sekolah', 'manager', 'kepala@alsyukro.sch.id', password_hash('mgr12345', PASSWORD_DEFAULT), 'manager', 'Pimpinan', date('Y-m-d')],
        ['Admin Arsip', 'adminarsip', 'arsip@alsyukro.sch.id', password_hash('arsip123', PASSWORD_DEFAULT), 'admin_arsip', 'Arsip', date('Y-m-d')]
    ];
    $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, role, unit, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($default_users as $u) {
        $stmt->bind_param("sssssss", $u[0], $u[1], $u[2], $u[3], $u[4], $u[5], $u[6]);
        $stmt->execute();
    }
    echo "Default users seeded successfully\n";
}
$conn->close();
?>
