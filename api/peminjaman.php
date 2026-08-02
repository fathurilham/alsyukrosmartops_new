<?php
/**
 * AL-SYUKROSMART OPS — CRUD API Handler
 * Semua operasi CRUD via MySQL, dikirim lewat fetch/form POST
 */
require_once '../config/auth.php';
require_once '../config/database.php';
requireLogin();

header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$user = getCurrentUser();
$role = $user['role'];

$action   = $_POST['action'] ?? $_GET['action'] ?? '';

if (!function_exists('ok')) {
    function ok($data=[]) { echo json_encode(['ok'=>true,'data'=>$data]); exit(); }
}
if (!function_exists('fail')) {
    function fail($msg)   { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$msg]); exit(); }
}

switch($action){
    case 'list': 
        $res = $conn->query("SELECT * FROM peminjaman ORDER BY CAST(SUBSTRING(kode, 5) AS UNSIGNED) ASC");
$data = $res->fetch_all(MYSQLI_ASSOC); usort($data, function($a, $b) { return (int)substr($a["kode"], 4) - (int)substr($b["kode"], 4); }); ok($data);
    case 'add':
        $barang = $_POST['barang']??'';
        $tgl_kembali = $_POST['tgl_kembali']??'';
        if(!$barang || !$tgl_kembali) fail('Data tidak lengkap');

        $res = $conn->query("SELECT MAX(CAST(SUBSTRING(kode, 5) AS UNSIGNED)) as max_num FROM peminjaman");
        $row = $res->fetch_assoc();
        $nextId = ($row['max_num'] ?? 0) + 1;
        $kode = 'PJM-'.str_pad($nextId,3,'0',STR_PAD_LEFT);
        $peminjam = $user['name'];
        $unit = $user['unit']??'';
        $jumlah = (int)($_POST['jumlah']??1);
        $tgl_pinjam = date('Y-m-d');
        $status = 'pending';
        $keperluan = $_POST['keperluan']??'';
        $approved_by = '';
        
        $stmt = $conn->prepare("INSERT INTO peminjaman (kode, peminjam, unit, barang, jumlah, tgl_pinjam, tgl_kembali, status, keperluan, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssisssss", $kode, $peminjam, $unit, $barang, $jumlah, $tgl_pinjam, $tgl_kembali, $status, $keperluan, $approved_by);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id=?");
            $stmt->bind_param("i", $newId);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal menambah data');
    case 'approve':
        if(!in_array($role,['admin','manager','staff','karyawan'])) fail('Akses ditolak');
        $id=(int)($_POST['id']??0);
        $newStatus = $_POST['status'] ?? 'dipinjam';
        
        $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $it = $stmt->get_result()->fetch_assoc();
        if (!$it) fail('Tidak ditemukan');
        
        $approved_by = ($newStatus === 'dipinjam') ? $user['name'] : $it['approved_by'];
        $tgl_dikembalikan = ($newStatus === 'dikembalikan') ? date('Y-m-d') : ($it['tgl_dikembalikan']??null);
        
        $stmt = $conn->prepare("UPDATE peminjaman SET status=?, approved_by=?, tgl_dikembalikan=? WHERE id=?");
        $stmt->bind_param("sssi", $newStatus, $approved_by, $tgl_dikembalikan, $id);
        if ($stmt->execute()) {
            $oldStatus = $it['status'];
            if ($newStatus === 'dipinjam' && $oldStatus !== 'dipinjam') {
                $stmtStock = $conn->prepare("UPDATE inventory SET stok = GREATEST(0, stok - ?) WHERE nama = ?");
                $stmtStock->bind_param("is", $it['jumlah'], $it['barang']);
                $stmtStock->execute();
            } else if ($newStatus === 'dikembalikan' && $oldStatus === 'dipinjam') {
                $stmtStock = $conn->prepare("UPDATE inventory SET stok = stok + ? WHERE nama = ?");
                $stmtStock->bind_param("is", $it['jumlah'], $it['barang']);
                $stmtStock->execute();
            }
            $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal update');
    case 'reject':
        if(!in_array($role,['admin','manager'])) fail('Akses ditolak');
        $id=(int)($_POST['id']??0);
        $stmt = $conn->prepare("UPDATE peminjaman SET status='ditolak' WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Tidak ditemukan');
    case 'return':
        $id=(int)($_POST['id']??0);
        $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $it = $stmt->get_result()->fetch_assoc();
        if (!$it || $it['status'] === 'dikembalikan') fail('Tidak valid');

        $stmt = $conn->prepare("UPDATE peminjaman SET status='dikembalikan', tgl_dikembalikan=CURRENT_DATE WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if ($it['status'] === 'dipinjam') {
                $stmtStock = $conn->prepare("UPDATE inventory SET stok = stok + ? WHERE nama = ?");
                $stmtStock->bind_param("is", $it['jumlah'], $it['barang']);
                $stmtStock->execute();
            }
            $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Tidak ditemukan');
    default: 
        $res = $conn->query("SELECT * FROM peminjaman ORDER BY CAST(SUBSTRING(kode, 5) AS UNSIGNED) ASC");
$data = $res->fetch_all(MYSQLI_ASSOC); usort($data, function($a, $b) { return (int)substr($a["kode"], 4) - (int)substr($b["kode"], 4); }); ok($data);
}
