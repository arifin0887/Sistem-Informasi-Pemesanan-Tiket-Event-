<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['user']['id'])) {
    echo json_encode([
        'allow' => false,
        'message' => 'Silakan login terlebih dahulu'
    ]);
    exit;
}

$id_event = (int)$_GET['id_event'];
$id_user  = (int)$_SESSION['user']['id'];

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

$stmt_total->bind_param("ii", $id_user, $id_event);
$stmt_total->execute();

$total_bought = (int)($stmt_total->get_result()->fetch_assoc()['total_tickets'] ?? 0);

// AMBIL DATA EVENT + JENIS TIKET
$stmt_event = $conn->prepare("
    SELECT e.nama_event, e.max_beli, MIN(t.nama_tiket) as jenis_tiket
    FROM event e
    JOIN tiket t ON e.id_event = t.id_event
    WHERE e.id_event = ?
");

$stmt_event->bind_param("i", $id_event);
$stmt_event->execute();

$event = $stmt_event->get_result()->fetch_assoc();

$nama_event   = $event['nama_event'] ?? 'Event';
$max_limit    = (int)($event['max_beli'] ?? 0);
$jenis_tiket  = strtolower($event['jenis_tiket'] ?? '');

// FALLBACK BERDASARKAN JENIS TIKET
if ($max_limit <= 0) {

    if (str_contains($jenis_tiket, 'bola') || str_contains($jenis_tiket, 'sepak')) {
        $max_limit = 1; 
    } 
    elseif (str_contains($jenis_tiket, 'Music') || str_contains($jenis_tiket, 'konser')) {
        $max_limit = 2; 
    } 
    elseif (str_contains($jenis_tiket, 'Festival') || str_contains($jenis_tiket, 'seni')) {
        $max_limit = 5;
    } 
    elseif (str_contains($jenis_tiket, 'pameran')) {
        $max_limit = 10;
    } 
    else {
        $max_limit = 3; 
    }
}

// VALIDASI LIMIT
$allow = $total_bought < $max_limit;

$message = $allow
    ? "Anda bisa membeli tiket untuk event {$nama_event}"
    : "Batas pembelian tercapai! Anda sudah membeli {$total_bought} tiket. Maksimal {$max_limit} tiket untuk event ini.";

echo json_encode([
    'allow' => $allow,
    'message' => $message,
    'limit' => $max_limit,
    'sudah_beli' => $total_bought
]);
?>