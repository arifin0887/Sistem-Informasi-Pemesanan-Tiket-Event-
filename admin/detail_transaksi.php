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
$sql_items = "SELECT od.*, t.nama_tiket, t.kategori_tiket, e.nama_event, e.tanggal
              FROM order_detail od
              JOIN tiket t ON od.id_tiket = t.id_tiket
              JOIN event e ON t.id_event = e.id_event
              WHERE od.id_order = '$id_order'";
$res_items = mysqli_query($conn, $sql_items);
?>

<section class="section">
  <div class="row">
    
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-4">Informasi Pesanan</h5>
          
          <div class="mb-3">
            <label class="text-muted small d-block">Nama Pelanggan</label>
            <strong class="text-dark"><?= htmlspecialchars($data_order['nama']) ?></strong>
          </div>
          
          <div class="mb-3">
            <label class="text-muted small d-block">Email</label>
            <span class="text-dark"><?= htmlspecialchars($data_order['email']) ?></span>
          </div>

          <hr class="opacity-50">

          <div class="mb-3">
            <label class="text-muted small d-block">Tanggal Transaksi</label>
            <span class="text-dark"><?= date('d F Y, H:i', strtotime($data_order['tanggal_order'])) ?></span>
          </div>

          <div class="mb-3">
            <label class="text-muted small d-block mb-1">Status Pembayaran</label>
            <?php 
              $status = strtolower($data_order['status']);
              // Menggunakan class subtle yang ada di CSS terpadu
              $badge_class = ($status == 'paid') ? 'bg-success-subtle' : (($status == 'pending') ? 'bg-warning-subtle' : 'bg-danger-subtle');
            ?>
            <span class="badge badge-status <?= $badge_class ?>"><?= ucfirst($status) ?></span>
          </div>
        </div>
      </div>
      
      <a href="index.php?page=transaksi" class="btn btn-light w-100 mb-3 border shadow-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    </div>

    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Item yang Dibeli</h5>
          
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Jenis Tiket</th>
                  <th class="text-center">Qty</th>
                  <th class="text-end">Harga</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php while($item = mysqli_fetch_assoc($res_items)): ?>
                <tr>
                  <td>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($item['nama_event']) ?></div>
                    <small class="text-muted">
                      <i class="bi bi-calendar-event me-1"></i> <?= date('d M Y', strtotime($item['tanggal'])) ?>
                    </small>
                  </td>
                  <td>
                    <span class="venue-tag">
                      <?= ucfirst($item['kategori_tiket']) ?> - <?= ucfirst($item['nama_tiket']) ?>
                    </span>
                  </td>
                  <td class="text-center fw-bold"><?= $item['qty'] ?></td>
                  <td class="text-end">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                  <td class="text-end fw-bold text-dark">Rp <?= number_format($item['qty'] * $item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4" class="text-end text-uppercase fw-bold pt-4" style="border:none;">Total Bayar</td>
                  <td class="text-end pt-4" style="border:none;">
                    <span class="h5 fw-bold" style="color: var(--secondary-color);">
                      Rp <?= number_format($data_order['total'], 0, ',', '.') ?>
                    </span>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

        </div>
      </div>
    </div>

  </div>
</section>