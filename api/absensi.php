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
        if(in_array($role,['admin','admin_hr','manager'])){
            $res = $conn->query("SELECT * FROM absensi");
            ok($res->fetch_all(MYSQLI_ASSOC));
        } else {
            $stmt = $conn->prepare("SELECT * FROM absensi WHERE nama=?");
            $stmt->bind_param("s", $user['name']);
            $stmt->execute();
            ok($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        }
    case 'checkin':
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT * FROM absensi WHERE nama=? AND tanggal=?");
        $stmt->bind_param("ss", $user['name'], $today);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) fail('Sudah absen hari ini');

        $nama = $user['name'];
        $unit = $user['unit']??'';
        $masuk = date('H:i');
        $keluar = '';
        $status = 'hadir';
        $keterangan = '';

        $stmt = $conn->prepare("INSERT INTO absensi (nama, unit, tanggal, masuk, keluar, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nama, $unit, $today, $masuk, $keluar, $status, $keterangan);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM absensi WHERE id=?");
            $stmt->bind_param("i", $newId);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal checkin');
    case 'checkout':
        $today = date('Y-m-d');
        $keluar = date('H:i');
        
        $stmt = $conn->prepare("UPDATE absensi SET keluar=? WHERE nama=? AND tanggal=? AND (keluar='' OR keluar IS NULL)");
        $stmt->bind_param("sss", $keluar, $user['name'], $today);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $stmt = $conn->prepare("SELECT * FROM absensi WHERE nama=? AND tanggal=?");
            $stmt->bind_param("ss", $user['name'], $today);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Data absensi tidak ditemukan atau sudah checkout');
    default: 
        $res = $conn->query("SELECT * FROM absensi");
        ok($res->fetch_all(MYSQLI_ASSOC));
}
