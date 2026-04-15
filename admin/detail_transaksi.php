<?php
// CEK KONEKSI & SESSION
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// AMBIL ID ORDER DARI URL
$id_order = $_GET['id'];

// QUERY MENGAMBIL DATA TRANSAKSI BESERTA NAMA PELANGGAN DAN EMAIL
$sql_order = "SELECT o.*, u.nama, u.email 
              FROM orders o 
              JOIN users u ON o.id_user = u.id_user 
              WHERE o.id_order = '$id_order'";
$res_order = mysqli_query($conn, $sql_order);
$data_order = mysqli_fetch_assoc($res_order);

// JIKA DATA TIDAK DITEMUKAN, KEMBALIKAN KE HALAMAN TRANSAKSI
if (!$data_order) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location='index.php?page=transaksi';</script>";
    exit;
}

// QUERY UNTUK MENGAMBIL ITEM YANG DIBELI DALAM TRANSAKSI INI
$sql_items = "SELECT od.*, t.nama_tiket, e.nama_event, e.tanggal
              FROM order_detail od
              JOIN tiket t ON od.id_tiket = t.id_tiket
              JOIN event e ON t.id_event = e.id_event
              WHERE od.id_order = '$id_order'";
$res_items = mysqli_query($conn, $sql_items);
?>

<div class="pagetitle">
  <h1>Detail Transaksi #<?= $id_order ?></h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item"><a href="index.php?page=transaksi">Transaksi</a></li>
      <li class="breadcrumb-item active">Detail</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    
    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Informasi Pesanan</h5>
          
          <div class="mb-3">
            <label class="text-muted small d-block">Nama Pelanggan</label>
            <strong><?= htmlspecialchars($data_order['nama']) ?></strong>
          </div>
          
          <div class="mb-3">
            <label class="text-muted small d-block">Email</label>
            <span><?= htmlspecialchars($data_order['email']) ?></span>
          </div>

          <hr>

          <div class="mb-3">
            <label class="text-muted small d-block">Tanggal Transaksi</label>
            <span><?= date('d F Y, H:i', strtotime($data_order['tanggal_order'])) ?></span>
          </div>

          <div class="mb-3">
            <label class="text-muted small d-block">Status</label>
            <?php 
              $status = $data_order['status'];
              $badge = ($status == 'paid') ? 'bg-success' : (($status == 'pending') ? 'bg-warning' : 'bg-danger');
            ?>
            <span class="badge <?= $badge ?>"><?= ucfirst($status) ?></span>
          </div>
        </div>
      </div>
      
      <a href="index.php?page=transaksi" class="btn btn-light w-100 mb-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    </div>

    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Item yang Dibeli</h5>
          
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="bg-light">
                <tr>
                  <th>Event</th>
                  <th>Jenis Tiket</th>
                  <th class="text-center">Qty</th>
                  <th class="text-end">Harga Satuan</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php while($item = mysqli_fetch_assoc($res_items)): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($item['nama_event']) ?></strong><br>
                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i> <?= date('d M Y', strtotime($item['tanggal'])) ?></small>
                  </td>
                  <td><?= ucfirst($item['nama_tiket']) ?></td>
                  <td class="text-center"><?= $item['qty'] ?></td>
                  <td class="text-end">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                  <td class="text-end font-weight-bold">Rp <?= number_format($item['qty'] * $item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
                <tr>
                  <th colspan="4" class="text-end text-uppercase">Total Bayar</th>
                  <th class="text-end text-primary h5">Rp <?= number_format($data_order['total'], 0, ',', '.') ?></th>
                </tr>
              </tfoot>
            </table>
          </div>

        </div>
      </div>
    </div>

  </div>
</section>