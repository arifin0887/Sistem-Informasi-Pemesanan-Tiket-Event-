<?php
require_once '../koneksi.php';

// Ambil filter tanggal (default: bulan ini)
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');

// QUERY LAPORAN
$query_laporan = mysqli_query($conn, "
    SELECT 
        o.id_order, 
        o.tanggal_order, 
        u.nama AS nama_pembeli, 
        o.total, 
        o.status,
        COUNT(od.id_detail) as jumlah_item,
        SUM(od.qty) as total_tiket,
        GROUP_CONCAT(CONCAT(t.nama_tiket, ' (', od.qty, ')') SEPARATOR ', ') as rincian_tiket
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    JOIN order_detail od ON o.id_order = od.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    AND o.status = 'paid'
    GROUP BY o.id_order
    ORDER BY o.tanggal_order DESC
");

// QUERY DATA CANCEL
$query_cancel = mysqli_query($conn, "
    SELECT 
        o.id_order,
        o.tanggal_order,
        u.nama AS nama_pembeli,
        SUM(od.qty) as total_tiket
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    JOIN order_detail od ON o.id_order = od.id_order
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    AND o.status = 'cancel'
    GROUP BY o.id_order
");

// HITUNG TOTAL CANCEL
$total_cancel = 0;
$data_cancel = [];

while($row = mysqli_fetch_assoc($query_cancel)) {
    $data_cancel[] = $row;
    $total_cancel += $row['total_tiket'];
}

// QUERY LIST EVENTS FOR FILTER
$query_events = mysqli_query($conn, "SELECT id_event, nama_event FROM event ORDER BY nama_event ASC");
$data_events = [];
while($row = mysqli_fetch_assoc($query_events)) {
    $data_events[] = $row;
}

$id_event_filter = $_GET['id_event'] ?? '';
$event_filter_sql = $id_event_filter ? " AND e.id_event = '$id_event_filter'" : '';


// Pre-calculate untuk statistik di atas
$data_rows = [];
$total_pendapatan = 0;
$total_tiket_terjual = 0;
while($row = mysqli_fetch_assoc($query_laporan)) {
    $data_rows[] = $row;
    $total_pendapatan += $row['total'];
    $total_tiket_terjual += $row['total_tiket'];
}
$jumlah_transaksi = count($data_rows);

// QUERY TOP EVENT
$top_event_query = mysqli_query($conn, "
    SELECT e.nama_event, SUM(o.total) as revenue, SUM(od.qty) as qty
    FROM orders o 
    JOIN order_detail od ON o.id_order = od.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event 
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    AND o.status = 'paid' $event_filter_sql
    GROUP BY e.id_event ORDER BY revenue DESC LIMIT 1
");
$top_event = mysqli_fetch_assoc($top_event_query) ?: ['nama_event' => 'Tidak ada', 'revenue' => 0, 'qty' => 0];

//QUERY PERJUALAN PER EVENT 
$event_sales_query = mysqli_query($conn, "
    SELECT 
        e.id_event, e.nama_event, v.nama_venue, 
        SUM(o.total) as revenue, 
        SUM(od.qty) as qty,
        COUNT(DISTINCT o.id_order) as transaksi
    FROM orders o 
    JOIN order_detail od ON o.id_order = od.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event 
    JOIN venue v ON e.id_venue = v.id_venue
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    AND o.status = 'paid' $event_filter_sql
    GROUP BY e.id_event 
    ORDER BY revenue DESC
");
$data_event_sales = [];
$total_event_qty = 0;
while($row = mysqli_fetch_assoc($event_sales_query)) {
    $data_event_sales[] = $row;
    $total_event_qty += $row['qty'];
}

// QUERY TIKET TERJUAL
$ticket_sales_query = mysqli_query($conn, "
    SELECT 
        t.id_tiket, t.nama_tiket, e.nama_event,
        t.harga,
        SUM(od.qty) as qty,
        SUM(od.qty * t.harga) as revenue
    FROM orders o 
    JOIN order_detail od ON o.id_order = od.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event 
    WHERE DATE(o.tanggal_order) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    AND o.status = 'paid' $event_filter_sql
    GROUP BY t.id_tiket 
    ORDER BY revenue DESC
");
$data_ticket_sales = [];
while($row = mysqli_fetch_assoc($ticket_sales_query)) {
    $data_ticket_sales[] = $row;
}
$avg_tickets_per_event = count($data_event_sales) > 0 ? round($total_event_qty / count($data_event_sales)) : 0;
?>

<div class="pagetitle mb-4">
    <h1 style="color: #1D1145; font-weight: 700;">Laporan Penjualan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=admin">Home</a></li>
            <li class="breadcrumb-item active">Laporan</li>
        </ol>
    </nav>
</div>

<section class="section">

    <!-- CARD -->
    <div class="row mb-4">

        <!-- CARD TOTAL OMZET -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(45deg, #0DB5BB, #0ca4aa); border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Total Omzet</h6>
                            <h2 class="mb-0 fw-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h2>
                        </div>
                        <div class="icon-shape bg-white bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-cash-stack fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD RATA RATA  -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-white" style="border-radius: 15px; border-left: 5px solid #1D1145 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small text-muted">Rata-rata Transaksi</h6>
                            <h2 class="mb-0 fw-bold" style="color: #1D1145;">
                                Rp <?= ($jumlah_transaksi > 0) ? number_format($total_pendapatan / $jumlah_transaksi, 0, ',', '.') : 0 ?>
                            </h2>
                        </div>
                        <div class="icon-shape bg-light p-3 rounded-circle text-primary">
                            <i class="bi bi-graph-up-arrow fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD TIKET TERJUAL -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(45deg, #1D1145, #2a1a5e); border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Tiket Terjual</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($total_tiket_terjual) ?> <span class="fs-6 fw-normal">Pcs</span></h2>
                        </div>
                        <div class="icon-shape bg-white bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-ticket-perforated fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD TIKET BATAL -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(45deg, #dc3545, #b02a37); border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Tiket Dibatalkan</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($total_cancel) ?> <span class="fs-6 fw-normal">Pcs</span></h2>
                        </div>
                        <div class="icon-shape bg-white bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-x-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD TOP EVENT -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(45deg, #28a745, #20c997); border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Top Event</h6>
                            <h5 class="mb-1 fw-bold"><?= htmlspecialchars($top_event['nama_event']) ?></h5>
                            <h6 class="mb-0">Rp <?= number_format($top_event['revenue'], 0, ',', '.') ?></h6>
                        </div>
                        <div class="icon-shape bg-white bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-trophy fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body py-4">

                    <!-- FORM FILTER & CETAK -->
                    <form method="GET" action="index.php" class="row g-3 mb-4 align-items-end filter-box p-3 rounded-3 mb-4" style="background: #f8f9fa;">
                        <input type="hidden" name="page" value="laporan">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase">Mulai Tanggal</label>
                            <input type="date" name="tgl_mulai" class="form-control border-0 shadow-sm" value="<?= $tgl_mulai ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" class="form-control border-0 shadow-sm" value="<?= $tgl_selesai ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase">Event</label>
                            <select name="id_event" class="form-control border-0 shadow-sm">
                                <option value="">Semua Event</option>
                                <?php foreach($data_events as $ev): ?>
                                <option value="<?= $ev['id_event'] ?>" <?= $id_event_filter == $ev['id_event'] ? 'selected' : '' ?>><?= htmlspecialchars($ev['nama_event']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm" style="background-color: #1D1145; border: none; height: 45px;">
                                <i class="bi bi-funnel-fill me-2"></i>Terapkan Filter
                            </button>
                        </div>
                        <div class="col-md-3">
                            <div class="dropdown">
                                <button class="btn btn-success w-100 fw-bold shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" style="height: 45px; background-color: #0DB5BB; border: none;">
                                    <i class="bi bi-cloud-download-fill me-2"></i>Ekspor Data
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><a class="dropdown-item py-2" href="ekspor_excel.php?tgl_mulai=<?= $tgl_mulai ?>&tgl_selesai=<?= $tgl_selesai ?>"><i class="bi bi-file-earmark-excel text-success me-2"></i>Simpan ke Excel</a></li>
                                    <li><a class="dropdown-item py-2" href="#" onclick="window.print()"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Cetak Laporan (PDF)</a></li>
                                </ul>
                            </div>
                        </div>
                    </form>

                    <!-- TABEL DATA TRANSAKSI -->
                    <div class="table-responsive">
                        <div class="card-header text-white py-3" style="border-radius: 15px 15px 0 0; background-color: #1D1145;">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2"></i>Transaksi </h5>
                        </div>
                        <table class="table table-hover align-middle">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th class="text-center py-3" style="color: #1D1145; font-weight: 600; width: 50px;">NO</th>
                                    <th class="py-3" style="color: #1D1145; font-weight: 600;">INFO TRANSAKSI</th>
                                    <th class="py-3" style="color: #1D1145; font-weight: 600;">PEMBELI</th>
                                    <th class="py-3" style="color: #1D1145; font-weight: 600;">RINCIAN TIKET</th>
                                    <th class="text-center py-3" style="color: #1D1145; font-weight: 600;">QTY</th>
                                    <th class="text-end py-3" style="color: #1D1145; font-weight: 600;">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($jumlah_transaksi > 0): ?>
                                    <?php $no = 1; foreach($data_rows as $row): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $no++ ?></td>
                                        <td>
                                            <div class="fw-bold text-dark">#<?= $row['id_order'] ?></div>
                                            <div class="small text-muted"><?= date('d/m/Y - H:i', strtotime($row['tanggal_order'])) ?> WIB</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_pembeli']) ?></div>
                                            <span class="badge bg-success-subtle text-success small">Verified Paid</span>
                                        </td>
                                        <td>
                                            <div class="p-2 bg-light rounded text-dark small border-start border-3 border-info">
                                                <?= htmlspecialchars($row['rincian_tiket']) ?>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold text-dark"><?= $row['total_tiket'] ?></td>
                                        <td class="text-end fw-bold text-primary">
                                            Rp <?= number_format($row['total'], 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted italic">Tidak ada data transaksi ditemukan pada periode ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- TABEL PENJUALAN PER EVENT -->
                    <?php if(!empty($data_event_sales)): ?>
                    <div class="table-responsive mt-4">
                        
                        <div class="card-header text-white py-3" style="border-radius: 15px 15px 0 0; background-color: #1D1145;">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-calendar-event me-2"></i>
                                Penjualan per Event (<?= count($data_event_sales) ?> Event)
                            </h5>
                        </div>

                        <table class="table table-hover align-middle">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th class="text-center py-3" style="width: 50px;">NO</th>
                                    <th class="py-3">EVENT & VENUE</th>
                                    <th class="text-center py-3">TRANSAKSI</th>
                                    <th class="text-center py-3">TIKET TERJUAL</th>
                                    <th class="text-end py-3">PENDAPATAN</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $no = 1; foreach($data_event_sales as $row): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++ ?></td>

                                    <td>
                                        <div class="fw-bold text-dark">
                                            <?= htmlspecialchars($row['nama_event']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            <?= htmlspecialchars($row['nama_venue']) ?>
                                        </div>
                                    </td>

                                    <td class="text-center fw-bold text-dark">
                                        <?= number_format($row['transaksi']) ?>
                                    </td>

                                    <td class="text-center fw-bold text-dark">
                                        <?= number_format($row['qty']) ?>
                                    </td>

                                    <td class="text-end fw-bold text-primary">
                                        Rp <?= number_format($row['revenue'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                    <?php endif; ?>

                    <!-- TABEL PENJUALAN PER TIKET -->
                    <?php if(!empty($data_ticket_sales)): ?>
                    <div class="table-responsive mt-4">

                        <div class="card-header text-white py-3" style="border-radius: 15px 15px 0 0; background-color: #1D1145;">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-ticket-detailed me-2"></i>
                                Penjualan per Tiket (<?= count($data_ticket_sales) ?> Jenis)
                            </h5>
                        </div>

                        <table class="table table-hover align-middle">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th class="text-center py-3" style="width: 50px;">NO</th>
                                    <th class="py-3">TIKET & EVENT</th>
                                    <th class="text-center py-3">QTY TERJUAL</th>
                                    <th class="text-center py-3">HARGA</th>
                                    <th class="text-end py-3">PENDAPATAN</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $no = 1; foreach(array_slice($data_ticket_sales, 0, 20) as $row): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++ ?></td>

                                    <td>
                                        <div class="fw-bold text-dark">
                                            <?= htmlspecialchars($row['nama_tiket']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?= htmlspecialchars($row['nama_event']) ?>
                                        </div>
                                    </td>

                                    <td class="text-center fw-bold text-dark">
                                        <?= number_format($row['qty']) ?>
                                    </td>

                                    <td class="text-center fw-bold text-dark">
                                        Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                    </td>

                                    <td class="text-end fw-bold text-primary">
                                        Rp <?= number_format($row['revenue'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if(count($data_ticket_sales) > 20): ?>
                        <div class="text-center text-muted small mt-2">
                            ... dan <?= count($data_ticket_sales) - 20 ?> jenis tiket lainnya
                        </div>
                        <?php endif; ?>

                    </div>
                    <?php endif; ?>

                    <!-- TABEL TIKET CANCEL -->
                    <div class="table-responsive mt-4">
                        <div class="card-header text-white py-3" style="border-radius: 15px 15px 0 0; background: linear-gradient(45deg, #e70606, #990909);">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-x-circle me-2"></i>Riwayat Pembatalan
                            </h5>
                        </div>

                        <table class="table table-hover align-middle">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th class="text-center py-3" style="color: #1D1145; font-weight: 600; width: 50px;">NO</th>
                                    <th class="py-3" style="color: #1D1145; font-weight: 600;">INFO TRANSAKSI</th>
                                    <th class="py-3" style="color: #1D1145; font-weight: 600;">PEMBELI</th>
                                    <th class="py-3" style="color: #1D1145; font-weight: 600;">TANGGAL</th>
                                    <th class="text-center py-3" style="color: #1D1145; font-weight: 600;">QTY</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if(count($data_cancel) > 0): ?>
                                    <?php $no = 1; foreach($data_cancel as $row): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $no++ ?></td>

                                        <!-- INFO TRANSAKSI -->
                                        <td>
                                            <div class="fw-bold text-dark">
                                                #<?= str_pad($row['id_order'], 6, '0', STR_PAD_LEFT) ?>
                                            </div>
                                            <div class="small text-muted">
                                                Order dibatalkan
                                            </div>
                                        </td>

                                        <!-- PEMBELI -->
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <?= htmlspecialchars($row['nama_pembeli']) ?>
                                            </div>
                                            <span class="badge bg-danger-subtle text-danger small">
                                                Cancelled
                                            </span>
                                        </td>

                                        <!-- TANGGAL -->
                                        <td>
                                            <div class="small text-dark">
                                                <?= date('d/m/Y', strtotime($row['tanggal_order'])) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?= date('H:i', strtotime($row['tanggal_order'])) ?> WIB
                                            </div>
                                        </td>

                                        <!-- QTY -->
                                        <td class="text-center fw-bold text-danger">
                                            <?= $row['total_tiket'] ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            Tidak ada data pembatalan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>