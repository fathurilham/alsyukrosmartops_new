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
        $res = $conn->query("SELECT * FROM maintenance");
        ok($res->fetch_all(MYSQLI_ASSOC));
    case 'add':
        $fasilitas = $_POST['fasilitas']??'';
        if(!$fasilitas) fail('Fasilitas wajib');

        $res = $conn->query("SELECT MAX(id) as max_id FROM maintenance");
        $row = $res->fetch_assoc();
        $nextId = ($row['max_id'] ?? 0) + 1;
        $kode = 'MNT-'.str_pad($nextId,3,'0',STR_PAD_LEFT);
        $jenis = $_POST['jenis']??'Rutin';
        $status = 'terjadwal';
        $teknisi = $_POST['teknisi']??'TBD';
        $tgl_jadwal = $_POST['tgl_jadwal']??date('Y-m-d');
        $prioritas = $_POST['prioritas']??'Normal';
        $deskripsi = $_POST['deskripsi']??'';
        $reporter = $user['name'];

        $stmt = $conn->prepare("INSERT INTO maintenance (kode, fasilitas, jenis, status, teknisi, tgl_jadwal, prioritas, deskripsi, reporter) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $kode, $fasilitas, $jenis, $status, $teknisi, $tgl_jadwal, $prioritas, $deskripsi, $reporter);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM maintenance WHERE id=?");
            $stmt->bind_param("i", $newId);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal menambah data');
    case 'update_status':
    case 'edit':
        if(!in_array($role,['admin','admin_fasilitas','teknisi'])) fail('Akses ditolak');
        $id=(int)($_POST['id']??0);
        
        $stmt = $conn->prepare("SELECT * FROM maintenance WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $it = $stmt->get_result()->fetch_assoc();
        if (!$it) fail('Tidak ditemukan');

        $status = $_POST['status'] ?? $it['status'];
        $kondisi = $_POST['kondisi'] ?? $it['kondisi'];
        $teknisi = $_POST['teknisi'] ?? $it['teknisi'];
        $prioritas = $_POST['prioritas'] ?? $it['prioritas'];
        $deskripsi = $_POST['deskripsi'] ?? $it['deskripsi'];
        $tgl_jadwal = $_POST['tgl_jadwal'] ?? $it['tgl_jadwal'];
        $maint_terakhir = $_POST['maint_terakhir'] ?? $it['maint_terakhir'];
        $maint_berikut = $_POST['maint_berikut'] ?? $it['maint_berikut'];
        $status_maint = $_POST['status_maint'] ?? $it['status_maint'];
        
        $tgl_selesai = $it['tgl_selesai'];
        if($status === 'selesai' || $status_maint === 'selesai') $tgl_selesai = date('Y-m-d');

        $stmt = $conn->prepare("UPDATE maintenance SET status=?, kondisi=?, teknisi=?, prioritas=?, deskripsi=?, tgl_jadwal=?, maint_terakhir=?, maint_berikut=?, status_maint=?, tgl_selesai=? WHERE id=?");
        $stmt->bind_param("ssssssssssi", $status, $kondisi, $teknisi, $prioritas, $deskripsi, $tgl_jadwal, $maint_terakhir, $maint_berikut, $status_maint, $tgl_selesai, $id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("SELECT * FROM maintenance WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal update data');
    case 'delete':
        if($role!=='admin') fail('Akses ditolak');
        $id=(int)($_POST['id']??0);
        $stmt = $conn->prepare("DELETE FROM maintenance WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) ok();
        fail('Gagal hapus data');
    default: 
        $res = $conn->query("SELECT * FROM maintenance");
        ok($res->fetch_all(MYSQLI_ASSOC));
}
