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
        $res = $conn->query("SELECT * FROM request_maint");
        ok($res->fetch_all(MYSQLI_ASSOC));
    case 'my':
        $stmt = $conn->prepare("SELECT * FROM request_maint WHERE pemohon=?");
        $stmt->bind_param("s", $user['name']);
        $stmt->execute();
        ok($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    case 'add':
        $fasilitas = trim($_POST['fasilitas']??'');
        $masalah = trim($_POST['masalah']??'');
        if(!$fasilitas||!$masalah) fail('Fasilitas dan masalah wajib diisi');

        $res = $conn->query("SELECT MAX(id) as max_id FROM request_maint");
        $row = $res->fetch_assoc();
        $nextId = ($row['max_id'] ?? 0) + 1;
        $kode = 'REQ-'.str_pad($nextId,3,'0',STR_PAD_LEFT);
        $pemohon = $user['name'];
        $unit = $user['unit']??'';
        $prioritas = $_POST['prioritas']??'Normal';
        $status = 'menunggu';
        $tgl_request = date('Y-m-d');
        $catatan_teknisi = '';

        $stmt = $conn->prepare("INSERT INTO request_maint (kode, pemohon, unit, fasilitas, masalah, prioritas, status, tgl_request, catatan_teknisi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $kode, $pemohon, $unit, $fasilitas, $masalah, $prioritas, $status, $tgl_request, $catatan_teknisi);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM request_maint WHERE id=?");
            $stmt->bind_param("i", $newId);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal menambah request');
    case 'update':
        if(!in_array($role,['admin','admin_fasilitas','teknisi'])) fail('Akses ditolak');
        $id = (int)($_POST['id']??0);
        
        $stmt = $conn->prepare("SELECT * FROM request_maint WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $it = $stmt->get_result()->fetch_assoc();
        if (!$it) fail('Tidak ditemukan');

        $status = $_POST['status'] ?? $it['status'];
        $catatan_teknisi = $_POST['catatan_teknisi'] ?? $it['catatan_teknisi'];

        $stmt = $conn->prepare("UPDATE request_maint SET status=?, catatan_teknisi=? WHERE id=?");
        $stmt->bind_param("ssi", $status, $catatan_teknisi, $id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("SELECT * FROM request_maint WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            ok($stmt->get_result()->fetch_assoc());
        }
        fail('Gagal update request');
    default: 
        $res = $conn->query("SELECT * FROM request_maint");
        ok($res->fetch_all(MYSQLI_ASSOC));
}
