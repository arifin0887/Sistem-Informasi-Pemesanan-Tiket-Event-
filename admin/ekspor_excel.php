<?php
require_once '../koneksi.php';

$tgl_mulai = $_GET['tgl_mulai'];
$tgl_selesai = $_GET['tgl_selesai'];

// Header Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Penjualan_{$tgl_mulai}_to_{$tgl_selesai}.xls");

// QUERY LEBIH DETAIL
$query = mysqli_query($conn, "
    SELECT 
        o.id_order,
        o.tanggal_order,
        u.nama,
        e.nama_event,
        t.kategori_tiket,
        v.nama_venue,
        t.nama_tiket,
        t.harga,
        od.qty,
        (t.harga * od.qty) as subtotal,
        o.total
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    JOIN order_detail od ON o.id_order = od.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    JOIN venue v ON e.id_venue = v.id_venue
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    AND o.status = 'paid'
    ORDER BY o.id_order DESC
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
            <th>Event</th>
            <th>Kelas</th>
            <th>Venue</th>
            <th>Nama Tiket</th>
            <th>Harga</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th>Total Order</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1; 
        $grand_total = 0;

        while($row = mysqli_fetch_assoc($query)): 
            $grand_total += $row['subtotal'];
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td>#<?= $row['id_order'] ?></td>
            <td><?= $row['tanggal_order'] ?></td>
            <td><?= $row['nama'] ?></td>
            <td><?= $row['nama_event'] ?></td>
            <td><?= $row['kategori_tiket'] ?></td>
            <td><?= $row['nama_venue'] ?></td>
            <td><?= $row['nama_tiket'] ?></td>
            <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>
            <td><?= $row['qty'] ?></td>
            <td>Rp <?= number_format($row['subtotal'],0,',','.') ?></td>
            <td>Rp <?= number_format($row['total'],0,',','.') ?></td>
        </tr>
        <?php endwhile; ?>

        <tr>
            <th colspan="9">GRAND TOTAL</th>
            <th colspan="2">
                Rp <?= number_format($grand_total,0,',','.') ?>
            </th>
        </tr>
    </tbody>
</table>