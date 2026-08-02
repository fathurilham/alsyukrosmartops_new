<?php
/**
 * Script untuk mereset dan mengurutkan ulang kode peminjaman
 * agar berurutan tanpa jeda (PJM-001, PJM-002, dst).
 */
require_once 'config/database.php';

$res = $conn->query("SELECT id FROM peminjaman ORDER BY id ASC");
if ($res) {
    $i = 1;
    $count = 0;
    while($row = $res->fetch_assoc()) {
        $new_kode = 'PJM-' . str_pad($i, 3, '0', STR_PAD_LEFT);
        $conn->query("UPDATE peminjaman SET kode='$new_kode' WHERE id={$row['id']}");
        $i++;
        $count++;
    }
    echo "<h3>Selesai!</h3>";
    echo "<p>Berhasil mengatur ulang $count data peminjaman agar kodenya berurutan dari PJM-001 hingga PJM-" . str_pad($count, 3, '0', STR_PAD_LEFT) . ".</p>";
    echo "<p><a href='index.php'>Kembali ke Dashboard</a> atau <a href='pages/inventory.php?sub=approval'>Lihat Persetujuan Peminjaman</a></p>";
} else {
    echo "Gagal mengambil data peminjaman: " . $conn->error;
}
