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
        $res = $conn->query("SELECT * FROM karyawan");
        ok($res->fetch_all(MYSQLI_ASSOC));
    case 'get':
        $id=(int)($_GET['id']??0);
        $stmt = $conn->prepare("SELECT * FROM karyawan WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($k = $stmt->get_result()->fetch_assoc()) ok($k);
        fail('Tidak ditemukan');
    case 'add':
        if(!in_array($role,['admin','admin_hr'])) fail('Akses ditolak');
        $nama = trim($_POST['nama']??'');
        if(!$nama) fail('Nama wajib');

        $res = $conn->query("SELECT MAX(id) as max_id FROM karyawan");
        $row = $res->fetch_assoc();
        $nextId = ($row['max_id'] ?? 0) + 1;
        $nip = 'KRY-'.str_pad($nextId,3,'0',STR_PAD_LEFT);
        $jabatan = $_POST['jabatan']??'';
        $unit = $_POST['unit']??'';
        $email = $_POST['email']??'';
        $telp = $_POST['telp']??'';
        $status = 'aktif';
        $tgl_bergabung = $_POST['tgl_bergabung']??date('Y-m-d');
        $kehadiran = 100;
        $kinerja = 90;

        $stmt = $conn->prepare("INSERT INTO karyawan (nip, nama, jabatan, unit, email, telp, status, tgl_bergabung, kehadiran, kinerja) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssii", $nip, $nama, $jabatan, $unit, $email, $telp, $status, $tgl_bergabung, $kehadiran, $kinerja);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM karyawan WHERE id=?");
            $stmt->bind_param("i", $newId);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal menambah data');
    case 'edit':
        if(!in_array($role,['admin','admin_hr'])) fail('Akses ditolak');
        $id=(int)($_POST['id']??0);
        
        $stmt = $conn->prepare("SELECT * FROM karyawan WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $k = $stmt->get_result()->fetch_assoc();
        if (!$k) fail('Tidak ditemukan');

        $nama = $_POST['nama'] ?? $k['nama'];
        $jabatan = $_POST['jabatan'] ?? $k['jabatan'];
        $unit = $_POST['unit'] ?? $k['unit'];
        $email = $_POST['email'] ?? $k['email'];
        $telp = $_POST['telp'] ?? $k['telp'];
        $status = $_POST['status'] ?? $k['status'];

        $stmt = $conn->prepare("UPDATE karyawan SET nama=?, jabatan=?, unit=?, email=?, telp=?, status=? WHERE id=?");
        $stmt->bind_param("ssssssi", $nama, $jabatan, $unit, $email, $telp, $status, $id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("SELECT * FROM karyawan WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal update data');
    case 'delete':
        if($role!=='admin') fail('Akses ditolak');
        $id=(int)($_POST['id']??0);
        $stmt = $conn->prepare("DELETE FROM karyawan WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) ok();
        fail('Gagal hapus data');
    default: 
        $res = $conn->query("SELECT * FROM karyawan");
        ok($res->fetch_all(MYSQLI_ASSOC));
}
