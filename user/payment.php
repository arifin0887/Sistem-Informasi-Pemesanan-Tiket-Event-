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

// =======================
// PROSES BAYAR
// =======================
if (isset($_POST['proses_bayar'])) {

    mysqli_begin_transaction($conn);

    try {

        // UPDATE STATUS ORDER
        mysqli_query($conn, "
            UPDATE orders 
            SET status = 'paid' 
            WHERE id_order = $id_order
        ");

        // AMBIL DETAIL UNTUK GENERATE TIKET
        $details = mysqli_query($conn, "
            SELECT * FROM order_detail 
            WHERE id_order = $id_order
        ");

        while ($d = mysqli_fetch_assoc($details)) {

            // CEK APAKAH SUDAH ADA TIKET
            $cek = mysqli_query($conn, "
                SELECT id_attendee 
                FROM attendee 
                WHERE id_detail = {$d['id_detail']}
            ");

            if (mysqli_num_rows($cek) == 0) {

                for ($i = 0; $i < $d['qty']; $i++) {

                    $kode = "EVT-" . strtoupper(bin2hex(random_bytes(4)));

                    mysqli_query($conn, "
                        INSERT INTO attendee (id_detail, kode_tiket, status_checkin) 
                        VALUES ({$d['id_detail']}, '$kode', 'belum')
                    ");
                }
            }
        }

        mysqli_commit($conn);

        $message = "✅ Pembayaran berhasil! Tiket sudah dibuat.";
        $type = "success";

        // REFRESH DATA ORDER
        $order['status'] = 'paid';

    } catch (Exception $e) {

        mysqli_rollback($conn);

        $message = "❌ Terjadi kesalahan saat pembayaran!";
        $type = "danger";
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

                        <!-- ===================== -->
                        <!-- BUTTON BAYAR -->
                        <!-- ===================== -->
                        <?php if ($order['status'] == 'pending'): ?>

                        <form method="POST">

                            <div class="mb-3">
                                <label class="fw-bold">Metode Pembayaran</label>

                                <select name="metode" class="form-select" required>
                                    <option value="qris">QRIS / E-Wallet</option>
                                    <option value="bank">Transfer Bank</option>
                                </select>
                            </div>

                            <button type="submit" name="proses_bayar" 
                                class="btn btn-success w-100 btn-lg fw-bold">
                                💳 Bayar Sekarang
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