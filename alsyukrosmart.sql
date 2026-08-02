-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 21 Jul 2026 pada 10.57
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alsyukrosmart`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `masuk` varchar(10) DEFAULT NULL,
  `keluar` varchar(10) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `aktivitas`
--

CREATE TABLE `aktivitas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `aktivitas`
--

INSERT INTO `aktivitas` (`id`, `nama`, `unit`, `jenis`, `deskripsi`, `tanggal`, `jam`) VALUES
(1, 'Budi Santoso', 'Umum', 'Rapat', 'Rapat persiapan acara HUT RI ke 83', '2026-07-16', '19:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `arsip`
--

CREATE TABLE `arsip` (
  `id` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `tahun` int(11) DEFAULT NULL,
  `ukuran` varchar(50) DEFAULT NULL,
  `tipe` varchar(20) DEFAULT NULL,
  `kerahasiaan` varchar(50) DEFAULT NULL,
  `uploader` varchar(100) DEFAULT NULL,
  `tgl_upload` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `event`
--

CREATE TABLE `event` (
  `id` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `personel` int(11) DEFAULT 0,
  `fasilitas` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `evaluasi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `tgl_beli` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `inventory`
--

INSERT INTO `inventory` (`id`, `kode`, `nama`, `kategori`, `lokasi`, `stok`, `status`, `keterangan`, `tgl_beli`) VALUES
(9, 'INV-001', 'Monitor', 'Elektronik', 'Lab Komputer', 30, 'tersedia', '', '2026-07-17'),
(10, 'INV-010', 'CPU', 'Elektronik', 'Lab Komputer', 30, 'tersedia', '', '2026-07-17'),
(11, 'INV-011', 'Keyboard', 'Elektronik', 'Lab Komputer', 30, 'tersedia', '', '2026-07-17'),
(12, 'INV-012', 'Proyektor', 'Elektronik', 'Lab Komputer', 8, 'tersedia', '', '2026-07-17'),
(13, 'INV-013', 'Kursi Futura', 'Furnitur', 'Unit SD', 50, 'tersedia', '', '2026-07-17'),
(14, 'INV-014', 'Kursi Futura', 'Furnitur', 'Unit SMP', 30, 'tersedia', '', '2026-07-17'),
(15, 'INV-015', 'Kursi Futura', 'Furnitur', 'Unit SMA', 25, 'tersedia', '', '2026-07-17'),
(16, 'INV-016', 'HVS SD', 'ATK', 'Gudang Sapras', 50, 'tersedia', '', '2026-07-17'),
(17, 'INV-017', 'HVS', 'ATK', 'Logistik', 30, 'tersedia', '', '2026-07-17'),
(18, 'INV-018', 'Screen Proyektor', 'Elektronik', 'Lab Komputer', 5, 'tersedia', '', '2026-07-17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `karyawan`
--

CREATE TABLE `karyawan` (
  `id` int(11) NOT NULL,
  `nip` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telp` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `tgl_bergabung` date DEFAULT NULL,
  `kehadiran` int(11) DEFAULT 100,
  `kinerja` int(11) DEFAULT 90
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `karyawan`
--

INSERT INTO `karyawan` (`id`, `nip`, `nama`, `jabatan`, `unit`, `email`, `telp`, `status`, `tgl_bergabung`, `kehadiran`, `kinerja`) VALUES
(1, 'KRY-001', 'Budi Santoso', 'Staff', 'Arsip', 'budi422@gmail.com', '085423199744', 'aktif', '2026-07-14', 100, 90);

-- --------------------------------------------------------

--
-- Struktur dari tabel `maintenance`
--

CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `fasilitas` varchar(100) NOT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `teknisi` varchar(100) DEFAULT NULL,
  `tgl_jadwal` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `prioritas` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `reporter` varchar(100) DEFAULT NULL,
  `kondisi` varchar(50) DEFAULT NULL,
  `maint_terakhir` date DEFAULT NULL,
  `maint_berikut` date DEFAULT NULL,
  `status_maint` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `peminjam` varchar(100) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `barang` varchar(100) NOT NULL,
  `jumlah` int(11) DEFAULT 1,
  `tgl_pinjam` date DEFAULT NULL,
  `tgl_kembali` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `keperluan` text DEFAULT NULL,
  `approved_by` varchar(100) DEFAULT NULL,
  `tgl_dikembalikan` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `request_maint`
--

CREATE TABLE `request_maint` (
  `id` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `pemohon` varchar(100) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `fasilitas` varchar(100) DEFAULT NULL,
  `masalah` text DEFAULT NULL,
  `prioritas` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `tgl_request` date DEFAULT NULL,
  `catatan_teknisi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `created_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `unit`, `created_at`) VALUES
(1, 'Administrator', 'admin', 'admin@alsyukro.sch.id', '$2y$10$wESOqXz3Jy9z50msduWuLOdDhX5vRmAxi/RuJO4C/xPoSph/Jiw7y', 'admin', 'IT', '2026-07-13'),
(2, 'Budi Santoso', 'staff1', 'budi@alsyukro.sch.id', '$2y$10$FgAGLHqmu8w05kqf6ERKn.zN305OO50DJB91TE9dZdXhsFafMm1tW', 'staff', 'Umum', '2026-07-13'),
(3, 'Siti Rahma', 'karyawan1', 'siti@alsyukro.sch.id', '$2y$10$KeKGVUWH8JFtDWwU2Jdr0.zTLLALqvhE.x8IascjotHn6/yJqD3dS', 'karyawan', 'Keuangan', '2026-07-13'),
(4, 'HR Manager', 'adminhr', 'hr@alsyukro.sch.id', '$2y$10$lriFS549Ag9jUdPsYPdWZOPh3WiYGDpHgdLwoA9IclcqhIKfcdpxC', 'admin_hr', 'HR', '2026-07-13'),
(5, 'Event Organizer', 'eo', 'eo@alsyukro.sch.id', '$2y$10$ML6l/UWxlSlz1CH2KQuNJO3My.G7NQATPL2dR02aJGNGDem9WkIl2', 'eo', 'Event', '2026-07-13'),
(6, 'Admin Fasilitas', 'adminfas', 'fasilitas@alsyukro.sch.id', '$2y$10$txejN4mD36D4s4TfYEtatORtDRnh6QU/PcoFomIB0q18rXNBqQDFa', 'admin_fasilitas', 'Fasilitas', '2026-07-13'),
(7, 'Teknisi Utama', 'teknisi', 'teknisi@alsyukro.sch.id', '$2y$10$nioK352TV6tT5O5N/yvuEeg5R1fgrhzgZGDsvmSR5kw9sRKUhvzP2', 'teknisi', 'Teknik', '2026-07-13'),
(8, 'Kepala Sekolah', 'manager', 'kepala@alsyukro.sch.id', '$2y$10$NjHTb6HXBMMiFNe2AL2p6u74UWAE8zENliCsfJ30EGR1pWJZohnHu', 'manager', 'Pimpinan', '2026-07-13'),
(9, 'Admin Arsip', 'adminarsip', 'arsip@alsyukro.sch.id', '$2y$10$eScFR//PiHaqDe0s1WPwluxGdTcxZT9Z3tky8sbK3jKc6AHxJ6ILC', 'admin_arsip', 'Arsip', '2026-07-13');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `arsip`
--
ALTER TABLE `arsip`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `request_maint`
--
ALTER TABLE `request_maint`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `aktivitas`
--
ALTER TABLE `aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `arsip`
--
ALTER TABLE `arsip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `event`
--
ALTER TABLE `event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `request_maint`
--
ALTER TABLE `request_maint`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
