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
        $res = $conn->query("SELECT * FROM event");
        ok($res->fetch_all(MYSQLI_ASSOC));
    case 'get':
        $id=(int)($_GET['id']??0);
        $stmt = $conn->prepare("SELECT * FROM event WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($e = $stmt->get_result()->fetch_assoc()) ok($e);
        fail('Tidak ditemukan');
    case 'add':
        if(!in_array($role,['admin','eo'])) fail('Akses ditolak');
        $nama = $_POST['nama']??'';
        $tgl_mulai = $_POST['tgl_mulai']??'';
        if(!$nama||!$tgl_mulai) fail('Data tidak lengkap');

        $res = $conn->query("SELECT MAX(id) as max_id FROM event");
        $row = $res->fetch_assoc();
        $nextId = ($row['max_id'] ?? 0) + 1;
        $kode = 'EVT-'.str_pad($nextId,3,'0',STR_PAD_LEFT);
        $jenis = $_POST['jenis']??'Akademik';
        $tgl_selesai = $_POST['tgl_selesai']??'';
        $lokasi = $_POST['lokasi']??'';
        $status = 'persiapan';
        $personel = (int)($_POST['personel']??0);
        $fasilitas = $_POST['fasilitas']??'';
        $deskripsi = $_POST['deskripsi']??'';
        $approved = 0;
        $evaluasi = '';

        $stmt = $conn->prepare("INSERT INTO event (kode, nama, jenis, tgl_mulai, tgl_selesai, lokasi, status, personel, fasilitas, deskripsi, approved, evaluasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssissis", $kode, $nama, $jenis, $tgl_mulai, $tgl_selesai, $lokasi, $status, $personel, $fasilitas, $deskripsi, $approved, $evaluasi);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM event WHERE id=?");
            $stmt->bind_param("i", $newId);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal menambah data');
    case 'edit':
        if(!in_array($role,['admin','eo'])) fail('Akses ditolak');
        $id=(int)($_POST['id']??0);
        
        $stmt = $conn->prepare("SELECT * FROM event WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $e = $stmt->get_result()->fetch_assoc();
        if (!$e) fail('Tidak ditemukan');

        $nama = $_POST['nama'] ?? $e['nama'];
        $jenis = $_POST['jenis'] ?? $e['jenis'];
        $tgl_mulai = $_POST['tgl_mulai'] ?? $e['tgl_mulai'];
        $tgl_selesai = $_POST['tgl_selesai'] ?? $e['tgl_selesai'];
        $lokasi = $_POST['lokasi'] ?? $e['lokasi'];
        $status = $_POST['status'] ?? $e['status'];
        $personel = isset($_POST['personel']) ? (int)$_POST['personel'] : $e['personel'];
        $fasilitas = $_POST['fasilitas'] ?? $e['fasilitas'];
        $deskripsi = $_POST['deskripsi'] ?? $e['deskripsi'];

        $stmt = $conn->prepare("UPDATE event SET nama=?, jenis=?, tgl_mulai=?, tgl_selesai=?, lokasi=?, status=?, personel=?, fasilitas=?, deskripsi=? WHERE id=?");
        $stmt->bind_param("ssssssissi", $nama, $jenis, $tgl_mulai, $tgl_selesai, $lokasi, $status, $personel, $fasilitas, $deskripsi, $id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("SELECT * FROM event WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal update data');
    case 'approve':
        if(!in_array($role,['admin','manager'])) fail('Akses ditolak');
        $id=(int)($_POST['id']??0);
        $stmt = $conn->prepare("UPDATE event SET approved=1 WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("SELECT * FROM event WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Tidak ditemukan');
    case 'evaluasi':
        $id=(int)($_POST['id']??0);
        $evaluasi = $_POST['evaluasi']??'';
        $stmt = $conn->prepare("UPDATE event SET evaluasi=? WHERE id=?");
        $stmt->bind_param("si", $evaluasi, $id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("SELECT * FROM event WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Tidak ditemukan');
    case 'delete':
        if($role!=='admin') fail('Akses ditolak');
        $id=(int)($_POST['id']??0);
        $stmt = $conn->prepare("DELETE FROM event WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) ok();
        fail('Gagal hapus data');
    default: 
        $res = $conn->query("SELECT * FROM event");
        ok($res->fetch_all(MYSQLI_ASSOC));
}
