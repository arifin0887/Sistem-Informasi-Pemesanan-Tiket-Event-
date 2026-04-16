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
$tikets = mysqli_query($conn, "SELECT od.*, t.nama_tiket, e.nama_event, e.tanggal, v.nama_venue, t.id_event 
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

<section class="section">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold">Konfirmasi Pembayaran #<?php echo $id_order; ?></h5>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $message; ?>
                            <br><a href="index.php?page=e-tiket&id=<?php echo $id_order; ?>" class="btn btn-sm btn-light mt-2 text-success fw-bold">Lihat E-Tiket Saya</a>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Event</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_item = 0;
                                while($t = mysqli_fetch_assoc($tikets)): 
                                    $total_item += $t['subtotal'];
                                ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold d-block"><?php echo $t['nama_event']; ?></span>
                                        <small class="text-muted"><?php echo $t['nama_tiket']; ?></small>
                                    </td>
                                    <td class="text-center"><?php echo $t['qty']; ?></td>
                                    <td class="text-end">Rp <?php echo number_format($t['subtotal'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>

                                <?php if ($order['potongan'] > 0): ?>
                                <tr class="table-light">
                                    <td colspan="2" class="text-end fw-bold text-danger">Potongan (<?php echo $order['kode_voucher'] ?? 'Discount'; ?>)</td>
                                    <td class="text-end fw-bold text-danger">- Rp <?php echo number_format($order['potongan'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endif; ?>

                                <tr class="table-primary">
                                    <td colspan="2" class="text-end fw-bold text-uppercase">Total Bayar</td>
                                    <td class="text-end fw-bold" style="font-size: 1.1rem;">
                                        Rp <?php echo number_format($order['total'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($order['status'] == 'pending'): ?>
                    <form method="POST">
                        <div class="p-3 border rounded mb-4 bg-light">
                            <h6 class="fw-bold mb-3">Pilih Metode Pembayaran:</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="pay" id="m1" checked>
                                <label class="form-check-label" for="m1">Instant Payment (QRIS/E-Wallet)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pay" id="m2">
                                <label class="form-check-label" for="m2">Virtual Account Bank</label>
                            </div>
                        </div>
                        <button type="submit" name="proses_bayar" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill">
                            Bayar Sekarang Rp <?php echo number_format($order['total'],0,',','.'); ?>
                        </button>
                    </form>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="display-1 text-success mb-3"><i class="bi bi-patch-check-fill"></i></div>
                            <h4 class="fw-bold text-success">Lunas</h4>
                            <p class="text-muted">Transaksi ini telah berhasil dibayar pada <?php echo date('d/m/Y H:i'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 bg-primary text-white shadow">
                <div class="card-body p-4 text-center">
                    <p class="mb-1 opacity-75">Total Tagihan</p>
                    <h2 class="fw-bold mb-0">Rp <?php echo number_format($order['total'],0,',','.'); ?></h2>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-shield-lock me-2"></i>Keamanan</h6>
                    <small class="text-muted">Data pembayaran Anda dienkripsi dan aman. Pastikan tidak membagikan bukti transfer kepada siapapun.</small>
                </div>
            </div>
        </div>
    </div>
</section>