<?php

// PROSES UPDATE STATUS & DELETE TRANSAKSI
if (isset($_GET['action'])) {
    $id_order = (int)$_GET['id'];
    
    if ($_GET['action'] == 'update_status' && isset($_GET['status'])) {
        $status = mysqli_real_escape_string($conn, $_GET['status']);
        $query = mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id_order=$id_order");
        $res = $query ? "updated" : "failed";
        header("Location: index.php?page=transaksi&status=$res");
        exit;
    }

    if ($_GET['action'] == 'delete') {
        $query = mysqli_query($conn, "DELETE FROM orders WHERE id_order=$id_order");
        $res = $query ? "deleted" : "failed";
        header("Location: index.php?page=transaksi&status=$res");
        exit;
    }
}

// HANDLING ALERT DARI URL (POST-REDIRECT-GET PATTERN)
$message = ""; $message_type = "";
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'updated': $message = "Status transaksi berhasil diperbarui!"; $message_type = "success"; break;
        case 'deleted': $message = "Data transaksi telah dihapus!"; $message_type = "success"; break;
        case 'failed': $message = "Terjadi kesalahan pada sistem database!"; $message_type = "danger"; break;
    }
}

// QUERY UNTUK MENGAMBIL SEMUA TRANSAKSI BESERTA NAMA PELANGGAN DAN EMAIL
$sql_transaksi = "SELECT o.*, u.nama, u.email 
                  FROM orders o 
                  JOIN users u ON o.id_user = u.id_user 
                  ORDER BY o.tanggal_order DESC";
$res_transaksi = mysqli_query($conn, $sql_transaksi);
?>

<style>
    .card { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .card-title { color: #1d1145; font-weight: 700; }
    .table thead th { background-color: #f8f9fa; color: #1d1145; border-bottom: 2px solid #eee; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .order-id { font-family: 'Monaco', 'Consolas', monospace; color: #e66c8a; font-weight: bold; }
    
    /* Status Badges Custom */
    .badge-status { padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; }
    .status-paid { background-color: #e8f5e9; color: #2e7d32; }
    .status-pending { background-color: #fff8e1; color: #f57f17; }
    .status-cancelled { background-color: #ffebee; color: #c62828; }
    
    .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; margin: 0 2px; }
</style>

<div class="pagetitle">
    <h1>Data Transaksi</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Transaksi</li>
        </ol>
    </nav>
</div>

<!-- ALERT UNTUK NOTIFIKASI UPDATE STATUS & DELETE -->
<?php if ($message): ?>
    <div class="alert alert-<?= $message_type; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
        <?= $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Riwayat Penjualan Tiket</h5>
                        <button class="btn btn-outline-primary btn-sm" onclick="window.location.reload();">
                            <i class="bi bi-arrow-clockwise"></i> Refresh Data
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>Waktu Transaksi</th>
                                    <th>Total Bayar</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($res_transaksi)): ?>
                                <tr>
                                    <td><span class="order-id">#<?= $row['id_order']; ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama']); ?></div>
                                        <div class="text-muted" style="font-size: 0.8rem;"><?= htmlspecialchars($row['email']); ?></div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.9rem;"><?= date('d/m/Y', strtotime($row['tanggal_order'])); ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($row['tanggal_order'])); ?> WIB</small>
                                    </td>
                                    <td><span class="fw-bold">Rp <?= number_format($row['total'], 0, ',', '.'); ?></span></td>
                                    <td>
                                        <?php if($row['status'] == 'paid'): ?>
                                            <span class="badge-status status-paid"><i class="bi bi-patch-check-fill me-1"></i> Terbayar</span>
                                        <?php elseif($row['status'] == 'pending'): ?>
                                            <span class="badge-status status-pending"><i class="bi bi-clock-history me-1"></i> Menunggu</span>
                                        <?php else: ?>
                                            <span class="badge-status status-cancelled"><i class="bi bi-x-circle-fill me-1"></i> Dibatalkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-light btn-action" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Ubah Status">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <ul class="dropdown-menu shadow border-0">
                                                <li><h6 class="dropdown-header">Ubah Status Ke:</h6></li>
                                                <li><a class="dropdown-item" href="index.php?page=transaksi&action=update_status&status=paid&id=<?= $row['id_order']; ?>">✅ Set Terbayar (Paid)</a></li>
                                                <li><a class="dropdown-item" href="index.php?page=transaksi&action=update_status&status=pending&id=<?= $row['id_order']; ?>">⏳ Set Menunggu (Pending)</a></li>
                                                <li><a class="dropdown-item text-danger" href="index.php?page=transaksi&action=update_status&status=cancelled&id=<?= $row['id_order']; ?>">❌ Batalkan (Cancel)</a></li>
                                            </ul>
                                        </div>

                                        <a href="index.php?page=detail&id=<?= $row['id_order']; ?>" class="btn btn-primary btn-action" title="Detail Pesanan">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="index.php?page=transaksi&action=delete&id=<?= $row['id_order']; ?>" 
                                           class="btn btn-danger btn-action" 
                                           onclick="return confirm('PERINGATAN: Menghapus transaksi akan menghilangkan riwayat pendapatan. Lanjutkan?')" 
                                           title="Hapus Permanen">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>