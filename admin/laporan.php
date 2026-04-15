<?php
require_once '../koneksi.php';

// Ambil filter tanggal (default: bulan ini)
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');

// Query Laporan yang lebih lengkap
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
    <div class="row mb-4">
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
        <div class="col-md-4">
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
        <div class="col-md-4">
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
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body py-4">
                    <form method="GET" action="index.php" class="row g-3 mb-4 align-items-end filter-box p-3 rounded-3 mb-4" style="background: #f8f9fa;">
                        <input type="hidden" name="page" value="laporan">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Mulai Tanggal</label>
                            <input type="date" name="tgl_mulai" class="form-control border-0 shadow-sm" value="<?= $tgl_mulai ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" class="form-control border-0 shadow-sm" value="<?= $tgl_selesai ?>">
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

                    <div class="table-responsive">
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
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* UI Adjustments */
    .filter-box input:focus {
        box-shadow: none;
        border: 1px solid #0DB5BB;
    }
    .table thead th { border: none; }
    .table tbody td { border-bottom: 1px solid #f2f2f2; padding: 1rem 0.75rem; }
    
    /* Print Setup */
    @media print {
        .sidebar, .navbar, .btn, .pagetitle nav, form, .filter-box, .dropdown {
            display: none !important;
        }
        .card { border: none !important; box-shadow: none !important; }
        .row { display: flex !important; }
        .col-md-4 { width: 33.33% !important; float: left; }
        .card-body { padding: 0 !important; }
        body { background-color: white !important; font-size: 12px; }
        .text-white { color: black !important; }
        .card.text-white { background: white !important; border: 1px solid #ddd !important; }
        .card.text-white * { color: black !important; }
    }
</style>