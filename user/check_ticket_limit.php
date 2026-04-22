<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['allow' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$id_event = (int)$_GET['id_event'];
$id_user = (int)$_SESSION['user']['id'];

$stmt_total = $conn->prepare("
    SELECT SUM(od.qty) as total_tickets 
    FROM order_detail od 
    JOIN orders o ON od.id_order = o.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket 
    WHERE o.id_user = ? AND t.id_event = ? AND o.status IN ('pending','paid')
");
$stmt_total->bind_param("ii", $id_user, $id_event);
$stmt_total->execute();
$total_bought = (int)($stmt_total->get_result()->fetch_assoc()['total_tickets'] ?? 0);

$stmt_kuota = $conn->prepare("SELECT SUM(kuota) as total_kuota FROM tiket WHERE id_event = ?");
$stmt_kuota->bind_param("i", $id_event);
$stmt_kuota->execute();
$total_kuota = (int)$stmt_kuota->get_result()->fetch_assoc()['total_kuota'];

$max_limit = 1;
if ($total_kuota >= 100 && $total_kuota <= 200) {
    $max_limit = 5;
} elseif ($total_kuota > 200 && $total_kuota <= 500) {
    $max_limit = 10;
}

$allow = $total_bought < $max_limit;
$message = $allow ? 'Bisa beli tiket' : "Anda sudah membeli {$total_bought} tiket. Maksimal {$max_limit} tiket per event (total kuota: {$total_kuota}).";

echo json_encode([
    'allow' => $allow,
    'message' => $message
]);
?>

