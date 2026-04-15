<?php
require_once '../koneksi.php';

$tgl_mulai = $_GET['tgl_mulai'];
$tgl_selesai = $_GET['tgl_selesai'];

// Header untuk Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Penjualan_{$tgl_mulai}_to_{$tgl_selesai}.xls");

$query = mysqli_query($conn, "
    SELECT o.id_order, o.tanggal_order, u.nama, o.total,
           GROUP_CONCAT(t.nama_tiket SEPARATOR ', ') as detail_tiket
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    JOIN order_detail od ON o.id_order = od.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    AND o.status = 'paid'
    GROUP BY o.id_order
");
?>

<h2>LAPORAN PENJUALAN EVENTKU</h2>
<p>Periode: <?= $tgl_mulai ?> s/d <?= $tgl_selesai ?></p>

<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>ID Order</th>
            <th>Tanggal</th>
            <th>Nama Pelanggan</th>
            <th>Item Tiket</th>
            <th>Total Pendapatan</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1; $grand_total = 0;
        while($row = mysqli_fetch_assoc($query)): 
            $grand_total += $row['total'];
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td>#<?= $row['id_order'] ?></td>
            <td><?= $row['tanggal_order'] ?></td>
            <td><?= $row['nama'] ?></td>
            <td><?= $row['detail_tiket'] ?></td>
            <td><?= $row['total'] ?></td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <th colspan="5">GRAND TOTAL</th>
            <th><?= $grand_total ?></th>
        </tr>
    </tbody>
</table>