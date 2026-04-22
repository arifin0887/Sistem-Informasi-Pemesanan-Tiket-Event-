<?php
// CEK LOGIN
if (!isset($_SESSION['user']['id'])) {
    echo "<div class='alert alert-danger'>Silakan login terlebih dahulu.</div>";
    return;
}

// CEK CART
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>window.location='index.php?page=event';</script>";
    exit;
}

$cart = $_SESSION['cart'];
$message = ""; $message_type = "";

// APPLY VOUCHER
if (isset($_POST['apply_voucher'])) {
    $kode = trim($_POST['kode_voucher']);

    $stmt = $conn->prepare("SELECT * FROM voucher WHERE kode_voucher = ? AND kuota > 0");
    $stmt->bind_param("s", $kode);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $voucher = $res->fetch_assoc();

        $_SESSION['cart']['id_voucher'] = $voucher['id_voucher'];
        $_SESSION['cart']['potongan'] = $voucher['potongan'];

        $message = "Voucher berhasil digunakan!";
        $message_type = "success";
    } else {
        $_SESSION['cart']['id_voucher'] = 0;
        $_SESSION['cart']['potongan'] = 0;

        $message = "Kode voucher tidak valid / habis.";
        $message_type = "danger";
    }
}

// HITUNG TOTAL
$subtotal = $cart['subtotal'];
$diskon = $_SESSION['cart']['potongan'] ?? 0;
$total = $subtotal - $diskon;
if ($total < 0) $total = 0;

$_SESSION['cart']['total'] = $total;

// CHECKOUT
if (isset($_POST['proses_checkout'])) {
    mysqli_begin_transaction($conn);

    try {
        $id_user = (int)$_SESSION['user']['id'];
        $id_voucher = $_SESSION['cart']['id_voucher'] ?? null;

        // INSERT ORDER
        $stmt = $conn->prepare("INSERT INTO orders (id_user, tanggal_order, total, status, id_voucher) VALUES (?, NOW(), ?, 'pending', ?)");
        $stmt->bind_param("iii", $id_user, $total, $id_voucher);
        $stmt->execute();

        $id_order = $conn->insert_id;

        // INSERT DETAIL
        $stmt2 = $conn->prepare("INSERT INTO order_detail (id_order, id_tiket, qty, subtotal) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("iiii",
            $id_order,
            $cart['id_tiket'],
            $cart['jumlah'],
            $subtotal
        );
        $stmt2->execute();

        // KURANGI STOK TIKET
        mysqli_query($conn, "UPDATE tiket SET kuota = kuota - {$cart['jumlah']} WHERE id_tiket = {$cart['id_tiket']}");

        // KURANGI KUOTA VOUCHER
        if ($id_voucher) {
            mysqli_query($conn, "UPDATE voucher SET kuota = kuota - 1 WHERE id_voucher = $id_voucher");
        }

        mysqli_commit($conn);

        unset($_SESSION['cart']);

        echo "<script>alert('Checkout berhasil!'); window.location='index.php?page=payment&id_order=$id_order';</script>";
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// DATA EVENT
$id_ev = (int)$cart['id_event'];
$ev = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT e.*, v.nama_venue 
    FROM event e 
    JOIN venue v ON e.id_venue = v.id_venue 
    WHERE e.id_event = $id_ev
"));
?>

<section class="section section-checkout">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card card-buy">
                    <div class="card-header text-white text-center">
                        <h4 class="mb-0 fw-bold">Checkout Pesanan</h4>
                        <p class="mb-0 opacity-75 small">Konfirmasi detail sebelum pembayaran</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4">
                            <div class="col-md-6 pe-md-4 border-end">
                                <div class="event-info-box mb-4">
                                    <h6 class="text-muted text-uppercase fw-bold small mb-3" style="letter-spacing: 1px;">Detail Event</h6>
                                    <h3 class="fw-bold text-primary mb-3"><?= htmlspecialchars($ev['nama_event']) ?></h3>
                                    
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="icon-box me-3 text-secondary"><i class="bi bi-calendar3 fs-5"></i></div>
                                        <div>
                                            <p class="mb-0 fw-bold text-primary"><?= date('d F Y', strtotime($ev['tanggal'])) ?></p>
                                            <p class="mb-0 small text-muted"><?= date('H:i', strtotime($ev['tanggal'])) ?> WIB</p>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center mb-4">
                                        <div class="icon-box me-3 text-secondary"><i class="bi bi-geo-alt fs-5"></i></div>
                                        <div>
                                            <p class="mb-0 fw-bold text-primary"><?= htmlspecialchars($ev['nama_venue']) ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="summary-box p-4">
                                    <h6 class="fw-bold text-primary mb-3">Ringkasan Pembayaran</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted"><?= $cart['nama_tiket'] ?> x<?= $cart['jumlah'] ?></span>
                                        <span class="fw-bold">Rp <?= number_format($subtotal) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 text-success fw-bold">
                                        <span>Potongan Voucher</span>
                                        <span>- Rp <?= number_format($diskon) ?></span>
                                    </div>
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary fs-5">Total Bayar</span>
                                        <span class="fs-4 fw-bold text-secondary">Rp <?= number_format($total) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 ps-md-4">
                                <?php if ($message): ?>
                                    <div class="alert alert-success border-0 shadow-sm mb-4"><?= $message ?></div>
                                <?php endif; ?>

                                <form method="POST" class="form-buy">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-primary">Nama Lengkap</label>
                                        <input type="text" class="form-control form-control-lg" value="<?= $_SESSION['user']['nama'] ?>" readonly>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-primary">Email</label>
                                        <input type="email" class="form-control form-control-lg" value="<?= $_SESSION['user']['email'] ?>" readonly>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-primary">Kode Voucher <small class="text-muted">(Opsional)</small></label>
                                        <div class="input-group">
                                            <input type="text" name="kode_voucher" class="form-control form-control-lg" placeholder="Masukkan kode voucher" value="<?= $cart['kode_voucher'] ?>">
                                            <button type="submit" name="apply_voucher" class="btn btn-outline-secondary">
                                                <i class="bi bi-tag me-1"></i>Gunakan
                                            </button>
                                        </div>
                                    </div>

                                    <button type="submit" name="proses_checkout" class="btn btn-checkout btn-lg">
                                        <i class="bi bi-credit-card me-2"></i>Lanjut ke Pembayaran
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>