<?php
require_once '../../koneksi.php';

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');
$id_event = $_GET['id_event'] ?? '';

$filter_sql = " AND DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
$event_join = $id_event ? " JOIN order_detail od_f ON o.id_order = od_f.id_order JOIN tiket t_f ON od_f.id_tiket = t_f.id_tiket " : "";
$event_cond = $id_event ? " AND t_f.id_event = '$id_event'" : "";

// Hitung Paid
$q_stats = mysqli_query($conn, "SELECT SUM(o.total) as omzet, SUM(od.qty) as tiket FROM orders o JOIN order_detail od ON o.id_order = od.id_order JOIN tiket t ON od.id_tiket = t.id_tiket WHERE o.status = 'paid' AND DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai' " . ($id_event ? " AND t.id_event = '$id_event'" : ""));
$s = mysqli_fetch_assoc($q_stats);

// Hitung Cancel
$q_cancel = mysqli_query($conn, "SELECT SUM(od.qty) as batal FROM orders o JOIN order_detail od ON o.id_order = od.id_order JOIN tiket t ON od.id_tiket = t.id_tiket WHERE o.status = 'cancel' AND DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai' " . ($id_event ? " AND t.id_event = '$id_event'" : ""));
$c = mysqli_fetch_assoc($q_cancel);
?>

<div class="col-md-4">
    <div class="card border-0 shadow-sm text-white p-3" style="background: linear-gradient(45deg, #0DB5BB, #0ca4aa); border-radius: 15px;">
        <h6 class="small opacity-75">TOTAL OMZET</h6>
        <h3 class="fw-bold">Rp <?= number_format($s['omzet'] ?? 0, 0, ',', '.') ?></h3>
    </div>
</div>
<div class="col-md-4">
    <div class="card border-0 shadow-sm text-white p-3" style="background: linear-gradient(45deg, #1D1145, #2a1a5e); border-radius: 15px;">
        <h6 class="small opacity-75">TIKET TERJUAL</h6>
        <h3 class="fw-bold"><?= number_format($s['tiket'] ?? 0) ?> Pcs</h3>
    </div>
</div>
<div class="col-md-4">
    <div class="card border-0 shadow-sm text-white p-3" style="background: linear-gradient(45deg, #dc3545, #b02a37); border-radius: 15px;">
        <h6 class="small opacity-75">TIKET DIBATALKAN</h6>
        <h3 class="fw-bold"><?= number_format($c['batal'] ?? 0) ?> Pcs</h3>
    </div>
</div>