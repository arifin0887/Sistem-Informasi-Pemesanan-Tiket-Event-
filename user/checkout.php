<?php

// CEK LOGIN
if (!isset($_SESSION['user']['id'])) {
    echo "<div class='alert alert-danger shadow-sm'>Silakan login terlebih dahulu untuk melanjutkan checkout.</div>";
    return;
}

// REDIRECT JIKA CART KOSONG (TIDAK ADA TIKET YANG DIPILIH)
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>window.location.href='index.php?page=event';</script>";
    exit;
}

// AMBIL DATA CART DARI SESSION
$cart = $_SESSION['cart'];
$message = '';
$message_type = '';

// LOGIC CHECKOUT 
if (isset($_POST['proses_checkout'])) {
    $nama_pemesan = trim($_POST['nama_pemesan'] ?? '');
    $email_pemesan = trim($_POST['email_pemesan'] ?? '');
    // METODE PEMBAYARAN BELUM DIPROSES DI SINI, HANYA DITERIMA SEBAGAI INPUT
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? '';

    // VALIDASI DATA PEMESANAN 
    if (empty($nama_pemesan) || empty($email_pemesan)) {
        $message = 'Mohon lengkapi data pemesan.';
        $message_type = 'warning';
    } else {
        // PROSES TRANSAKSI DENGAN TRANSAKSI DATABASE (BEGIN-TRY-CATCH-ROLLBACK)
        mysqli_begin_transaction($conn);
        try {
            $id_user = (int)$_SESSION['user']['id'];
            $total = (int)$cart['total'];
            $id_voucher = ($cart['id_voucher'] > 0) ? (int)$cart['id_voucher'] : null;

            // INSERT KE TABEL orders  
            $sql_order = "INSERT INTO orders (id_user, tanggal_order, total, status, id_voucher) VALUES (?, NOW(), ?, 'pending', ?)";
            $stmt_o = $conn->prepare($sql_order);
            $stmt_o->bind_param("iii", $id_user, $total, $id_voucher);
            
            // JIKA GAGAL, THROW EXCEPTION UNTUK MENANGANI ROLLBACK
            if (!$stmt_o->execute()) throw new Exception("Gagal menyimpan pesanan utama.");
            $id_order = $conn->insert_id;

            // INSERT KE TABEL order_detail
            $id_tiket = (int)$cart['id_tiket'];
            $qty = (int)$cart['jumlah'];
            $subtotal = (int)$cart['subtotal'];

            // JIKA GAGAL, THROW EXCEPTION UNTUK MENANGANI ROLLBACK
            $stmt_d = $conn->prepare("INSERT INTO order_detail (id_order, id_tiket, qty, subtotal) VALUES (?, ?, ?, ?)");
            $stmt_d->bind_param("iiii", $id_order, $id_tiket, $qty, $subtotal);
            if (!$stmt_d->execute()) throw new Exception("Gagal menyimpan detail tiket.");

            // UPDATE KUOTA TIKET (KURANGI KUOTA SESUAI JUMLAH YANG DIBELI)
            if ($id_voucher) {
                $stmt_u = $conn->prepare("UPDATE voucher SET kuota = kuota - 1 WHERE id_voucher = ? AND kuota > 0");
                $stmt_u->bind_param("i", $id_voucher);
                $stmt_u->execute();
            }

            mysqli_commit($conn);

            // BERSIHKAN CART & REDIRECT
            unset($_SESSION['cart']);
            echo "<script>alert('Pesanan berhasil dibuat!'); window.location.href='index.php?page=payment&id_order=$id_order';</script>";
            exit;

        } catch (Exception $e) {
            // JIKA TERJADI ERROR, ROLLBACK TRANSAKSI DAN TAMPILKAN PESAN ERROR
            mysqli_rollback($conn);
            $message = 'Terjadi kesalahan: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// AMBIL DATA EVENT & VENUE UNTUK DITAMPILKAN DI RINGKASAN CHECKOUT
$id_ev = (int)$cart['id_event'];
$ev_query = mysqli_query($conn, "SELECT e.*, v.nama_venue FROM event e JOIN venue v ON e.id_venue = v.id_venue WHERE e.id_event = $id_ev");
$ev_data = mysqli_fetch_assoc($ev_query);
?>

<div class="pagetitle">
    <h1>Checkout</h1>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title">Informasi Pemesan</h5>
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type; ?>"><?= $message; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_pemesan" class="form-control" value="<?= $_SESSION['user']['nama'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email_pemesan" class="form-control" value="<?= $_SESSION['user']['email'] ?? '' ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-select" required>
                                <option value="">-- Pilih Pembayaran --</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="ewallet">E-Wallet (QRIS)</option>
                            </select>
                        </div>
                        <button type="submit" name="proses_checkout" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill">
                            Buat Pesanan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h5 class="card-title">Ringkasan Tiket</h5>
                    <div class="d-flex align-items-center mb-3">
                        <div class="ms-0">
                            <h6 class="fw-bold mb-0"><?= $ev_data['nama_event'] ?></h6>
                            <small class="text-muted"><?= date('d M Y', strtotime($ev_data['tanggal'])) ?> | <?= $ev_data['nama_venue'] ?></small>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?= $cart['nama_tiket'] ?> (x<?= $cart['jumlah'] ?>)</span>
                        <span>Rp <?= number_format($cart['subtotal'], 0, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Diskon</span>
                        <span>- Rp <?= number_format($cart['diskon'] ?? 0, 0, ',', '.') ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total Bayar</span>
                        <h4 class="fw-bold text-primary">Rp <?= number_format($cart['total'], 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- <div class="pagetitle mb-4">
    <h1 class="fw-bold text-navy">Checkout</h1>
    <nav><ol class="breadcrumb"><li class="breadcrumb-item">Konfirmasi pesanan dan pilih metode pembayaran</li></ol></nav>
</div>

<?php if($message): ?>
    <div class="alert alert-<?= $message_type; ?> border-0 shadow-sm rounded-4 mb-4 fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> fs-4 me-3"></i>
            <div><?= htmlspecialchars($message); ?></div>
        </div>
    </div>
<?php endif; ?>

<section class="section">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <h5 class="fw-bold mb-4">Informasi Kontak</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                                <input type="text" class="form-control rounded-3" name="nama_pemesan" 
                                       value="<?= htmlspecialchars($_SESSION['user']['nama']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Alamat Email</label>
                                <input type="email" class="form-control rounded-3" name="email_pemesan" 
                                       value="<?= htmlspecialchars($_SESSION['user']['email']); ?>" required>
                            </div>
                        </div>

                        <hr class="my-4 op-1">

                        <h5 class="fw-bold mb-4">Metode Pembayaran</h5>
                        <div class="payment-options">
                            <div class="form-check payment-card border rounded-4 p-3 mb-3 transition">
                                <input class="form-check-input ms-0 me-3" type="radio" name="metode_pembayaran" id="p_bank" value="transfer_bank" required>
                                <label class="form-check-label w-100 fw-semibold" for="p_bank">
                                    <i class="bi bi-bank me-2"></i> Transfer Bank (Manual Konfirmasi)
                                </label>
                            </div>
                            <div class="form-check payment-card border rounded-4 p-3 mb-3 transition">
                                <input class="form-check-input ms-0 me-3" type="radio" name="metode_pembayaran" id="p_ewallet" value="e_wallet">
                                <label class="form-check-label w-100 fw-semibold" for="p_ewallet">
                                    <i class="bi bi-qr-code-scan me-2"></i> E-Wallet (QRIS / OVO / Dana)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a href="index.php?page=buy&id_event=<?= $cart['id_event']; ?>&id_tiket=<?= $cart['id_tiket']; ?>" 
                               class="text-decoration-none text-muted fw-bold small">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Edit Tiket
                            </a>
                            <button type="submit" name="checkout" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">
                                Konfirmasi & Bayar <i class="bi bi-chevron-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Ringkasan Pesanan</h6>
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-3 p-3 text-center me-3">
                            <i class="bi bi-calendar-event text-primary fs-4"></i>
                        </div>
                        <div>
                            <span class="d-block fw-bold text-navy"><?= htmlspecialchars($ev_data['nama_event']); ?></span>
                            <small class="text-muted"><?= htmlspecialchars($ev_data['nama_venue']); ?></small>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-4 mb-4">
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted"><?= $cart['nama_tiket']; ?> x<?= $cart['jumlah']; ?></span>
                            <span>Rp <?= number_format($cart['subtotal'], 0, ',', '.'); ?></span>
                        </div>
                        <?php if($cart['diskon'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Promo (<?= $cart['id_voucher'] ? 'Voucher Aktif' : ''; ?>)</span>
                            <span>-Rp <?= number_format($cart['diskon'], 0, ',', '.'); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between fw-bold pt-2 border-top mt-2">
                            <span>Total</span>
                            <span class="text-primary">Rp <?= number_format($cart['total'], 0, ',', '.'); ?></span>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-4 small py-2">
                        <i class="bi bi-shield-check me-1"></i> Transaksi Anda aman & terenkripsi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->

<style>
    .payment-card:has(input:checked) {
        border-color: var(--bs-primary) !important;
        background-color: #f0f7ff;
    }
    .payment-card:hover {
        border-color: #bbb;
        cursor: pointer;
    }
    .text-navy { color: #1a237e; }
    .transition { transition: all 0.2s ease-in-out; }
</style>

<script>
    <?php if ($redirect_url): ?>
        // REDIRECT OTOMATIS SETELAH 1.5 DETIK (1500ms) JIKA ADA PESAN SUKSES
        setTimeout(function() {
            window.location.href = '<?= $redirect_url; ?>';
        }, 1500);
    <?php endif; ?>

    // MEMBUAT SELURUH KARTU PEMBAYARAN DAPAT DIKLIK UNTUK MEMILIH RADIO BUTTON
    document.querySelectorAll('.payment-card').forEach(card => {
        card.addEventListener('click', () => {
            card.querySelector('input').checked = true;
        });
    });
</script>