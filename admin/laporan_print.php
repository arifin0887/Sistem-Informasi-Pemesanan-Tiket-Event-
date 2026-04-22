<?php
require_once '../koneksi.php';

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');
$id_event_filter = $_GET['id_event'] ?? '';
$event_filter_sql = $id_event_filter ? " AND e.id_event = '$id_event_filter'" : '';

// FULL data for print (no pagination)
$query_laporan = mysqli_query($conn, "
    SELECT o.id_order, o.tanggal_order, u.nama AS nama_pembeli, o.total, o.status,
           COUNT(od.id_detail) as jumlah_item, SUM(od.qty) as total_tiket,
           GROUP_CONCAT(CONCAT(t.nama_tiket, ' (', t.kategori_tiket, ')') SEPARATOR ', ') as rincian_tiket
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    JOIN order_detail od ON o.id_order = od.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    AND o.status = 'paid' $event_filter_sql
    GROUP BY o.id_order ORDER BY o.tanggal_order DESC
");

$total_pendapatan = 0;
$total_tiket_terjual = 0;
$data_rows = [];
while($row = mysqli_fetch_assoc($query_laporan)) {
    $data_rows[] = $row;
    $total_pendapatan += $row['total'];
    $total_tiket_terjual += $row['total_tiket'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan - <?= $tgl_mulai ?> s/d <?= $tgl_selesai ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #1D1145; color: white; font-weight: bold; }
        .total { font-weight: bold; background-color: #f8f9fa; }
        .grand-total { background-color: #0DB5BB; color: white; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PENJUALAN EVENTKU</h2>
        <p>Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)) ?></p>
        <?php if($id_event_filter): ?>
            <p>Event: <?= mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_event FROM event WHERE id_event='$id_event_filter'"))['nama_event'] ?></p>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Order</th>
                <th>Tanggal</th>
                <th>Pembeli</th>
                <th>Rincian Tiket</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach($data_rows as $row): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>#<?= $row['id_order'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['tanggal_order'])) ?></td>
                <td><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                <td><?= $row['rincian_tiket'] ?></td>
                <td class="text-right">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="grand-total">
                <td colspan="5"><strong>GRAND TOTAL</strong></td>
                <td class="text-right"><strong>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></strong></td>
            </tr>
        </tbody>
    </table>

    <script>
        window.print();
    </script>
</body>
</html>
