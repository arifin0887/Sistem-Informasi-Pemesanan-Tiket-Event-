<?php
session_start();
require_once '../koneksi.php';

// 🔥 WAJIB: header JSON
header('Content-Type: application/json');

// 🔥 MATIKAN ERROR HTML (BIAR GAK RUSAK JSON)
error_reporting(0);
ini_set('display_errors', 0);

// VALIDASI LOGIN
if (!isset($_SESSION['user']['id'])) {
    echo json_encode([
        'allow' => false,
        'message' => 'Silakan login terlebih dahulu'
    ]);
    exit;
}

// VALIDASI PARAMETER
if (!isset($_GET['id_event'])) {
    echo json_encode([
        'allow' => false,
        'message' => 'ID event tidak ditemukan'
    ]);
    exit;
}

$id_event = (int)$_GET['id_event'];
$id_user  = (int)$_SESSION['user']['id'];

try {

    // TOTAL TIKET YANG SUDAH DIBELI USER
    $stmt_total = $conn->prepare("
        SELECT SUM(od.qty) as total_tickets 
        FROM order_detail od 
        JOIN orders o ON od.id_order = o.id_order 
        JOIN tiket t ON od.id_tiket = t.id_tiket 
        WHERE o.id_user = ? 
        AND t.id_event = ? 
        AND o.status IN ('pending','paid')
    ");

    if (!$stmt_total) {
        throw new Exception("Query total gagal");
    }

    $stmt_total->bind_param("ii", $id_user, $id_event);
    $stmt_total->execute();

    $result_total = $stmt_total->get_result()->fetch_assoc();
    $total_bought = (int)($result_total['total_tickets'] ?? 0);

    // AMBIL DATA EVENT
   $stmt_event = $conn->prepare("
        SELECT e.nama_event, e.max_beli, MIN(t.nama_tiket) as jenis_tiket
        FROM event e
        LEFT JOIN tiket t ON e.id_event = t.id_event
        WHERE e.id_event = ?
        GROUP BY e.id_event
    ");

    if (!$stmt_event) {
        throw new Exception("Query event gagal");
    }

    $stmt_event->bind_param("i", $id_event);
    $stmt_event->execute();

    $event = $stmt_event->get_result()->fetch_assoc();

    if (!$event) {
        echo json_encode([
            'allow' => false,
            'message' => 'Event tidak ditemukan'
        ]);
        exit;
    }

    $nama_event   = $event['nama_event'];
    $max_limit    = (int)$event['max_beli'];
    $jenis_tiket  = strtolower($event['jenis_tiket'] ?? '');

    // FALLBACK RULE
    if ($max_limit <= 0) {
        if (str_contains($jenis_tiket, 'bola') || str_contains($jenis_tiket, 'sepak')) {
            $max_limit = 1;
        } elseif (str_contains($jenis_tiket, 'music') || str_contains($jenis_tiket, 'konser')) {
            $max_limit = 2;
        } elseif (str_contains($jenis_tiket, 'festival') || str_contains($jenis_tiket, 'seni')) {
            $max_limit = 5;
        } elseif (str_contains($jenis_tiket, 'pameran')) {
            $max_limit = 10;
        } else {
            $max_limit = 3;
        }
    }

    // VALIDASI LIMIT
    $allow = $total_bought < $max_limit;

    $message = $allow
        ? "Anda bisa membeli tiket untuk event {$nama_event}"
        : "Batas pembelian tercapai! Anda sudah membeli {$total_bought} tiket. Maksimal {$max_limit} tiket.";

    echo json_encode([
        'allow' => $allow,
        'message' => $message,
        'limit' => $max_limit,
        'sudah_beli' => $total_bought
    ]);

} catch (Exception $e) {

    // 🔥 HANDLE ERROR BIAR GAK KELUAR HTML
    echo json_encode([
        'allow' => false,
        'message' => 'Terjadi kesalahan server'
    ]);
}