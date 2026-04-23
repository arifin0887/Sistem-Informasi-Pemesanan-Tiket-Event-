<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../koneksi.php';

// PROTEKSI ADMIN
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// AMBIL ID ORDER
$id_order = (int)$_GET['id'];

// PROSES VERIFIKASI
if (isset($_GET['verif'])) {

    if ($_GET['verif'] == 'approve') {
        // 1. Update status order utama menjadi paid
        mysqli_query($conn, "
            UPDATE orders 
            SET status='paid' 
            WHERE id_order = $id_order
        ");

        // 2. Ambil data dari tabel order_detail
        $res_items = mysqli_query($conn, "SELECT * FROM order_detail WHERE id_order = $id_order");

        while ($item = mysqli_fetch_assoc($res_items)) {
            $qty = $item['qty'];
            $id_detail = $item['id_detail']; // Ambil id_detail untuk relasi ke attendee

            // 3. Loop berdasarkan QTY untuk generate tiket per lembar
            for ($i = 0; $i < $qty; $i++) {
                // Generate Kode Unik
                $kode_tiket = "TIX-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

                // 4. Simpan ke tabel attendee
                // status_checkin default 0 (belum) atau sesuaikan dengan enum Anda
                mysqli_query($conn, "
                    INSERT INTO attendee (id_detail, kode_tiket, status_checkin) 
                    VALUES ('$id_detail', '$kode_tiket', '0')
                ");
            }
        }

        echo "<script>
                alert('Pembayaran Disetujui! " . $qty . " Tiket Berhasil Dibuat.');
                window.location.href='index.php?page=transaksi';
              </script>";
        exit;
    }

    if ($_GET['verif'] == 'reject') {
        mysqli_query($conn, "UPDATE orders SET status='cancel' WHERE id_order = $id_order");
        echo "<script>window.location.href='index.php?page=transaksi&msg=rejected';</script>";
        exit;
    }
}

// QUERY ORDER
$sql_order = "SELECT o.*, u.nama, u.email 
              FROM orders o 
              JOIN users u ON o.id_user = u.id_user 
              WHERE o.id_order = $id_order";

$res_order = mysqli_query($conn, $sql_order);
$data_order = mysqli_fetch_assoc($res_order);

if (!$data_order) {
    echo "<script>alert('Data tidak ditemukan');window.location='index.php?page=transaksi';</script>";
    exit;
}

// QUERY ITEM
$sql_items = "SELECT od.*, t.nama_tiket, t.kategori_tiket, e.nama_event, e.tanggal
              FROM order_detail od
              JOIN tiket t ON od.id_tiket = t.id_tiket
              JOIN event e ON t.id_event = e.id_event
              WHERE od.id_order = $id_order";

$res_items = mysqli_query($conn, $sql_items);
?>

<!-- ALERT -->
<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success">
    <?= ($_GET['msg'] == 'approved') ? 'Pembayaran berhasil diverifikasi!' : 'Pembayaran ditolak!' ?>
</div>
<?php endif; ?>

<section class="section">
  <div class="row">

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-4">Informasi Pesanan</h5>
          
          <div class="mb-3">
            <label class="small text-muted">Nama Pelanggan</label>
            <strong class="d-block"><?= $data_order['nama'] ?></strong>
          </div>

          <div class="mb-3">
            <label class="small text-muted">Email</label>
            <div><?= $data_order['email'] ?></div>
          </div>

          <hr>

          <div class="mb-3">
            <label class="small text-muted">Tanggal Transaksi</label>
            <div><?= date('d M Y H:i', strtotime($data_order['tanggal_order'])) ?></div>
          </div>

          <div class="mb-3">
            <label class="small text-muted">Status Saat Ini</label><br>
            <?php 
              $status = $data_order['status'];
              if ($status == 'paid') {
                  echo '<span class="badge bg-success">Terbayar</span>';
              } elseif ($status == 'pending') {
                  echo '<span class="badge bg-secondary">Pending</span>';
              } elseif ($status == 'menunggu_verifikasi') {
                  echo '<span class="badge bg-warning text-dark">Menunggu Verifikasi</span>';
              } else {
                  echo '<span class="badge bg-danger">Dibatalkan</span>';
              }
            ?>
          </div>

          <hr>

          <div class="mb-3">
            <label class="small text-muted">Metode Pembayaran</label>
            <div><b><?= strtoupper($data_order['metode_bayar'] ?? '-') ?></b></div>
          </div>
        </div>
      </div>

      <a href="index.php?page=transaksi" class="btn btn-light w-100 mt-3 border">
        ← Kembali ke Daftar
      </a>
    </div>

    <div class="col-lg-8">
      
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h5 class="card-title mb-3">Detail Tiket yang Dibeli</h5>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Kategori</th>
                  <th class="text-center">Qty</th>
                  <th class="text-end">Harga</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php while($item = mysqli_fetch_assoc($res_items)): ?>
                <tr>
                  <td>
                    <b><?= $item['nama_event'] ?></b><br>
                    <small class="text-muted"><?= date('d M Y', strtotime($item['tanggal'])) ?></small>
                  </td>
                  <td><?= $item['kategori_tiket'] ?></td>
                  <td class="text-center"><?= $item['qty'] ?></td>
                  <td class="text-end">Rp <?= number_format($item['subtotal']) ?></td>
                  <td class="text-end"><b>Rp <?= number_format($item['qty'] * $item['subtotal']) ?></b></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
              <tfoot>
                <tr class="table-light">
                  <td colspan="4" class="text-end"><b>Total yang Harus Dibayar</b></td>
                  <td class="text-end"><b class="text-primary fs-5">Rp <?= number_format($data_order['total']) ?></b></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Bukti Pembayaran</h5>
          
          <div class="row g-4">
            <div class="col-md-6">
              <?php if (!empty($data_order['bukti_transfer'])): ?>
                <div class="position-relative">
                    <img src="../uploads/<?= $data_order['bukti_transfer'] ?>" 
                         class="img-fluid rounded border shadow-sm w-100"
                         style="max-height: 400px; object-fit: contain; cursor: zoom-in;"
                         onclick="window.open(this.src)"
                         title="Klik untuk memperbesar">
                    <div class="small text-muted mt-2 text-center">
                        <i class="bi bi-search"></i> Klik gambar untuk memperbesar
                    </div>
                </div>
              <?php else: ?>
                <div class="alert alert-secondary text-center py-5">
                    <i class="bi bi-image text-muted d-block mb-2" style="font-size: 2rem;"></i>
                    Belum ada bukti transfer yang diunggah.
                </div>
              <?php endif; ?>
            </div>

            <div class="col-md-6 d-flex flex-column justify-content-center">
              <?php if ($data_order['status'] == 'menunggu_verifikasi'): ?>
                <div class="p-3 border rounded bg-light">
                  <h6 class="fw-bold">Konfirmasi Verifikasi</h6>
                  <p class="small text-muted">Periksa kesesuaian nominal pada bukti transfer dengan total tagihan sebelum menyetujui.</p>
                  
                  <div class="d-grid gap-2 mt-3">
                    <a href="index.php?page=detail&id=<?= $id_order ?>&verif=approve"
                       class="btn btn-success py-2"
                       onclick="return confirm('Apakah Anda yakin nominal sudah sesuai dan ingin MENERIMA pembayaran ini?')">
                       ✔ Terima Pembayaran
                    </a>
                    <a href="index.php?page=detail&id=<?= $id_order ?>&verif=reject"
                       class="btn btn-outline-danger py-2"
                       onclick="return confirm('Tolak pembayaran ini? Pelanggan akan melihat status dibatalkan.')">
                       ✖ Tolak Pembayaran
                    </a>
                  </div>
                </div>
              <?php else: ?>
                <div class="text-center p-4">
                    <span class="text-muted">Status transaksi: <strong><?= strtoupper($data_order['status']) ?></strong></span>
                    <p class="small mt-1">Tidak ada aksi verifikasi yang diperlukan.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>
      
    </div>
  </div>
</section>