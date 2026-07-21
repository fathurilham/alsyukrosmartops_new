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

switch($action){
    case 'list': 
        $res = $conn->query("SELECT * FROM arsip");
        ok($res->fetch_all(MYSQLI_ASSOC));
    case 'get':
        $id=(int)($_GET['id']??0);
        $stmt = $conn->prepare("SELECT * FROM arsip WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($it = $stmt->get_result()->fetch_assoc()) ok($it);
        fail('Tidak ditemukan');
    case 'add':
        $nama = trim($_POST['nama']??'');
        if(!$nama) fail('Nama dokumen wajib');

        $res = $conn->query("SELECT MAX(id) as max_id FROM arsip");
        $row = $res->fetch_assoc();
        $nextId = ($row['max_id'] ?? 0) + 1;
        $kode = 'ARS-'.str_pad($nextId,3,'0',STR_PAD_LEFT);
        $kategori = $_POST['kategori']??'Administrasi';
        $tahun = (int)($_POST['tahun']??date('Y'));
        $ukuran = $_POST['ukuran']??'—';
        $tipe = $_POST['tipe']??'pdf';
        $kerahasiaan = $_POST['kerahasiaan']??'Internal';
        $uploader = $user['name'];
        $tgl_upload = date('Y-m-d');
        $keterangan = $_POST['keterangan']??'';

        $stmt = $conn->prepare("INSERT INTO arsip (kode, nama, kategori, tahun, ukuran, tipe, kerahasiaan, uploader, tgl_upload, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssissssss", $kode, $nama, $kategori, $tahun, $ukuran, $tipe, $kerahasiaan, $uploader, $tgl_upload, $keterangan);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM arsip WHERE id=?");
            $stmt->bind_param("i", $newId);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal menambah data');
    case 'delete':
        if(!in_array($role,['admin','admin_arsip'])) fail('Akses ditolak');
        $id=(int)($_POST['id']??0);
        $stmt = $conn->prepare("DELETE FROM arsip WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) ok();
        fail('Gagal hapus data');
    case 'search':
        $q = strtolower($_GET['q']??'');
        $stmt = $conn->prepare("SELECT * FROM arsip WHERE LOWER(nama) LIKE ? OR LOWER(kategori) LIKE ? OR LOWER(keterangan) LIKE ?");
        $like = "%$q%";
        $stmt->bind_param("sss", $like, $like, $like);
        $stmt->execute();
        ok($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    default: 
        $res = $conn->query("SELECT * FROM arsip");
        ok($res->fetch_all(MYSQLI_ASSOC));
}
