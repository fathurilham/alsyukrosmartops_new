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
        $res = $conn->query("SELECT * FROM aktivitas");
        ok($res->fetch_all(MYSQLI_ASSOC));
    case 'add':
        $deskripsi = trim($_POST['deskripsi']??'');
        if(!$deskripsi) fail('Deskripsi wajib diisi');

        $nama = $user['name'];
        $unit = $user['unit']??'';
        $jenis = $_POST['jenis']??'Lainnya';
        $tanggal = $_POST['tanggal']??date('Y-m-d');
        $jam = date('H:i');

        $stmt = $conn->prepare("INSERT INTO aktivitas (nama, unit, jenis, deskripsi, tanggal, jam) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nama, $unit, $jenis, $deskripsi, $tanggal, $jam);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM aktivitas WHERE id=?");
            $stmt->bind_param("i", $newId);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal menambah aktivitas');
    case 'my':
        $stmt = $conn->prepare("SELECT * FROM aktivitas WHERE nama=?");
        $stmt->bind_param("s", $user['name']);
        $stmt->execute();
        ok($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    default: 
        $res = $conn->query("SELECT * FROM aktivitas");
        ok($res->fetch_all(MYSQLI_ASSOC));
}
