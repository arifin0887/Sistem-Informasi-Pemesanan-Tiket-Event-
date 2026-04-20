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
            'diskon'       => 0,
            'total'        => $data['harga'] * $jumlah,
            'id_voucher'   => null,
            'kode_voucher' => ''
        ];

        echo "<script>
            alert('Tiket berhasil ditambahkan ke keranjang!');
            window.location='index.php?page=checkout';
        </script>";
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

<script>
    const ticketPrice = <?= $data['harga']; ?>;
    const maxQuota = <?= $data['kuota']; ?>;
    let currentDiscount = 0;

    // FUNGSI AMBIL KUOTA 
    function changeQty(step) {
        const input = document.getElementById('jumlah_tiket');
        let newVal = parseInt(input.value) + step;
        if(newVal >= 1 && newVal <= maxQuota) {
            input.value = newVal;
            updateSummary();
        }
    }

    // FUNGSI UPDATE 
    function updateSummary() {
        const jumlah = parseInt(document.getElementById('jumlah_tiket').value);
        const subtotal = ticketPrice * jumlah;
        const total = Math.max(0, subtotal - currentDiscount);

        document.getElementById('display_subtotal').innerText = "Rp " + subtotal.toLocaleString('id-ID');
        document.getElementById('display_diskon').innerText = "- Rp " + currentDiscount.toLocaleString('id-ID');
        document.getElementById('display_total').innerText = "Rp " + total.toLocaleString('id-ID');
    }

    // FUNGSI VALIDASI VOUCHER
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