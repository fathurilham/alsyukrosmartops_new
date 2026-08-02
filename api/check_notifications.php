<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';
require_once '../config/auth.php';

$user = getCurrentUser();
$role = $user['role'] ?? '';
$realNotifs = [];
$totalNotifItems = 0;

$sidebarCounts = [
    'peminjaman' => 0,
    'event' => 0,
    'maint' => 0
];

if (in_array($role, ['admin', 'manager'])) {
    $resPjm = $conn->query("SELECT COUNT(*) as cnt FROM peminjaman WHERE status='pending'");
    $cntPjm = $resPjm ? (int)$resPjm->fetch_assoc()['cnt'] : 0;
    $sidebarCounts['peminjaman'] = $cntPjm;
    if ($cntPjm > 0) {
        $realNotifs[] = ['package', "$cntPjm peminjaman menunggu approval", 'Baru saja', 'unread', 'dashboard.php?mod=inventory&sub=approval'];
        $totalNotifItems += $cntPjm;
    }
}

if (in_array($role, ['admin', 'eo', 'manager'])) {
    $resEv = $conn->query("SELECT COUNT(*) as cnt FROM event WHERE status='persiapan'");
    $cntEv = $resEv ? (int)$resEv->fetch_assoc()['cnt'] : 0;
    $sidebarCounts['event'] = $cntEv;
    if ($cntEv > 0) {
        $evUrl = ($role === 'admin' || $role === 'manager') ? 'dashboard.php?mod=event&sub=approval_event' : 'dashboard.php?mod=event';
        $realNotifs[] = ['tent', "$cntEv event dalam persiapan", 'Baru saja', 'unread', $evUrl];
        $totalNotifItems += $cntEv;
    }
}

if (in_array($role, ['admin', 'admin_fasilitas', 'teknisi', 'manager'])) {
    $resMaint = $conn->query("SELECT COUNT(*) as cnt FROM request_maint WHERE status='menunggu'");
    $cntMaint = $resMaint ? (int)$resMaint->fetch_assoc()['cnt'] : 0;
    $sidebarCounts['maint'] = $cntMaint;
    if ($cntMaint > 0) {
        $maintUrl = $role === 'teknisi' ? 'dashboard.php?mod=maintenance&sub=tugas_maint' : 'dashboard.php?mod=maintenance';
        $realNotifs[] = ['tool', "$cntMaint request maintenance menunggu", 'Baru saja', 'unread', $maintUrl];
        $totalNotifItems += $cntMaint;
    }
}

echo json_encode([
    'status' => 'ok',
    'count'  => $totalNotifItems,
    'notifs' => $realNotifs,
    'sidebarCounts' => $sidebarCounts
]);
