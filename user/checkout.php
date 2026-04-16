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

// ================== APPLY VOUCHER ==================
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

// ================== HITUNG TOTAL ==================
$subtotal = $cart['subtotal'];
$diskon = $_SESSION['cart']['potongan'] ?? 0;
$total = $subtotal - $diskon;
if ($total < 0) $total = 0;

$_SESSION['cart']['total'] = $total;

// ================== CHECKOUT ==================
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

// ================== DATA EVENT ==================
$id_ev = (int)$cart['id_event'];
$ev = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT e.*, v.nama_venue 
    FROM event e 
    JOIN venue v ON e.id_venue = v.id_venue 
    WHERE e.id_event = $id_ev
"));
?>

<div class="container mt-4">
    <div class="row">
        
        <!-- FORM -->
        <div class="col-lg-7">
            <div class="card p-4 shadow-sm">
                <h5>Checkout</h5>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST">

                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" class="form-control" value="<?= $_SESSION['user']['nama'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" value="<?= $_SESSION['user']['email'] ?>" readonly>
                    </div>

                    <!-- VOUCHER -->
                    <div class="mb-3">
                        <label>Kode Voucher</label>
                        <div class="input-group">
                            <input type="text" name="kode_voucher" class="form-control" placeholder="Masukkan kode voucher">
                            <button type="submit" name="apply_voucher" class="btn btn-outline-primary">
                                Gunakan
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="proses_checkout" class="btn btn-primary w-100">
                        Checkout Sekarang
                    </button>
                </form>
            </div>
        </div>

        <!-- RINGKASAN -->
        <div class="col-lg-5">
            <div class="card p-4 shadow-sm">
                <h5>Ringkasan</h5>

                <p class="fw-bold"><?= $ev['nama_event'] ?></p>
                <small><?= date('d F Y', strtotime($ev['tanggal'])) ?></small>

                <hr>

                <div class="d-flex justify-content-between">
                    <span><?= $cart['nama_tiket'] ?> x<?= $cart['jumlah'] ?></span>
                    <span>Rp <?= number_format($subtotal) ?></span>
                </div>

                <div class="d-flex justify-content-between text-danger">
                    <span>Diskon</span>
                    <span>- Rp <?= number_format($diskon) ?></span>
                </div>

                <hr>

                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span>Rp <?= number_format($total) ?></span>
                </div>
            </div>
        </div>

    </div>
</div>