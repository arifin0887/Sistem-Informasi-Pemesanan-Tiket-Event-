<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['user']['id'])) {
    echo "<script>location.href='index.php?page=login';</script>";
    exit;
}

$id_order = (int)($_GET['id_order'] ?? 0);
$id_user  = (int)$_SESSION['user']['id'];

// AMBIL DATA ORDER
$query_order = mysqli_query($conn, "
    SELECT o.*, v.potongan 
    FROM orders o 
    LEFT JOIN voucher v ON o.id_voucher = v.id_voucher 
    WHERE o.id_order = $id_order AND o.id_user = $id_user
");

$order = mysqli_fetch_assoc($query_order);

if (!$order) {
    echo "<div class='alert alert-danger'>Order tidak ditemukan.</div>";
    exit;
}

// AMBIL DETAIL TIKET
$tikets = mysqli_query($conn, "
    SELECT od.*, t.nama_tiket, t.kategori_tiket, e.nama_event, e.tanggal, v.nama_venue
    FROM order_detail od 
    JOIN tiket t ON od.id_tiket = t.id_tiket 
    JOIN event e ON t.id_event = e.id_event 
    JOIN venue v ON e.id_venue = v.id_venue 
    WHERE od.id_order = $id_order
");

$message = "";
$type = "";

// PROSES BAYAR
if (isset($_POST['proses_bayar'])) {

    $metode = $_POST['metode'];

    // UPLOAD FILE
    $file = $_FILES['bukti'];
    $nama_file = $file['name'];
    $tmp = $file['tmp_name'];

    // VALIDASI FILE
    $ext = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png'];

    if (!in_array($ext, $allowed)) {
        echo "<script>alert('Format file tidak valid!');</script>";
        return;
    }

    // BUAT NAMA UNIK
    $new_name = 'bukti_' . time() . '.' . $ext;

    // FOLDER SIMPAN
    $path = '../uploads/' . $new_name;

    if (move_uploaded_file($tmp, $path)) {

        // SIMPAN KE DATABASE (opsional)
        mysqli_query($conn, "
            UPDATE orders 
            SET status='menunggu_verifikasi', 
                metode_bayar='$metode',
                bukti_transfer='$new_name'
            WHERE id_order='$id_order'
        ");

        echo "<script>
            alert('Bukti berhasil diupload, menunggu verifikasi admin');
            window.location='index.php?page=riwayat';
        </script>";

    } else {
        echo "<script>alert('Upload gagal!');</script>";
    }
}
?>

<section class="section-payment py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="card shadow-lg rounded-4 border-0">

                    <!-- HEADER -->
                    <div class="card-header bg-primary-eventku text-white text-center py-4">
                        <h4 class="mb-0 fw-bold">Pembayaran Order #<?= $id_order ?></h4>
                        <small>Status: 
                            <span class="badge <?= $order['status'] == 'paid' ? 'bg-success' : 'bg-warning' ?>">
                                <?= strtoupper($order['status']) ?>
                            </span>
                        </small>
                    </div>

                    <div class="card-body p-4">

                        <?php if ($message): ?>
                        <div class="alert alert-<?= $type ?>"><?= $message ?></div>
                        <?php endif; ?>

                        <!-- DETAIL TIKET -->
                        <h5 class="fw-bold mb-3">Detail Pembelian</h5>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tiket</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($t = mysqli_fetch_assoc($tikets)): ?>
                                <tr>
                                    <td>
                                        <?= $t['nama_event'] ?><br>
                                        <small class="text-muted"><?= $t['nama_tiket'] ?></small>
                                    </td>
                                    <td><?= $t['qty'] ?></td>
                                    <td>Rp <?= number_format($t['subtotal']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <hr>

                        <!-- DISKON -->
                        <?php if ($order['potongan'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Potongan Voucher</span>
                            <span class="text-success fw-bold">- Rp <?= number_format($order['potongan']) ?></span>
                        </div>
                        <?php endif; ?>

                        <!-- TOTAL -->
                        <div class="d-flex justify-content-between">
                            <h5>Total Bayar</h5>
                            <h4 class="text-primary">Rp <?= number_format($order['total']) ?></h4>
                        </div>

                        <hr>

                        <!-- BUTTON BAYAR -->
                        <?php if ($order['status'] == 'pending'): ?>

                        <form method="POST" enctype="multipart/form-data">

                            <div class="mb-3">
                                <label class="fw-bold">Metode Pembayaran</label>
                                <select name="metode" id="metodeBayar" class="form-select" required onchange="showPaymentInfo()">
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="qris">E-Wallet</option>
                                    <option value="bank">Transfer Bank</option>
                                </select>
                            </div>

                            <!-- INFO PEMBAYARAN -->
                            <div id="infoPembayaran" class="mb-3" style="display:none;"></div>

                            <!-- UPLOAD BUKTI -->
                            <div class="mb-3">
                                <label class="fw-bold">Upload Bukti Transfer</label>
                                <input type="file" name="bukti" class="form-control" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG, JPEG</small>
                            </div>

                            <button type="submit" name="proses_bayar" 
                                class="btn btn-success w-100 btn-lg fw-bold">
                                💳 Konfirmasi Pembayaran
                            </button>

                        </form>

                        <?php else: ?>

                        <div class="text-center">
                            <h5 class="text-success">✔ Sudah Dibayar</h5>

                            <a href="index.php?page=e-tiket&id=<?= $id_order ?>">                                class="btn btn-primary mt-3">
                                Lihat E-Tiket
                            </a>
                        </div>

                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
function showPaymentInfo() {
    let metode = document.getElementById('metodeBayar').value;
    let info = document.getElementById('infoPembayaran');

    if (metode === 'bank') {
        info.style.display = 'block';
        info.innerHTML = `
            <div class="alert alert-info text-center">
                <b>Transfer ke: Bank BCA</b><br>
                No Rek:
                <b>1234567890</b><br>
                A/N PT Edu Tech Development
            </div>
        `;
    } 
    else if (metode === 'qris') {
        info.style.display = 'block';
        info.innerHTML = `
            <div class="alert alert-success text-center">
                <b>E-Wallet:</b><br>
                Kirim ke E Wallet berikut:<br>
                <span class="fw-bold">081234567890 (OVO/DANA/LinkAja)</span><br>
                <small class="text-muted">Pastikan memasukkan nomor yang benar</small>
            </div>
        `;
    } 
    else {
        info.style.display = 'none';
    }
}
</script>