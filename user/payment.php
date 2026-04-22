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

<section class="section section-payment">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card card-buy">
                    <div class="card-header text-white text-center">
                        <h4 class="mb-0 fw-bold">Konfirmasi Pembayaran #<?= $id_order ?></h4>
                        <p class="mb-0 opacity-75 small">Lengkapi pembayaran untuk menerima e-tiket</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <?php if ($message): ?>
                            <div class="alert alert-success border-0 shadow-sm mb-4">
                                <i class="bi bi-check-circle-fill me-2 text-success"></i> <?= $message ?>
                                <br><a href="index.php?page=e-tiket&id=<?= $id_order ?>" class="btn btn-sm btn-outline-success mt-2 fw-bold">Lihat E-Tiket Saya</a>
                            </div>
                        <?php endif; ?>

                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="event-info-box mb-4">
                                    <h6 class="text-muted text-uppercase fw-bold small mb-3" style="letter-spacing: 1px;">Detail Pesanan</h6>
                                    
                                    <?php 
                                    mysqli_data_seek($tikets, 0); // Reset pointer
                                    while($t = mysqli_fetch_assoc($tikets)): 
                                    ?>
                                    <div class="border-bottom pb-3 mb-3">
                                        <h5 class="fw-bold text-primary mb-2"><?= htmlspecialchars($t['nama_event']) ?></h5>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">[<?= ucfirst($t['kategori_tiket']) ?>] <?= $t['nama_tiket'] ?></span>
                                            <span class="fw-bold">x<?= $t['qty'] ?></span>
                                        </div>
                                        <div class="text-end text-primary fw-bold">
                                            Rp <?= number_format($t['subtotal'], 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>

                                    <?php if ($order['potongan'] > 0): ?>
                                    <div class="d-flex justify-content-between text-success fw-bold fs-6 mb-3">
                                        <span>Potongan Voucher</span>
                                        <span>- Rp <?= number_format($order['potongan'], 0, ',', '.') ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-between align-items-center fs-4 fw-bold">
                                        <span class="text-primary">Total Bayar</span>
                                        <span class="text-secondary">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
                                    </div>
                                </div>

                                <?php if ($order['status'] == 'pending'): ?>
                                <form method="POST" class="form-buy">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-primary fs-5 mb-3">Pilih Metode Pembayaran</label>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-check payment-method">
                                                    <input class="form-check-input" type="radio" name="pay" id="qris" value="qris" checked>
                                                    <label class="form-check-label d-flex align-items-center p-3 border rounded-lg bg-light hover-shadow" for="qris">
                                                        <i class="bi bi-qr-code-scan fs-3 text-secondary me-3"></i>
                                                        <div>
                                                            <div class="fw-bold">QRIS / E-Wallet</div>
                                                            <small class="text-muted">Instant & Secure</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check payment-method">
                                                    <input class="form-check-input" type="radio" name="pay" id="bank" value="bank">
                                                    <label class="form-check-label d-flex align-items-center p-3 border rounded-lg bg-light hover-shadow" for="bank">
                                                        <i class="bi bi-bank fs-3 text-secondary me-3"></i>
                                                        <div>
                                                            <div class="fw-bold">Virtual Account</div>
                                                            <small class="text-muted">Transfer Bank</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" name="proses_bayar" class="btn btn-checkout btn-lg">
                                        <i class="bi bi-lock-fill me-2"></i>Bayar Sekarang Rp <?= number_format($order['total'], 0, ',', '.') ?>
                                    </button>
                                </form>
                                <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="icon-box mb-4" style="font-size: 4rem; color: var(--success);">
                                        <i class="bi bi-patch-check-fill"></i>
                                    </div>
                                    <h3 class="fw-bold text-success mb-3">Pembayaran Berhasil!</h3>
                                    <p class="text-muted mb-4">Transaksi lunas. E-tiket sudah tersedia.</p>
                                    <a href="index.php?page=e-tiket&id=<?= $id_order ?>" class="btn btn-success btn-lg">
                                        <i class="bi bi-ticket-perforated me-2"></i>Lihat E-Tiket
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-lg-4">
                                <div class="card border-0 bg-gradient text-white shadow rounded-3" style="background: linear-gradient(135deg, var(--secondary) 0%, #d45a78 100%);">
                                    <div class="card-body p-4 text-center">
                                        <i class="bi bi-credit-card-2-front fs-1 mb-3 opacity-75"></i>
                                        <h2 class="fw-bold mb-1">Rp <?= number_format($order['total'], 0, ',', '.') ?></h2>
                                        <p class="opacity-75 mb-0">Total Tagihan</p>
                                    </div>
                                </div>
                                <div class="card border-0 shadow-sm mt-3">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-shield-check me-2 text-success"></i>100% Aman
                                        </h6>
                                        <ul class="list-unstyled small text-muted">
                                            <li class="mb-2"><i class="bi bi-shield-lock me-2 text-primary"></i>Data terenkripsi SSL</li>
                                            <li class="mb-2"><i class="bi bi-check-circle me-2 text-success"></i>Pembayaran resmi</li>
                                            <li><i class="bi bi-headset me-2 text-warning"></i>Customer support 24/7</li>
                                        </ul>
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
