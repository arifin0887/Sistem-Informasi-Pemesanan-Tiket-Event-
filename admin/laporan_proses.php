<?php
require_once '../koneksi.php';

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');
$id_event = $_GET['id_event'] ?? '';

$filter_sql = " AND DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
if($id_event) $filter_sql .= " AND e.id_event = '$id_event'";

// Query Transaksi Utama
$query = mysqli_query($conn, "
    SELECT o.id_order, o.tanggal_order, u.nama AS nama_pembeli, o.total, o.status,
           SUM(od.qty) as total_tiket,
           GROUP_CONCAT(CONCAT(t.nama_tiket, ' (', od.qty, ')') SEPARATOR ', ') as rincian_tiket
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    JOIN order_detail od ON o.id_order = od.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    WHERE o.status = 'paid' $filter_sql
    GROUP BY o.id_order
    ORDER BY o.tanggal_order DESC
");
?>

<div class="card-header text-white py-3 mb-3" style="border-radius: 10px; background-color: #1D1145;">
    <h5 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Data Transaksi Terverifikasi</h5>
</div>
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>NO</th>
            <th>INFO TRANSAKSI</th>
            <th>PEMBELI</th>
            <th>RINCIAN TIKET</th>
            <th class="text-center">QTY</th>
            <th class="text-end">SUBTOTAL</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        if(mysqli_num_rows($query) > 0):
            while($row = mysqli_fetch_assoc($query)): 
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td>
                <div class="fw-bold">#<?= $row['id_order'] ?></div>
                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($row['tanggal_order'])) ?></small>
            </td>
            <td><?= htmlspecialchars($row['nama_pembeli']) ?></td>
            <td><small class="bg-light p-1 border-start border-3 border-info"><?= $row['rincian_tiket'] ?></small></td>
            <td class="text-center"><?= $row['total_tiket'] ?></td>
            <td class="text-end fw-bold text-primary">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
        </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-center py-4">Data tidak ditemukan.</td></tr>
        <?php endif; ?>
    </tbody>
</table>