<?php
require_once '../koneksi.php';

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');
$id_event_filter = $_GET['id_event'] ?? '';
$event_filter_sql = $id_event_filter ? " AND e.id_event = '$id_event_filter'" : '';

// Query Laporan (Paid)
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

$data_rows = [];
$total_pendapatan = 0;
$total_tiket_terjual = 0;
while($row = mysqli_fetch_assoc($query_laporan)) {
    $data_rows[] = $row;
    $total_pendapatan += $row['total'];
    $total_tiket_terjual += $row['total_tiket'];
}
$jumlah_transaksi = count($data_rows);

// Query Cancel
$query_cancel = mysqli_query($conn, "
    SELECT o.id_order, o.tanggal_order, u.nama AS nama_pembeli, o.total, o.status,
           COUNT(od.id_detail) as jumlah_item, SUM(od.qty) as total_tiket,
           GROUP_CONCAT(CONCAT(t.nama_tiket, ' (', t.kategori_tiket, ')') SEPARATOR ', ') as rincian_tiket
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    JOIN order_detail od ON o.id_order = od.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    AND o.status = 'cancel' $event_filter_sql
    GROUP BY o.id_order ORDER BY o.tanggal_order DESC
");

$data_cancel = [];
$total_cancel = 0; 

while($row = mysqli_fetch_assoc($query_cancel)) {
    $data_cancel[] = $row;
    $total_cancel += (float)$row['total_tiket']; 
}

// Query Top Event
$top_event_query = mysqli_query($conn, "
    SELECT e.nama_event, SUM(o.total) as revenue FROM orders o 
    JOIN order_detail od ON o.id_order = od.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event 
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai' AND o.status = 'paid' $event_filter_sql
    GROUP BY e.id_event ORDER BY revenue DESC LIMIT 1
");
$top_event = mysqli_fetch_assoc($top_event_query) ?: ['nama_event' => 'Tidak ada', 'revenue' => 0];

// Penjualan Per Event
$event_sales_query = mysqli_query($conn, "
    SELECT 
        e.id_event,
        e.nama_event, 
        v.nama_venue, 
        SUM(o.total) as revenue, 
        SUM(od.qty) as total_qty, 
        COUNT(DISTINCT o.id_order) as transaksi,
        SUM(CASE WHEN t.kategori_tiket LIKE '%Reguler%' THEN od.qty ELSE 0 END) as qty_reguler,
        SUM(CASE WHEN t.kategori_tiket LIKE '%VIP%' AND t.kategori_tiket NOT LIKE '%VVIP%' THEN od.qty ELSE 0 END) as qty_vip,
        SUM(CASE WHEN t.kategori_tiket LIKE '%VVIP%' THEN od.qty ELSE 0 END) as qty_vvip
    FROM orders o 
    JOIN order_detail od ON o.id_order = od.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event 
    JOIN venue v ON e.id_venue = v.id_venue
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai' 
    AND o.status = 'paid' 
    $event_filter_sql
    GROUP BY e.id_event 
    ORDER BY revenue DESC
");
?>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm text-white p-4" style="background: linear-gradient(45deg, #0DB5BB, #0ca4aa); border-radius: 15px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1 small opacity-75">Total Omzet</h6>
                    <h2 class="mb-0 fw-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h2>
                </div>
                <i class="bi bi-cash-stack fs-1"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm text-white p-4" style="background: linear-gradient(45deg, #1D1145, #2a1a5e); border-radius: 15px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1 small opacity-75">Rata-rata Transaksi</h6>
                    <h2 class="mb-0 fw-bold">Rp <?= ($jumlah_transaksi > 0) ? number_format($total_pendapatan / $jumlah_transaksi, 0, ',', '.') : 0 ?></h2>
                </div>
                <i class="bi bi-graph-up-arrow fs-1"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm text-white p-4" style="background: #1D1145; border-radius: 15px;">
            <h6 class="small opacity-75">TIKET TERJUAL</h6>
            <h3 class="fw-bold"><?= number_format($total_tiket_terjual) ?> Pcs</h3>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm text-white p-4" style="background: #dc3545; border-radius: 15px;">
            <h6 class="small opacity-75">TIKET DIBATALKAN</h6>
            <h3 class="fw-bold"><?= number_format($total_cancel ?? 0) ?> Pcs</h3>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm text-white p-4" style="background: #28a745; border-radius: 15px;">
            <h6 class="small opacity-75">TOP EVENT</h6>
            <h5 class="fw-bold mb-0 text-truncate"><?= htmlspecialchars($top_event['nama_event']) ?></h5>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
    <div class="card-body py-4">

        <div class="card-header text-white py-3 mb-3" style="border-radius: 10px; background-color: #1D1145;">
            <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2"></i>Data Transaksi (Paid)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>ID ORDER</th>
                        <th>PEMBELI</th>
                        <th>RINCIAN</th>
                        <th class="text-center">QTY</th>
                        <th class="text-end">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($jumlah_transaksi > 0): $no=1; foreach($data_rows as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong>#<?= $row['id_order'] ?></strong><br><small><?= $row['tanggal_order'] ?></small></td>
                        <td><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                        <td><small class="p-1 bg-light border-start border-3 border-info"><?= $row['rincian_tiket'] ?></small></td>
                        <td class="text-center"><?= $row['total_tiket'] ?></td>
                        <td class="text-end fw-bold">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center py-4">Tidak ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card-header text-white py-3 mt-5 mb-3" style="border-radius: 10px; background-color: #0ca4aa;">
            <h5 class="mb-0 fw-bold"><i class="bi bi-trophy me-2"></i>Performa per Event</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>EVENT</th>
                        <th class="text-center">TRANSAKSI</th>
                        <th class="text-center">REGULER</th>
                        <th class="text-center">VIP</th>
                        <th class="text-center">VVIP</th>
                        <th class="text-center">TOTAL TIKET</th>
                        <th class="text-end">PENDAPATAN</th>
                    </tr>
                </thead>
                <?php 
                    if(mysqli_num_rows($event_sales_query) > 0):
                        while($ev = mysqli_fetch_assoc($event_sales_query)): 
                    ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($ev['nama_event']) ?></div>
                            <small class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($ev['nama_venue']) ?></small>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-dark"><?= number_format($ev['transaksi']) ?></span>
                        </td>
                        <td class="text-center"><?= number_format($ev['qty_reguler']) ?></td>
                        <td class="text-center"><?= number_format($ev['qty_vip']) ?></td>
                        <td class="text-center"><?= number_format($ev['qty_vvip']) ?></td>
                        
                        <td class="text-center fw-bold bg-light">
                            <?= number_format($ev['total_qty']) ?>
                        </td>
                        
                        <td class="text-end fw-bold">
                            Rp <?= number_format($ev['revenue'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada data penjualan untuk periode ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card-header text-white py-3 mt-5 mb-3" style="border-radius: 10px; background-color: #dc3545;">
            <h5 class="mb-0 fw-bold"><i class="bi bi-x-circle me-2"></i>Riwayat Pembatalan</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>ID ORDER</th>
                        <th>PEMBELI</th>
                        <th>RINCIAN</th>
                        <th class="text-center">QTY</th>
                        <th class="text-end">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($data_cancel) > 0): $no=1; foreach($data_cancel as $c): ?>
                    <tr class="opacity-75"> <td><?= $no++ ?></td>
                        <td>
                            <strong>#<?= $c['id_order'] ?></strong><br>
                            <small class="text-muted"><?= $c['tanggal_order'] ?></small>
                        </td>
                        <td><?= htmlspecialchars($c['nama_pembeli']) ?></td>
                        <td>
                            <small class="p-1 bg-light border-start border-3 border-danger">
                                <?= $c['rincian_tiket'] ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="text-center"><?= $c['total_tiket'] ?></span>
                        </td>
                        <td class="text-end fw-bold text-decoration-line-through text-muted">
                            Rp <?= number_format($c['total'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Tidak ada data pembatalan pada periode ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>