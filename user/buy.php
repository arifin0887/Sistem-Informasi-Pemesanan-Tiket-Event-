<?php

// CEK LOGIN
if (!isset($_SESSION['user']['id'])) {
    echo "<script>alert('Silakan login terlebih dahulu'); window.location='login.php';</script>";
    exit;
}

// AMBIL ID EVENT & ID TIKET DARI URL
$id_event = (int)($_GET['id_event'] ?? 0);
$id_tiket = (int)($_GET['id_tiket'] ?? 0);

// QUERY UNTUK MENGAMBIL DETAIL EVENT, NAMA TIKET, HARGA, DAN KUOTA
$stmt = $conn->prepare("
    SELECT e.nama_event, e.tanggal, v.nama_venue, t.nama_tiket, t.harga, t.kuota 
    FROM event e 
    JOIN venue v ON e.id_venue = v.id_venue 
    JOIN tiket t ON e.id_event = t.id_event
    WHERE e.id_event = ? AND t.id_tiket = ?
");
$stmt->bind_param("ii", $id_event, $id_tiket);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// JIKA DATA TIDAK DITEMUKAN
if (!$data) {
    echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
    return;
}

// PROSES PEMBELIAN TIKET
if (isset($_POST['proses_buy'])) {
    $jumlah = (int)$_POST['jumlah'];
    
    // VALIDASI JUMLAH TIKET YANG DIBELI
    if ($jumlah > $data['kuota']) {
        echo "<script>alert('Jumlah melebihi kuota tersedia!');</script>";
    } else if ($jumlah <= 0) {
        echo "<script>alert('Jumlah minimal 1 tiket!');</script>";
    } else {
        // SIMPAN KE SESSION CART UNTUK DILANJUTKAN KE HALAMAN CHECKOUT
        $_SESSION['cart'] = [
            'id_event'     => $id_event,
            'id_tiket'     => $id_tiket,
            'nama_event'   => $data['nama_event'],
            'nama_tiket'   => $data['nama_tiket'],
            'harga_satuan' => $data['harga'],
            'jumlah'       => $jumlah,
            'subtotal'     => $data['harga'] * $jumlah,
            'diskon'       => 0, // Inisialisasi awal, akan dihitung di checkout jika ada voucher
            'total'        => $data['harga'] * $jumlah,
            'id_voucher'   => null,
            'kode_voucher' => ''
        ];

        header("Location: index.php?page=checkout");
        exit;
    }
}
?>

<section class="section">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold">Pemesanan Tiket</h5>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><td>Event</td><td>: <strong><?= $data['nama_event'] ?></strong></td></tr>
                        <tr><td>Tiket</td><td>: <span class="badge bg-info"><?= $data['nama_tiket'] ?></span></td></tr>
                        <tr><td>Harga</td><td>: <strong>Rp <?= number_format($data['harga'],0,',','.') ?></strong></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jumlah Tiket</label>
                             <!-- Input jumlah tiket dengan validasi min 1 dan max sesuai kuota yang tersedia -->
                            <input type="number" name="jumlah" class="form-control" value="1" min="1" max="<?= $data['kuota'] ?>" required>
                            <small class="text-muted">Tersedia: <?= $data['kuota'] ?> tiket</small>
                        </div>
                        <button type="submit" name="proses_buy" class="btn btn-primary w-100 fw-bold rounded-pill">
                            Lanjut ke Checkout <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .buy-card { border-radius: 20px; overflow: hidden; }
    .input-step { display: flex; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; width: 130px; }
    .input-step button { border: none; background: #f8f9fa; width: 40px; transition: 0.2s; }
    .input-step button:hover { background: #e9ecef; }
    .input-step input { border: none; text-align: center; width: 50px; font-weight: bold; }
    .summary-box { background: #f8faff; border-radius: 15px; padding: 20px; border: 1px solid #eef2ff; }
</style>

<!-- <div class="row g-4 mt-2">
    <div class="col-lg-7">
        <div class="card buy-card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="index.php?page=event" class="text-decoration-none">Event</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($data['nama_event']); ?></li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-navy mb-1"><?= htmlspecialchars($data['nama_event']); ?></h2>
                <p class="text-muted"><i class="bi bi-geo-alt me-2"></i><?= $data['nama_venue']; ?></p>
                
                <div class="p-3 rounded-4 bg-light d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <span class="badge bg-primary rounded-pill px-3 mb-2"><?= $data['nama_tiket']; ?></span>
                        <h4 class="mb-0 fw-bold">Rp <?= number_format($data['harga'], 0, ',', '.'); ?> <small class="text-muted fs-6">/tiket</small></h4>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Tersedia</small>
                        <span class="fw-bold"><?= $data['kuota']; ?> Tiket</span>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Informasi Penting</h6>
                    <ul class="small text-muted ps-3">
                        <li>Tiket yang sudah dibeli tidak dapat dibatalkan/refund.</li>
                        <li>Maksimal pembelian disesuaikan dengan kuota tersedia.</li>
                        <li>Pastikan kode voucher diinput sebelum menekan tombol beli.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card buy-card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Konfirmasi Pesanan</h5>
                
                <form method="POST">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <label class="fw-semibold">Jumlah Tiket</label>
                        <div class="input-step">
                            <button type="button" onclick="changeQty(-1)"><i class="bi bi-dash"></i></button>
                            <input type="number" name="jumlah" id="jumlah_tiket" value="1" min="1" max="<?= $data['kuota']; ?>" readonly>
                            <button type="button" onclick="changeQty(1)"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Gunakan Promo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-ticket-perforated"></i></span>
                            <input type="text" id="kode_voucher_field" class="form-control border-start-0 ps-0" placeholder="Kode Promo">
                            <button class="btn btn-outline-primary" type="button" onclick="validateVoucher()">Cek</button>
                        </div>
                        <div id="voucherStatus" class="mt-2 small"></div>
                        <input type="hidden" name="id_voucher" id="id_voucher_input">
                    </div>

                    <div class="summary-box">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold" id="display_subtotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span class="text-muted">Potongan Harga</span>
                            <span id="display_diskon">- Rp 0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Total Bayar</span>
                            <h4 class="fw-bold text-primary mb-0" id="display_total">Rp 0</h4>
                        </div>
                    </div>

                    <button type="submit" name="beli" class="btn btn-primary btn-lg w-100 mt-4 rounded-pill fw-bold shadow">
                        Beli Tiket Sekarang <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div> -->

<script>
    const ticketPrice = <?= $data['harga']; ?>;
    const maxQuota = <?= $data['kuota']; ?>;
    let currentDiscount = 0;

    function changeQty(step) {
        const input = document.getElementById('jumlah_tiket');
        let newVal = parseInt(input.value) + step;
        if(newVal >= 1 && newVal <= maxQuota) {
            input.value = newVal;
            updateSummary();
        }
    }

    function updateSummary() {
        const jumlah = parseInt(document.getElementById('jumlah_tiket').value);
        const subtotal = ticketPrice * jumlah;
        const total = Math.max(0, subtotal - currentDiscount);

        document.getElementById('display_subtotal').innerText = "Rp " + subtotal.toLocaleString('id-ID');
        document.getElementById('display_diskon').innerText = "- Rp " + currentDiscount.toLocaleString('id-ID');
        document.getElementById('display_total').innerText = "Rp " + total.toLocaleString('id-ID');
    }

    function validateVoucher() {
        const kode = document.getElementById('kode_voucher_field').value;
        const statusDiv = document.getElementById('voucherStatus');
        
        if(!kode) return;

        statusDiv.innerHTML = '<span class="text-muted spinner-border spinner-border-sm me-2"></span>Checking...';

        fetch('cek_voucher.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'kode_voucher=' + encodeURIComponent(kode) + '&action=validate_voucher'
        })
        .then(res => res.json())
        .then(data => {
            if(data.valid) {
                currentDiscount = data.diskon;
                document.getElementById('id_voucher_input').value = data.id_voucher;
                statusDiv.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i> ${data.message}</span>`;
            } else {
                currentDiscount = 0;
                document.getElementById('id_voucher_input').value = "";
                statusDiv.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i> ${data.message}</span>`;
            }
            updateSummary();
        })
        .catch(() => {
            statusDiv.innerHTML = '<span class="text-danger small">Gagal memeriksa voucher</span>';
        });
    }

    // Jalankan kalkulasi saat halaman pertama kali dibuka
    document.addEventListener('DOMContentLoaded', updateSummary);
</script>