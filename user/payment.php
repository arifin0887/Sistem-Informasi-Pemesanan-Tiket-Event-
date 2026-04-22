<?php

// CEK SESSION USER
if (!isset($_SESSION['user']['id'])) {
    echo "<script>location.href='index.php?page=login';</script>"; exit;
}

// ID ORDER DARI URL
$id_order = (int)$_GET['id_order'];
$id_user = (int)$_SESSION['user']['id'];

// AMBIL DATA ORDER UNTUK USER 
$query_order = mysqli_query($conn, "SELECT o.*, v.potongan 
    FROM orders o 
    LEFT JOIN voucher v ON o.id_voucher = v.id_voucher 
    WHERE o.id_order = $id_order AND o.id_user = $id_user");
$order = mysqli_fetch_assoc($query_order);

// JIKA ORDER TIDAK DITEMUKAN ATAU BUKAN MILIK USER, TAMPILKAN ERROR
if (!$order) {
    echo "<div class='alert alert-danger'>Order tidak ditemukan.</div>"; exit;
}

// AMBIL DATA TIKET YANG DIBELI DALAM ORDER BESERTA NAMA EVENT, TANGGAL, DAN VENUE
$tikets = mysqli_query($conn, "SELECT od.*, t.nama_tiket, t.kategori_tiket, e.nama_event, e.tanggal, v.nama_venue, t.id_event 
    FROM order_detail od 
    JOIN tiket t ON od.id_tiket = t.id_tiket 
    JOIN event e ON t.id_event = e.id_event 
    JOIN venue v ON e.id_venue = v.id_venue 
    WHERE od.id_order = $id_order");

// LOGIC PEMBAYARAN
$message = '';
if (isset($_POST['proses_bayar'])) {
    mysqli_begin_transaction($conn);
    try {
        // UPDATE STATUS ORDER MENJADI 'paid'
        mysqli_query($conn, "UPDATE orders SET status = 'paid' WHERE id_order = $id_order");

        // GENERATE KODE TIKET UNTUK SETIAP ITEM YANG DIBELI DAN SIMPAN KE TABEL ATTENDEE
        $details = mysqli_query($conn, "SELECT * FROM order_detail WHERE id_order = $id_order");
        while ($d = mysqli_fetch_assoc($details)) {

            // CEK KODE TIKET, JIKA BELUM MAKA GENERATE SESUAI QTY YANG DIBELI
            $cek = mysqli_query($conn, "SELECT id_attendee FROM attendee WHERE id_detail = {$d['id_detail']}");
            if (mysqli_num_rows($cek) == 0) {
                for ($i = 0; $i < $d['qty']; $i++) {
                    $kode = "EVT-" . strtoupper(bin2hex(random_bytes(4))); // GENERATE KODE TIKET UNIK
                    mysqli_query($conn, "INSERT INTO attendee (id_detail, kode_tiket, status_checkin) 
                                       VALUES ({$d['id_detail']}, '$kode', 'belum')");
                }
            }
        }

        // COMMIT TRANSAKSI
        mysqli_commit($conn);
        $message = "Pembayaran berhasil, silakan periksa e-tiket anda!";
        $order['status'] = 'paid'; // UPDATE STATUS ORDER DI VARIABEL UNTUK TAMPILAN
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = "Terjadi kesalahan pembayaran.";
    }
}
?>

<section class="section-payment py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card card-eventku">
                    <div class="card-header bg-primary-eventku text-white text-center py-4">
                        <h4 class="mb-0 fw-bold">Konfirmasi Pembayaran #<?= $id_order ?></h4>
                        <p class="mb-0 opacity-75 small">Lengkapi pembayaran untuk menerima e-tiket</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="event-info-box p-3 rounded-4 mb-4" style="background: #f8f9fa;">
                                    <h6 class="text-muted text-uppercase fw-bold small mb-3">Ringkasan Tiket</h6>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-primary-eventku">Total Bayar</span>
                                        <span class="fs-4 fw-bold text-secondary">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
                                    </div>
                                </div>

                                <?php if ($order['status'] == 'pending'): ?>
                                <form method="POST">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-primary-eventku mb-3">Metode Pembayaran</label>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <input type="radio" class="btn-check" name="pay" id="qris" value="qris" checked>
                                                <label class="btn btn-outline-light text-dark w-100 p-3 text-start border shadow-sm" for="qris">
                                                    <i class="bi bi-qr-code-scan me-2"></i> QRIS / E-Wallet
                                                </label>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="radio" class="btn-check" name="pay" id="bank" value="bank">
                                                <label class="btn btn-outline-light text-dark w-100 p-3 text-start border shadow-sm" for="bank">
                                                    <i class="bi bi-bank me-2"></i> Virtual Account
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" name="proses_bayar" class="btn btn-primary-eventku text-white w-100 btn-eventku shadow-lg">
                                        Bayar Sekarang
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>

                            <div class="col-lg-4">
                                <div class="card border-0 bg-primary-eventku text-white shadow-sm rounded-4">
                                    <div class="card-body p-4 text-center">
                                        <i class="bi bi-shield-lock-fill fs-1 mb-2 opacity-50"></i>
                                        <h3 class="fw-bold mb-0">Lunas</h3>
                                        <small class="opacity-75">Sistem Pembayaran Aman</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>