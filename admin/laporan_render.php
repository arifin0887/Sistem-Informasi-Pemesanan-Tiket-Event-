<?php
require_once '../koneksi.php';

// 1. FILTER & PARAMETER
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');
$id_event_filter = $_GET['id_event'] ?? '';
$event_filter_sql = $id_event_filter ? " AND e.id_event = '$id_event_filter'" : '';

// URL Params agar filter tetap nempel saat ganti halaman
$url_params = "tgl_mulai=$tgl_mulai&tgl_selesai=$tgl_selesai&id_event=$id_event_filter";

// 2. PAGINATION SETUP (PAID)
$page = (int)($_GET['paid_page'] ?? 1);
if ($page < 1) $page = 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// 3. PAGINATION SETUP (CANCEL)
$cancel_page = (int)($_GET['cancel_page'] ?? 1);
if ($cancel_page < 1) $cancel_page = 1;
$cancel_limit = 3;
$cancel_offset = ($cancel_page - 1) * $cancel_limit;

// 4. PAGINATION SETUP (EVENT)
$event_page = (int)($_GET['event_page'] ?? 1);
if ($event_page < 1) $event_page = 1;
$event_limit = 3;
$event_offset = ($event_page - 1) * $event_limit;

// Count total events for pagination
$count_events = mysqli_query($conn, "
    SELECT COUNT(DISTINCT e.id_event) as total 
    FROM orders o 
    JOIN order_detail od ON o.id_order = od.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event 
    JOIN venue v ON e.id_venue = v.id_venue
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai' AND o.status = 'paid' $event_filter_sql
");
$total_events = mysqli_fetch_assoc($count_events)['total'] ?? 0;
$total_event_pages = ceil($total_events / $event_limit);

// --- DATA PAID ---
$count_paid = mysqli_query($conn, "SELECT COUNT(DISTINCT o.id_order) as total FROM orders o JOIN users u ON o.id_user = u.id_user JOIN order_detail od ON o.id_order = od.id_order JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai' AND o.status = 'paid' $event_filter_sql");
$total_paid = mysqli_fetch_assoc($count_paid)['total'] ?? 0;
$total_pages_paid = ceil($total_paid / $limit);

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
    GROUP BY o.id_order ORDER BY o.tanggal_order DESC LIMIT $limit OFFSET $offset
");

$data_rows = [];
$data_rows = [];
while($row = mysqli_fetch_assoc($query_laporan)) {
    $data_rows[] = $row;
}

// FULL totals (separate queries for cards - unchanged by pagination)
$total_stmt = mysqli_query($conn, "
    SELECT SUM(o.total) as total_pendapatan, SUM(od.qty) as total_tiket_terjual 
    FROM orders o JOIN order_detail od ON o.id_order = od.id_order JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai' AND o.status = 'paid' $event_filter_sql
");
$total_stats = mysqli_fetch_assoc($total_stmt);
$total_pendapatan = $total_stats['total_pendapatan'] ?? 0;
$total_tiket_terjual = $total_stats['total_tiket_terjual'] ?? 0;

// --- DATA CANCEL ---
$count_cancel = mysqli_query($conn, "SELECT COUNT(DISTINCT o.id_order) as total FROM orders o JOIN users u ON o.id_user = u.id_user JOIN order_detail od ON o.id_order = od.id_order JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai' AND o.status = 'cancel' $event_filter_sql");
$total_cancel_trans = mysqli_fetch_assoc($count_cancel)['total'] ?? 0;
$total_cancel_pages = ceil($total_cancel_trans / $cancel_limit);

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
    GROUP BY o.id_order ORDER BY o.tanggal_order DESC LIMIT $cancel_limit OFFSET $cancel_offset
");
$data_cancel = [];
while($rc = mysqli_fetch_assoc($query_cancel)) { $data_cancel[] = $rc; }

// --- TOP EVENT ---
$top_event_query = mysqli_query($conn, "
    SELECT e.nama_event, SUM(o.total) as revenue FROM orders o 
    JOIN order_detail od ON o.id_order = od.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event 
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai' AND o.status = 'paid' $event_filter_sql
    GROUP BY e.id_event ORDER BY revenue DESC LIMIT 1
");
$top_event = mysqli_fetch_assoc($top_event_query) ?: ['nama_event' => 'Tidak ada', 'revenue' => 0];

// --- PER EVENT BREAKDOWN ---
$event_sales_query = mysqli_query($conn, "
    SELECT 
        e.id_event, e.nama_event, v.nama_venue, 
        SUM(o.total) as revenue, SUM(od.qty) as total_qty, COUNT(DISTINCT o.id_order) as transaksi,
        SUM(CASE WHEN t.kategori_tiket LIKE '%Reguler%' THEN od.qty ELSE 0 END) as qty_reguler,
        SUM(CASE WHEN t.kategori_tiket LIKE '%VIP%' AND t.kategori_tiket NOT LIKE '%VVIP%' THEN od.qty ELSE 0 END) as qty_vip,
        SUM(CASE WHEN t.kategori_tiket LIKE '%VVIP%' THEN od.qty ELSE 0 END) as qty_vvip
    FROM orders o 
    JOIN order_detail od ON o.id_order = od.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event 
    JOIN venue v ON e.id_venue = v.id_venue
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai' AND o.status = 'paid' $event_filter_sql
    GROUP BY e.id_event ORDER BY revenue DESC LIMIT $event_limit OFFSET $event_offset
");

$data_events = [];
while($ev = mysqli_fetch_assoc($event_sales_query)) {
    $data_events[] = $ev;
}
$total_events_displayed = count($data_events);
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
                    <h2 class="mb-0 fw-bold">Rp <?= ($total_paid > 0) ? number_format($total_pendapatan / $total_paid, 0, ',', '.') : 0 ?></h2>
                </div>
                <i class="bi bi-graph-up-arrow fs-1"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm text-white p-4" style="background: #1D1145; border-radius: 15px;">
            <h6 class="small opacity-75 text-uppercase">Tiket Terjual</h6>
            <h3 class="fw-bold mb-0"><?= number_format($total_tiket_terjual) ?> Pcs</h3>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm text-white p-4" style="background: #dc3545; border-radius: 15px;">
            <h6 class="small opacity-75 text-uppercase">Tiket Dibatalkan</h6>
            <h3 class="fw-bold mb-0"><?= number_format($total_cancel_trans) ?> Pcs</h3>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm text-white p-4" style="background: #28a745; border-radius: 15px;">
            <h6 class="small opacity-75 text-uppercase">Top Event</h6>
            <h5 class="fw-bold mb-0 text-truncate"><?= htmlspecialchars($top_event['nama_event']) ?></h5>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-5" style="border-radius: 15px;">
    <div class="card-body">
        <div class="card-header text-white py-3 mb-3" style="border-radius: 10px; background-color: #1D1145;">
            <h5 class="mb-0 fw-bold"><i class="bi bi-check-circle me-2"></i>Data Transaksi (Paid)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID ORDER</th>
                        <th>PEMBELI</th>
                        <th>RINCIAN</th>
                        <th class="text-center">QTY</th>
                        <th class="text-end">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($total_paid > 0): foreach($data_rows as $row): ?>
                    <tr>
                        <td><strong>#<?= $row['id_order'] ?></strong><br><small class="text-muted"><?= $row['tanggal_order'] ?></small></td>
                        <td><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                        <td><small class="p-1 bg-light border-start border-3 border-info text-dark"><?= $row['rincian_tiket'] ?></small></td>
                        <td class="text-center"><?= $row['total_tiket'] ?></td>
                        <td class="text-end fw-bold">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data transaksi lunas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_pages_paid > 1): ?>
        <nav class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=laporan&<?= $url_params ?>&paid_page=<?= $page-1 ?>&cancel_page=<?= $cancel_page ?>&event_page=<?= $event_page ?>">Prev</a>
                </li>
                <?php for($i=1; $i<=$total_pages_paid; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=laporan&<?= $url_params ?>&paid_page=<?= $i ?>&cancel_page=<?= $cancel_page ?>&event_page=<?= $event_page ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages_paid) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=laporan&<?= $url_params ?>&paid_page=<?= $page+1 ?>&cancel_page=<?= $cancel_page ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm mb-5" style="border-radius: 15px;">
    <div class="card-body">
        <div class="card-header text-white py-3 mb-3" style="border-radius: 10px; background-color: #0ca4aa;">
            <h5 class="mb-0 fw-bold"><i class="bi bi-trophy me-2"></i>Performa Per Event</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>EVENT</th>
                        <th class="text-center">TRANSAKSI</th>
                        <th class="text-center text-primary">REGULER</th>
                        <th class="text-center text-warning">VIP</th>
                        <th class="text-center text-danger">VVIP</th>
                        <th class="text-center fw-bold">TOTAL</th>
                        <th class="text-end">PENDAPATAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($total_events > 0): foreach($data_events as $ev): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($ev['nama_event']) ?></strong><br><small><?= htmlspecialchars($ev['nama_venue']) ?></small></td>
                        <td class="text-center"><span class="badge bg-light text-dark border"><?= $ev['transaksi'] ?></span></td>
                        <td class="text-center"><?= number_format($ev['qty_reguler']) ?></td>
                        <td class="text-center"><?= number_format($ev['qty_vip']) ?></td>
                        <td class="text-center"><?= number_format($ev['qty_vvip']) ?></td>
                        <td class="text-center fw-bold"><?= number_format($ev['total_qty']) ?></td>
                        <td class="text-end fw-bold text-success">Rp <?= number_format($ev['revenue'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Data event tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_event_pages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($event_page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=laporan&<?= $url_params ?>&paid_page=<?= $page ?>&cancel_page=<?= $cancel_page ?>&event_page=<?= $event_page-1 ?>">Prev</a>
                </li>
                <?php for($i=1; $i<=$total_event_pages; $i++): ?>
                <li class="page-item <?= ($event_page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=laporan&<?= $url_params ?>&paid_page=<?= $page ?>&cancel_page=<?= $cancel_page ?>&event_page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($event_page >= $total_event_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=laporan&<?= $url_params ?>&paid_page=<?= $page ?>&cancel_page=<?= $cancel_page ?>&event_page=<?= $event_page+1 ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-body text-muted">
        <div class="card-header text-white py-3 mb-3" style="border-radius: 10px; background-color: #dc3545;">
            <h5 class="mb-0 fw-bold"><i class="bi bi-x-circle me-2"></i>Riwayat Pembatalan</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID ORDER</th>
                        <th>PEMBELI</th>
                        <th>RINCIAN</th>
                        <th class="text-center">QTY</th>
                        <th class="text-end text-decoration-line-through">NILAI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($total_cancel_trans > 0): foreach($data_cancel as $c): ?>
                    <tr class="opacity-75">
                        <td><strong>#<?= $c['id_order'] ?></strong><br><small><?= $c['tanggal_order'] ?></small></td>
                        <td><?= htmlspecialchars($c['nama_pembeli']) ?></td>
                        <td><small class="p-1 bg-light border-start border-3 border-danger"><?= $c['rincian_tiket'] ?></small></td>
                        <td class="text-center"><?= $c['total_tiket'] ?></td>
                        <td class="text-end text-muted text-decoration-line-through">Rp <?= number_format($c['total'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center py-4">Tidak ada pembatalan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_cancel_pages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($cancel_page <= 1) ? 'disabled' : '' ?>">
<a class="page-link" href="?page=laporan&<?= $url_params ?>&paid_page=<?= $page ?>&event_page=<?= $event_page ?>&cancel_page=<?= $cancel_page-1 ?>">Prev</a>
                </li>
                <?php for($i=1; $i<=$total_cancel_pages; $i++): ?>
                <li class="page-item <?= ($cancel_page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=laporan&<?= $url_params ?>&paid_page=<?= $page ?>&event_page=<?= $event_page ?>&cancel_page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($cancel_page >= $total_cancel_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=laporan&<?= $url_params ?>&paid_page=<?= $page ?>&cancel_page=<?= $cancel_page+1 ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>