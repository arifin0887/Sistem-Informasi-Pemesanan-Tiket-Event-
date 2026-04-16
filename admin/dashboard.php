<?php
// CEK KONEKSI & SESSION
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// QUERY MENGHITUNG TOTAL TIKET TERJUAL DARI SEMUA TRANSAKSI YANG SUDAH DIBAYAR
// MENGGUNAKAN COALESCE UNTUK MENGEMBALIKAN 0 JIKA TIDAK ADA DATA
$sql_sales = "SELECT COALESCE(SUM(od.qty), 0) as total_terjual 
              FROM order_detail od 
              JOIN orders o ON od.id_order = o.id_order 
              WHERE o.status = 'paid'";
$res_sales = mysqli_query($conn, $sql_sales);
$data_sales = mysqli_fetch_assoc($res_sales);

// QUERY MENGHITUNG TOTAL PENDAPATAN DARI SEMUA TRANSAKSI YANG SUDAH DIBAYAR
$sql_revenue = "SELECT COALESCE(SUM(total), 0) as total_duit 
                FROM orders 
                WHERE status = 'paid'";
$res_rev = mysqli_query($conn, $sql_revenue);
$data_rev = mysqli_fetch_assoc($res_rev);

// QUERY MENGHITUNG TOTAL PELANGGAN (USER) YANG TERDAFTAR DI DATABASE
$sql_cust = "SELECT COUNT(id_user) as total_user FROM users WHERE role = 'user'";
$res_cust = mysqli_query($conn, $sql_cust);
$data_cust = mysqli_fetch_assoc($res_cust);

// QUERY UNTUK MENGAMBIL 5 TRANSAKSI TERBARU BESERTA NAMA PELANGGAN DAN NAMA EVENT
$sql_recent = "SELECT o.id_order, u.nama, e.nama_event, o.total, o.status 
               FROM orders o 
               JOIN users u ON o.id_user = u.id_user
               JOIN order_detail od ON o.id_order = od.id_order
               JOIN tiket t ON od.id_tiket = t.id_tiket
               JOIN event e ON t.id_event = e.id_event
               ORDER BY o.id_order DESC LIMIT 5";
$res_recent = mysqli_query($conn, $sql_recent);
?>

<div class="pagetitle">
  <h1>Admin Dashboard</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item active">Dashboard</li>
    </ol>
  </nav>
</div>

<section class="section dashboard">
  <div class="row">

    <div class="col-lg-12">
      <div class="row">

        <div class="col-md-4">
          <div class="card info-card sales-card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Tiket Terjual <span>| Total</span></h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                  <i class="bi bi-ticket-perforated"></i>
                </div>
                <div class="ps-3">
                  <h6><?= number_format($data_sales['total_terjual']); ?></h6>
                  <span class="text-muted small fw-medium">Unit Terpesan</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card info-card revenue-card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Total Omzet <span>| Keseluruhan</span></h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                  <i class="bi bi-wallet2"></i>
                </div>
                <div class="ps-3">
                  <h6>Rp <?= number_format($data_rev['total_duit'], 0, ',', '.'); ?></h6>
                  <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill"></i> Selesai</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card info-card customers-card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">User Terdaftar <span>| Pelanggan</span></h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                  <i class="bi bi-person-badge"></i>
                </div>
                <div class="ps-3">
                  <h6><?= number_format($data_cust['total_user']); ?></h6>
                  <span class="text-muted small fw-medium">Jiwa Bergabung</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="col-lg-12 mt-3">
      <div class="card recent-sales overflow-auto shadow-sm">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Transaksi Terbaru</h5>
            <a href="index.php?page=transaksi" class="btn btn-sm btn-light text-primary fw-bold">Lihat Semua</a>
          </div>
          
          <table class="table table-hover datatable">
            <thead>
              <tr>
                <th>ID ORDER</th>
                <th>PELANGGAN</th>
                <th>EVENT YANG DIBELI</th>
                <th class="text-end">NOMINAL</th>
                <th class="text-center">STATUS</th>
              </tr>
            </thead>
            <tbody>
              <?php while($row = mysqli_fetch_assoc($res_recent)): ?>
              <tr>
                <td><span class="fw-bold text-primary">#<?= $row['id_order']; ?></span></td>
                <td>
                    <div class="fw-bold"><?= htmlspecialchars($row['nama']); ?></div>
                </td>
                <td>
                    <div class="text-dark small fw-medium"><?= htmlspecialchars($row['nama_event']); ?></div>
                </td>
                <td class="text-end fw-bold">Rp <?= number_format($row['total'], 0, ',', '.'); ?></td>
                <td class="text-center">
                    <?php 
                        $status = $row['status'];
                        $badgeClass = ($status == 'paid') ? 'bg-success-subtle text-success' : (($status == 'pending') ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                    ?>
                    <span class="badge <?= $badgeClass ?> border">
                        <?= ucfirst($status); ?>
                    </span>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</section>