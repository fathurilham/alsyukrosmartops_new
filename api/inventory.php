<?php
/**
 * AL-SYUKROSMART OPS — CRUD API Handler
 * Semua operasi CRUD via MySQL, dikirim lewat fetch/form POST
 */
require_once '../config/auth.php';
require_once '../config/database.php';
requireLogin();

header('Content-Type: application/json');
$user = getCurrentUser();
$role = $user['role'];

$action   = $_POST['action'] ?? $_GET['action'] ?? '';

if (!function_exists('ok')) {
    function ok($data=[]) { echo json_encode(['ok'=>true,'data'=>$data]); exit(); }
}
if (!function_exists('fail')) {
    function fail($msg)   { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$msg]); exit(); }
}

switch($action) {
    case 'list':
        $res = $conn->query("SELECT * FROM inventory");
        $items = $res->fetch_all(MYSQLI_ASSOC);
        ok($items);
    case 'get':
        $id = (int)($_GET['id']??0);
        $stmt = $conn->prepare("SELECT * FROM inventory WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($it = $res->fetch_assoc()) ok($it);
        fail('Tidak ditemukan');
    case 'add':
        if (!in_array($role,['admin','admin_fasilitas'])) fail('Akses ditolak');
        $nama = trim($_POST['nama']??'');
        if (!$nama) fail('Nama wajib diisi');
        
        $res = $conn->query("SELECT MAX(id) as max_id FROM inventory");
        $row = $res->fetch_assoc();
        $nextId = ($row['max_id'] ?? 0) + 1;
        $kode = 'INV-'.str_pad($nextId,3,'0',STR_PAD_LEFT);
        $kategori = $_POST['kategori']??'Lainnya';
        $lokasi = $_POST['lokasi']??'';
        $stok = (int)($_POST['stok']??0);
        $status = 'tersedia';
        $keterangan = $_POST['keterangan']??'';
        $tgl_beli = $_POST['tgl_beli']??date('Y-m-d');
        
        $stmt = $conn->prepare("INSERT INTO inventory (kode, nama, kategori, lokasi, stok, status, keterangan, tgl_beli) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssisss", $kode, $nama, $kategori, $lokasi, $stok, $status, $keterangan, $tgl_beli);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM inventory WHERE id=?");
            $stmt->bind_param("i", $newId);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal menambah data');
    case 'edit':
        if (!in_array($role,['admin','admin_fasilitas'])) fail('Akses ditolak');
        $id = (int)($_POST['id']??0);
        
        $stmt = $conn->prepare("SELECT * FROM inventory WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $it = $stmt->get_result()->fetch_assoc();
        if (!$it) fail('Tidak ditemukan');
        
        $nama = $_POST['nama'] ?? $it['nama'];
        $kategori = $_POST['kategori'] ?? $it['kategori'];
        $lokasi = $_POST['lokasi'] ?? $it['lokasi'];
        $stok = isset($_POST['stok']) ? (int)$_POST['stok'] : $it['stok'];
        $status = $_POST['status'] ?? $it['status'];
        $keterangan = $_POST['keterangan'] ?? $it['keterangan'];
        
        $stmt = $conn->prepare("UPDATE inventory SET nama=?, kategori=?, lokasi=?, stok=?, status=?, keterangan=? WHERE id=?");
        $stmt->bind_param("sssisss", $nama, $kategori, $lokasi, $stok, $status, $keterangan, $id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("SELECT * FROM inventory WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal update data');
    case 'delete':
        if ($role !== 'admin') fail('Akses ditolak');
        $id = (int)($_POST['id']??0);
        $stmt = $conn->prepare("DELETE FROM inventory WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) ok();
        fail('Gagal menghapus data');
    default: 
        $res = $conn->query("SELECT * FROM inventory");
        ok($res->fetch_all(MYSQLI_ASSOC));
}
